<?php

use App\Events\ComplaintStatusChanged;
use App\Models\Complaint;
use App\Models\ComplaintMeeting;
use App\Models\ComplaintSummonsService;
use App\Models\User;
use App\Notifications\ComplaintAlert;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;

test('staff can record amicable settlement minutes and print the agreement', function () {
    Event::fake([ComplaintStatusChanged::class]);
    Notification::fake();
    $resident = User::factory()->create();
    $staff = User::factory()->create(['role' => 'staff']);
    $complaint = Complaint::factory()->for($resident, 'resident')->create([
        'status' => 'approved',
        'approved_at' => now(),
    ]);

    $this->actingAs($staff, 'staff')
        ->post(route('staff.complaints.meetings.store', $complaint), [
            'meeting_type' => 'mediation',
            'meeting_date' => now()->toDateString(),
            'participants' => 'Alex Resident and Jordan Respondent',
            'minutes' => 'Both parties discussed the property boundary and agreed to restore the marker.',
            'outcome' => 'amicable_settlement',
            'settlement_terms' => 'The parties will restore the boundary marker by the end of the month.',
        ])
        ->assertRedirect(route('staff.complaints.show', $complaint))
        ->assertSessionHas('status', 'Meeting minutes saved and case status updated.');

    $meeting = ComplaintMeeting::query()->firstOrFail();

    $this->assertDatabaseHas('complaints', ['id' => $complaint->id, 'status' => 'resolved']);
    $this->assertDatabaseHas('complaint_meetings', [
        'id' => $meeting->id,
        'complaint_id' => $complaint->id,
        'recorded_by_id' => $staff->id,
        'outcome' => 'amicable_settlement',
        'settlement_terms' => 'The parties will restore the boundary marker by the end of the month.',
    ]);
    $this->assertDatabaseHas('complaint_activities', [
        'complaint_id' => $complaint->id,
        'action' => 'meeting_recorded',
        'to_status' => 'resolved',
    ]);
    Event::assertDispatched(ComplaintStatusChanged::class, fn (ComplaintStatusChanged $event): bool => $event->previousStatus === 'approved'
        && $event->complaint->status === 'resolved');
    Notification::assertSentTo($resident, ComplaintAlert::class);

    $this->actingAs($resident, 'resident')
        ->get(route('complaint-meetings.print', [$complaint, $meeting]))
        ->assertSee('Terms of amicable settlement')
        ->assertSee('The parties will restore the boundary marker by the end of the month.')
        ->assertSee('Print minutes / agreement');
});

test('an unsettled hearing marks the complaint unsettled and staff can add later minutes', function () {
    $resident = User::factory()->create();
    $staff = User::factory()->create(['role' => 'staff']);
    $complaint = Complaint::factory()->for($resident, 'resident')->create([
        'status' => 'approved',
        'approved_at' => now(),
    ]);

    $this->actingAs($staff, 'staff')
        ->post(route('staff.complaints.meetings.store', $complaint), [
            'meeting_type' => 'hearing',
            'meeting_date' => now()->toDateString(),
            'minutes' => 'The respondent did not agree with the proposed payment schedule.',
            'outcome' => 'unsettled',
        ])
        ->assertRedirect(route('staff.complaints.show', $complaint));

    $meeting = ComplaintMeeting::query()->firstOrFail();
    $this->assertDatabaseHas('complaints', ['id' => $complaint->id, 'status' => 'unsettled']);
    expect($meeting->settlement_terms)->toBeNull();

    $this->actingAs($resident, 'resident')
        ->get(route('resident.complaints.show', $complaint))
        ->assertSee('Unsettled')
        ->assertSee('Minutes and settlement records');
});

test('residents cannot create meeting records or choose a settlement outcome', function () {
    $resident = User::factory()->create();
    $complaint = Complaint::factory()->for($resident, 'resident')->create(['status' => 'approved']);

    $this->actingAs($resident, 'resident')
        ->post(route('staff.complaints.meetings.store', $complaint), [
            'meeting_type' => 'mediation',
            'meeting_date' => now()->toDateString(),
            'minutes' => 'A resident cannot save official meeting minutes.',
            'outcome' => 'amicable_settlement',
            'settlement_terms' => 'Invalid resident-generated terms.',
        ])
        ->assertRedirect(route('login'));

    $this->assertDatabaseCount('complaint_meetings', 0);
    $this->assertDatabaseHas('complaints', ['id' => $complaint->id, 'status' => 'approved']);
});

test('staff can encode a complaint for an active resident', function () {
    $resident = User::factory()->create(['name' => 'Alex Resident']);
    $staff = User::factory()->create(['role' => 'staff']);

    $this->actingAs($staff, 'staff')
        ->get(route('staff.complaints.create'))
        ->assertSee('name="resident_id"', false)
        ->assertSee('Alex Resident');

    $this->post(route('staff.complaints.store'), [
        'resident_id' => $resident->id,
        'title' => 'Boundary marker dispute',
        'category' => 'civil',
        'respondents' => 'Jordan Respondent',
        'case_subject' => 'Property boundary dispute',
        'description' => 'The shared boundary marker was moved and needs barangay mediation.',
        'location' => 'Purok 1',
        'relief_sought' => 'Restore the agreed boundary marker.',
    ])->assertRedirect(route('staff.complaints.show', Complaint::query()->firstOrFail()));

    $complaint = Complaint::query()->firstOrFail();
    $this->assertDatabaseHas('complaints', [
        'id' => $complaint->id,
        'resident_id' => $resident->id,
        'status' => 'pending',
    ]);
    $this->assertDatabaseHas('complaint_activities', [
        'complaint_id' => $complaint->id,
        'actor_id' => $staff->id,
        'description' => 'Complaint encoded by staff.',
    ]);
});

test('residents cannot select another complainant or attach evidence when filing', function () {
    $resident = User::factory()->create();
    $otherResident = User::factory()->create();

    $this->actingAs($resident, 'resident')
        ->post(route('resident.complaints.store'), [
            'resident_id' => $otherResident->id,
            'title' => 'Boundary marker dispute',
            'category' => 'civil',
            'respondents' => 'Jordan Respondent',
            'case_subject' => 'Property boundary dispute',
            'description' => 'The shared boundary marker was moved and needs barangay mediation.',
            'location' => 'Purok 1',
            'attachments' => [UploadedFile::fake()->image('evidence.jpg')],
        ])
        ->assertSessionHasErrors(['resident_id', 'attachments']);

    $this->assertDatabaseCount('complaints', 0);
});

test('staff can record a refused summons with a private signed service proof', function () {
    Storage::fake('private');
    Notification::fake();
    $resident = User::factory()->create();
    $staff = User::factory()->create(['role' => 'staff']);
    $complaint = Complaint::factory()->for($resident, 'resident')->create([
        'status' => 'approved',
        'summons_ready_at' => now(),
    ]);

    $this->actingAs($staff, 'staff')
        ->post(route('staff.complaints.summons-services.store', $complaint), [
            'served_at' => now()->subHour()->format('Y-m-d\TH:i'),
            'server_name' => 'Taylor Server',
            'recipient_name' => 'Jordan Respondent',
            'result' => 'refused',
            'signer_type' => 'server',
            'signer_name' => 'Taylor Server',
            'refusal_response' => 'The respondent declined to sign and stated they would return another day.',
            'proof' => UploadedFile::fake()->image('service-proof.png'),
        ])
        ->assertRedirect()
        ->assertSessionHas('status', 'Summons service record saved.');

    $service = ComplaintSummonsService::query()->firstOrFail();
    expect($service->result)->toBe('refused')
        ->and($service->signer_type)->toBe('server')
        ->and($service->refusal_response)->toContain('declined to sign');
    Storage::disk('private')->assertExists($service->proof_path);
    $this->assertDatabaseHas('complaint_activities', [
        'complaint_id' => $complaint->id,
        'action' => 'summons_service_recorded',
    ]);
    Notification::assertSentTo($resident, ComplaintAlert::class);

    $this->actingAs($resident, 'resident')
        ->get(route('summons-services.proof', $service))
        ->assertRedirect(route('login'));

    $this->actingAs($staff, 'staff')
        ->get(route('summons-services.proof', $service))
        ->assertOk();

    $this->get(route('summons-services.print', $service))
        ->assertSee('Summons service acknowledgment')
        ->assertSee('The respondent declined to sign');
});

test('a refused summons record requires the refusal response and a proof file', function () {
    Storage::fake('private');
    $staff = User::factory()->create(['role' => 'staff']);
    $complaint = Complaint::factory()->create(['summons_ready_at' => now()]);

    $this->actingAs($staff, 'staff')
        ->from(route('staff.complaints.show', $complaint))
        ->post(route('staff.complaints.summons-services.store', $complaint), [
            'served_at' => now()->subHour()->format('Y-m-d\TH:i'),
            'server_name' => 'Taylor Server',
            'recipient_name' => 'Jordan Respondent',
            'result' => 'refused',
            'signer_type' => 'server',
            'signer_name' => 'Taylor Server',
            'refusal_response' => '',
        ])
        ->assertRedirect(route('staff.complaints.show', $complaint))
        ->assertSessionHasErrors(['refusal_response', 'proof']);

    $this->assertDatabaseCount('complaint_summons_services', 0);
});

test('staff can filter the complaint inbox by unsettled status', function () {
    $resident = User::factory()->create();
    $staff = User::factory()->create(['role' => 'staff']);
    $unsettled = Complaint::factory()->for($resident, 'resident')->create([
        'reference' => 'BRGY-UNSETTLED-1',
        'status' => 'unsettled',
    ]);
    Complaint::factory()->for($resident, 'resident')->create([
        'reference' => 'BRGY-PENDING-1',
        'status' => 'pending',
    ]);

    $this->actingAs($staff, 'staff')
        ->get(route('staff.complaints.index', ['status' => 'unsettled']))
        ->assertSee($unsettled->reference)
        ->assertDontSee('BRGY-PENDING-1');
});

test('users can switch the workspace language to Filipino', function () {
    $staff = User::factory()->create(['role' => 'staff']);

    $this->actingAs($staff, 'staff')
        ->post(route('locale.update'), ['locale' => 'fil'])
        ->assertRedirect();

    $this->get(route('staff.dashboard'))
        ->assertSee('lang="fil"', false)
        ->assertSee('Pangkalahatang-ideya');
});

test('residents can print the receipt confirmation without accessing its private proof file', function () {
    $resident = User::factory()->create();
    $complaint = Complaint::factory()->for($resident, 'resident')->create([
        'receipt_proof_path' => 'complaints/receipt/private.pdf',
        'receipt_proof_original_name' => 'private-receipt.pdf',
        'receipt_proof_mime_type' => 'application/pdf',
        'receipt_uploaded_at' => now(),
    ]);

    $this->actingAs($resident, 'resident')
        ->get(route('complaint-receipt.show', ['complaint' => $complaint, 'guard' => 'resident']))
        ->assertForbidden();

    $this->get(route('complaint-receipt.print', $complaint))
        ->assertSee('Receipt submission acknowledgment')
        ->assertDontSee('private-receipt.pdf');
});
