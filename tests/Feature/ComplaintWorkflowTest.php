<?php

use App\Models\Complaint;
use App\Models\ComplaintActivity;
use App\Models\ComplaintEvidence;
use App\Models\User;
use App\Notifications\ComplaintAlert;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;

test('resident complaint form lists the case category before the short title', function () {
    $response = $this->actingAs(User::factory()->create(), 'resident')
        ->get(route('resident.complaints.create'));

    $response->assertOk()
        ->assertSeeInOrder(['name="category"', 'Short title', 'name="title"'])
        ->assertSee('Civil')
        ->assertSee('Criminal case')
        ->assertSee('Others')
        ->assertSee('relief_sought')
        ->assertSee('incident_date')
        ->assertSee('witnesses');
});

test('resident can submit an other complaint without civil or criminal case fields', function () {
    $resident = User::factory()->create();

    $response = $this->actingAs($resident, 'resident')
        ->post(route('resident.complaints.store'), [
            'title' => 'Request for community assistance',
            'category' => 'other',
            'respondents' => 'Barangay office',
            'case_subject' => 'Community assistance request',
            'description' => 'I am requesting help coordinating a neighborhood clean-up activity.',
            'location' => 'Purok 3',
        ]);

    $complaint = Complaint::query()->firstOrFail();

    $response->assertRedirect(route('resident.complaints.show', $complaint));
    $this->assertDatabaseHas('complaints', [
        'id' => $complaint->id,
        'resident_id' => $resident->id,
        'category' => 'other',
        'relief_sought' => null,
        'incident_date' => null,
        'witnesses' => null,
    ]);
});

test('staff encoding stores private evidence and notifies officials', function () {
    Storage::fake('private');
    Notification::fake();
    $resident = User::factory()->create();
    $staff = User::factory()->create(['role' => 'staff']);

    $response = $this->actingAs($staff, 'staff')->post(route('staff.complaints.store'), [
        'resident_id' => $resident->id,
        'title' => 'Broken streetlight near the community hall',
        'category' => 'civil',
        'respondents' => 'Barangay maintenance office',
        'case_subject' => 'Unresolved community maintenance concern',
        'incident_date' => '2026-10-12',
        'witnesses' => 'Not applicable to a civil complaint.',
        'description' => 'The streetlight has been out for several nights and the road is very dark.',
        'location' => 'Near the community hall',
        'relief_sought' => 'Please repair the streetlight and inspect nearby lights.',
        'attachments' => [UploadedFile::fake()->image('streetlight.png')->size(800)],
    ]);

    $complaint = Complaint::query()->firstOrFail();
    $evidence = ComplaintEvidence::query()->firstOrFail();

    $response->assertRedirect(route('staff.complaints.show', $complaint));
    $this->assertDatabaseHas('complaints', [
        'id' => $complaint->id,
        'resident_id' => $resident->id,
        'status' => 'pending',
        'category' => 'civil',
        'respondents' => 'Barangay maintenance office',
        'case_subject' => 'Unresolved community maintenance concern',
        'relief_sought' => 'Please repair the streetlight and inspect nearby lights.',
        'incident_date' => null,
        'witnesses' => null,
    ]);
    $this->assertDatabaseHas('complaint_activities', ['complaint_id' => $complaint->id, 'action' => 'submitted']);
    Storage::disk('private')->assertExists($evidence->path);
    Notification::assertSentTo($staff, ComplaintAlert::class);
});

test('criminal complaint submission stores incident details and staff can print the filled form', function () {
    Notification::fake();
    $resident = User::factory()->create(['name' => 'Alex Resident']);
    $staff = User::factory()->create(['role' => 'staff']);

    $response = $this->actingAs($resident, 'resident')
        ->post(route('resident.complaints.store'), [
            'title' => 'Missing tools from the storage shed',
            'category' => 'criminal_case',
            'respondents' => 'Jordan Respondent',
            'case_subject' => 'Alleged theft',
            'relief_sought' => 'This field does not apply to a criminal case.',
            'incident_date' => '2026-10-12',
            'description' => 'Several tools went missing from the shared storage shed after the evening meeting.',
            'location' => 'Purok 2 storage shed',
            'witnesses' => 'Taylor Witness saw the shed being opened.',
        ]);

    $complaint = Complaint::query()->firstOrFail();

    $response->assertRedirect(route('resident.complaints.show', $complaint));
    $this->assertDatabaseHas('complaints', [
        'id' => $complaint->id,
        'category' => 'criminal_case',
        'respondents' => 'Jordan Respondent',
        'case_subject' => 'Alleged theft',
        'witnesses' => 'Taylor Witness saw the shed being opened.',
        'relief_sought' => null,
    ]);
    expect($complaint->incident_date->toDateString())->toBe('2026-10-12');

    $this->actingAs($staff, 'staff')
        ->get(route('staff.complaints.show', $complaint))
        ->assertSee('Print complaint')
        ->assertSee('Office of the Lupong Tagapamayapa')
        ->assertSee('Barangay Poblacion seal')
        ->assertSee('Municipality of Mahaplag seal')
        ->assertSee('Barangay Case No.')
        ->assertSee('Alex Resident')
        ->assertSee('Jordan Respondent')
        ->assertSee('Alleged theft')
        ->assertSee('HON. EDGARDO R. CAMASIN')
        ->assertSee('(Complainant/s)')
        ->assertDontSee('Location:')
        ->assertSee('size: A4 portrait', false)
        ->assertSee('paper-bottom', false)
        ->assertSee('paper-ruled', false)
        ->assertSee('repeating-linear-gradient', false)
        ->assertSee('Taylor Witness saw the shed being opened.');
});

test('staff can print a civil complaint', function () {
    $resident = User::factory()->create();
    $staff = User::factory()->create(['role' => 'staff']);
    $complaint = Complaint::factory()->for($resident, 'resident')->create([
        'category' => 'civil',
        'respondents' => 'Jordan Respondent',
        'case_subject' => 'Property boundary dispute',
        'relief_sought' => 'Request assistance to settle the boundary.',
    ]);

    $this->actingAs($staff, 'staff')
        ->get(route('staff.complaints.show', $complaint))
        ->assertSee('Print complaint')
        ->assertSee('Office of the Lupong Tagapamayapa')
        ->assertSee('HON. EDGARDO R. CAMASIN')
        ->assertDontSee('Location:')
        ->assertSee('paper-ruled', false)
        ->assertSee('repeating-linear-gradient', false)
        ->assertSee('Request assistance to settle the boundary.');
});

test('staff only see the printable form for civil and criminal complaints', function () {
    $resident = User::factory()->create();
    $staff = User::factory()->create(['role' => 'staff']);
    $complaint = Complaint::factory()->for($resident, 'resident')->create([
        'category' => 'infrastructure',
        'title' => 'Streetlight out along the market road',
    ]);

    $this->actingAs($staff, 'staff')
        ->get(route('staff.complaints.show', $complaint))
        ->assertDontSee('Print complaint')
        ->assertDontSee('Office of the Lupong Tagapamayapa')
        ->assertSee('Streetlight out along the market road')
        ->assertSee('Description');
});

test('civil complaint submission stores an empty relief request as null', function () {
    $resident = User::factory()->create();

    $response = $this->actingAs($resident, 'resident')
        ->post(route('resident.complaints.store'), [
            'title' => 'Boundary disagreement',
            'category' => 'civil',
            'respondents' => 'Jordan Respondent',
            'case_subject' => 'Property boundary dispute',
            'description' => 'The boundary marker between the adjoining properties has been moved.',
            'location' => 'Purok 1',
            'relief_sought' => '',
        ]);

    $complaint = Complaint::query()->firstOrFail();

    $response->assertRedirect(route('resident.complaints.show', $complaint));
    $this->assertDatabaseHas('complaints', [
        'id' => $complaint->id,
        'category' => 'civil',
        'relief_sought' => null,
    ]);
});

test('criminal complaint submission requires an incident date', function () {
    $resident = User::factory()->create();

    $this->actingAs($resident, 'resident')
        ->from(route('resident.complaints.create'))
        ->post(route('resident.complaints.store'), [
            'title' => 'Missing tools from the storage shed',
            'category' => 'criminal_case',
            'respondents' => 'Jordan Respondent',
            'case_subject' => 'Alleged theft',
            'description' => 'Several tools went missing from the shared storage shed after the evening meeting.',
            'location' => 'Purok 2 storage shed',
        ])
        ->assertRedirect(route('resident.complaints.create'))
        ->assertSessionHasErrors('incident_date');

    $this->assertDatabaseCount('complaints', 0);
});

test('staff approval records assignment and residents receive status updates', function () {
    Notification::fake();
    $resident = User::factory()->create();
    $staff = User::factory()->create(['role' => 'staff']);
    $assignedStaff = User::factory()->create(['role' => 'staff']);
    $complaint = Complaint::factory()->for($resident, 'resident')->create();

    $response = $this->actingAs($staff, 'staff')->post(route('staff.complaints.approve', $complaint), [
        'assigned_staff_id' => $assignedStaff->id,
    ]);

    $response->assertSessionHas('status', 'Complaint status updated.');
    $this->assertDatabaseHas('complaints', [
        'id' => $complaint->id,
        'status' => 'approved',
        'assigned_staff_id' => $assignedStaff->id,
    ]);
    $this->assertDatabaseHas('complaint_activities', [
        'complaint_id' => $complaint->id,
        'actor_id' => $staff->id,
        'from_status' => 'pending',
        'to_status' => 'approved',
    ]);
    expect($complaint->fresh()->staff_responded_at)->not->toBeNull();
    Notification::assertSentTo(
        $resident,
        ComplaintAlert::class,
        fn (ComplaintAlert $notification): bool => str_contains($notification->message, 'collect your receipt'),
    );

    $this->actingAs($resident, 'resident')
        ->get(route('resident.complaints.show', $complaint))
        ->assertSee('Upload your receipt proof')
        ->assertSee('Send proof to staff');
});

test('approved resident can upload private receipt proof for staff confirmation', function () {
    Storage::fake('private');
    Notification::fake();

    $resident = User::factory()->create();
    $staff = User::factory()->create(['role' => 'staff']);
    $complaint = Complaint::factory()
        ->for($resident, 'resident')
        ->create(['status' => 'approved', 'approved_at' => now()]);

    $this->actingAs($resident, 'resident')
        ->post(route('resident.complaints.receipt.store', $complaint), [
            'receipt_proof' => UploadedFile::fake()->image('receipt.png'),
        ])
        ->assertRedirect();

    $complaint->refresh();
    expect($complaint->receipt_uploaded_at)->not->toBeNull();
    Storage::disk('private')->assertExists($complaint->receipt_proof_path);
    $this->assertDatabaseHas('complaint_activities', [
        'complaint_id' => $complaint->id,
        'actor_id' => $resident->id,
        'action' => 'receipt_uploaded',
    ]);
    Notification::assertSentTo(
        $staff,
        ComplaintAlert::class,
        fn (ComplaintAlert $notification): bool => str_contains($notification->message, 'Confirm it before preparing the summons'),
    );
});

test('receipt upload rejects an unsupported document without saving it', function () {
    Storage::fake('private');
    $resident = User::factory()->create();
    $complaint = Complaint::factory()
        ->for($resident, 'resident')
        ->create(['status' => 'approved', 'approved_at' => now()]);

    $this->actingAs($resident, 'resident')
        ->from(route('resident.complaints.show', $complaint))
        ->post(route('resident.complaints.receipt.store', $complaint), [
            'receipt_proof' => UploadedFile::fake()->create('receipt.txt', 4, 'text/plain'),
        ])
        ->assertRedirect(route('resident.complaints.show', $complaint))
        ->assertSessionHasErrors('receipt_proof');

    expect($complaint->refresh()->receipt_proof_path)->toBeNull();
    Storage::disk('private')->assertDirectoryEmpty('complaints/'.$complaint->id.'/receipt');
});

test('receipt proof is visible only to staff and admins', function () {
    Storage::fake('private');
    $resident = User::factory()->create();
    $otherResident = User::factory()->create();
    $staff = User::factory()->create(['role' => 'staff']);
    $complaint = Complaint::factory()->for($resident, 'resident')->create([
        'status' => 'approved',
    ]);
    $receiptPath = 'complaints/'.$complaint->id.'/receipt/receipt.pdf';
    $complaint->update([
        'receipt_proof_path' => $receiptPath,
        'receipt_proof_original_name' => 'receipt.pdf',
        'receipt_proof_mime_type' => 'application/pdf',
    ]);
    Storage::disk('private')->put($receiptPath, 'receipt proof');

    $this->actingAs($resident, 'resident')
        ->get(route('complaint-receipt.show', ['complaint' => $complaint, 'guard' => 'resident']))
        ->assertForbidden();

    $this->actingAs($otherResident, 'resident')
        ->get(route('complaint-receipt.show', ['complaint' => $complaint, 'guard' => 'resident']))
        ->assertForbidden();

    $this->actingAs($staff, 'staff')
        ->get(route('complaint-receipt.show', ['complaint' => $complaint, 'guard' => 'staff']))
        ->assertOk();

    $admin = User::factory()->create(['role' => 'admin']);
    $this->actingAs($admin, 'admin')
        ->get(route('complaint-receipt.show', ['complaint' => $complaint, 'guard' => 'admin']))
        ->assertOk();
});

test('staff confirms uploaded receipt proof before the resident can view a summons', function () {
    Notification::fake();
    $resident = User::factory()->create();
    $staff = User::factory()->create(['role' => 'staff']);
    $complaint = Complaint::factory()
        ->for($resident, 'resident')
        ->create(['status' => 'approved', 'approved_at' => now()]);

    $this->actingAs($staff, 'staff')
        ->post(route('staff.complaints.receipt.confirm', $complaint))
        ->assertForbidden();

    $this->actingAs($resident, 'resident')
        ->post(route('resident.complaints.receipt.store', $complaint), [
            'receipt_proof' => UploadedFile::fake()->image('receipt.jpg'),
        ])
        ->assertRedirect();

    $this->actingAs($resident, 'resident')
        ->get(route('resident.complaints.summons', $complaint))
        ->assertRedirect(route('resident.complaints.show', $complaint));

    $this->actingAs($staff, 'staff')
        ->post(route('staff.complaints.receipt.confirm', $complaint))
        ->assertRedirect(route('staff.complaints.summons', $complaint))
        ->assertSessionHas('status', 'Receipt confirmed. Complete the summons details below.');

    $complaint->refresh();
    expect($complaint->receipt_confirmed_at)->not->toBeNull();
    expect($complaint->staff_responded_at)->not->toBeNull();
    $this->assertDatabaseHas('complaint_activities', [
        'complaint_id' => $complaint->id,
        'actor_id' => $staff->id,
        'action' => 'receipt_confirmed',
    ]);
    Notification::assertSentTo(
        $resident,
        ComplaintAlert::class,
        fn (ComplaintAlert $notification): bool => str_contains($notification->message, 'confirmed your receipt'),
    );
});

test('residents can view a prepared summons but only staff and admins can print it', function () {
    Notification::fake();
    $resident = User::factory()->create();
    $staff = User::factory()->create(['role' => 'staff']);
    $admin = User::factory()->create(['role' => 'admin']);
    $complaint = Complaint::factory()
        ->for($resident, 'resident')
        ->create([
            'status' => 'approved',
            'receipt_confirmed_at' => now(),
        ]);

    $this->actingAs($staff, 'staff')
        ->get(route('staff.complaints.summons', $complaint))
        ->assertOk()
        ->assertSee('Summons details');

    $summonsDetails = [
        'summons_case_number' => 'CV-24-2025',
        'summons_for' => 'Land dispute',
        'summons_hearing_date' => '2026-11-05',
        'summons_hearing_time' => '13:30',
        'summons_issued_date' => '2026-10-18',
        'summons_signatory' => 'Edgardo R. Camasin',
    ];

    $this->actingAs($staff, 'staff')
        ->post(route('staff.complaints.summons.update', $complaint), array_replace($summonsDetails, [
            'summons_issued_date' => '2026-11-06',
        ]))
        ->assertSessionHasErrors('summons_issued_date');

    $this->actingAs($staff, 'staff')
        ->post(route('staff.complaints.summons.update', $complaint), $summonsDetails)
        ->assertRedirect(route('staff.complaints.summons', $complaint))
        ->assertSessionHas('status', 'Summons details saved. You can now print the form.');

    $complaint->refresh();
    expect($complaint->summons_ready_at)->not->toBeNull();
    $this->assertDatabaseHas('complaints', [
        'id' => $complaint->id,
        'summons_case_number' => 'CV-24-2025',
        'summons_for' => 'Land dispute',
        'summons_hearing_time' => '13:30',
        'summons_signatory' => 'Edgardo R. Camasin',
    ]);
    expect($complaint->summons_hearing_date->toDateString())->toBe('2026-11-05');
    expect($complaint->summons_issued_date->toDateString())->toBe('2026-10-18');
    Notification::assertSentTo(
        $resident,
        ComplaintAlert::class,
        fn (ComplaintAlert $notification): bool => str_contains($notification->message, 'summons') && str_contains($notification->message, 'ready'),
    );

    $this->actingAs($staff, 'staff')
        ->get(route('staff.complaints.summons', $complaint))
        ->assertSee('Print summons')
        ->assertDontSee('resident-summons-view');

    $this->actingAs($admin, 'admin')
        ->get(route('admin.complaints.summons', $complaint))
        ->assertSee('Print summons')
        ->assertDontSee('resident-summons-view');

    $this->actingAs($resident, 'resident')
        ->get(route('resident.complaints.summons', $complaint))
        ->assertSee('SUMMONS')
        ->assertSee('CV-24-2025')
        ->assertSee('Land dispute')
        ->assertSee('Edgardo R. Camasin')
        ->assertDontSee('Print summons')
        ->assertSee('resident-summons-view', false)
        ->assertSee('.resident-summons-view {', false);
});

test('a second status action reports who handled the complaint first', function () {
    $resident = User::factory()->create();
    $firstStaff = User::factory()->create(['role' => 'staff', 'name' => 'Maria Official']);
    $secondStaff = User::factory()->create(['role' => 'staff']);
    $complaint = Complaint::factory()->for($resident, 'resident')->create();

    $this->actingAs($firstStaff, 'staff')
        ->post(route('staff.complaints.approve', $complaint))
        ->assertSessionHas('status');

    $this->actingAs($secondStaff, 'staff')
        ->post(route('staff.complaints.reject', $complaint), ['reason' => 'Not applicable'])
        ->assertSessionHas('error', 'Already handled by Maria Official.');

    $complaint->refresh();
    expect($complaint->status)->toBe('approved');
    expect(ComplaintActivity::query()->where('complaint_id', $complaint->id)->count())->toBe(1);
});

test('rejection requires a reason', function () {
    $resident = User::factory()->create();
    $staff = User::factory()->create(['role' => 'staff']);
    $complaint = Complaint::factory()->for($resident, 'resident')->create();

    $this->actingAs($staff, 'staff')
        ->post(route('staff.complaints.reject', $complaint))
        ->assertSessionHasErrors('reason');

    $this->assertDatabaseHas('complaints', ['id' => $complaint->id, 'status' => 'pending']);
});

test('a staff member can reject a complaint with a reason', function () {
    $resident = User::factory()->create();
    $staff = User::factory()->create(['role' => 'staff']);
    $complaint = Complaint::factory()->for($resident, 'resident')->create();

    $this->actingAs($staff, 'staff')
        ->post(route('staff.complaints.reject', $complaint), ['reason' => 'The reported location is outside the barangay.'])
        ->assertSessionHas('status');

    $this->assertDatabaseHas('complaints', [
        'id' => $complaint->id,
        'status' => 'rejected',
        'rejection_reason' => 'The reported location is outside the barangay.',
    ]);
});

test('a pending complaint cannot be resolved by its resident', function () {
    $resident = User::factory()->create();
    $complaint = Complaint::factory()->for($resident, 'resident')->create();

    $this->actingAs($resident, 'resident')
        ->post('/resident/complaints/'.$complaint->id.'/resolve')
        ->assertNotFound();

    $this->assertDatabaseHas('complaints', ['id' => $complaint->id, 'status' => 'pending']);
});

test('residents cannot approve a complaint', function () {
    $resident = User::factory()->create();
    $complaint = Complaint::factory()->for($resident, 'resident')->create();

    $this->actingAs($resident, 'resident')
        ->post(route('staff.complaints.approve', $complaint))
        ->assertRedirect(route('login'));

    $this->assertDatabaseHas('complaints', ['id' => $complaint->id, 'status' => 'pending']);
});

test('only staff and admins can view complaint evidence files', function () {
    Storage::fake('private');
    $owner = User::factory()->create();
    $otherResident = User::factory()->create();
    $complaint = Complaint::factory()->for($owner, 'resident')->create();
    $evidence = $complaint->evidence()->create([
        'path' => 'complaints/'.$complaint->id.'/private.jpg',
        'mime_type' => 'image/jpeg',
        'original_name' => 'private.jpg',
        'size' => 100,
    ]);
    Storage::disk('private')->put($evidence->path, 'private');

    $this->actingAs($owner, 'resident')
        ->get(route('complaint-evidence.show', ['evidence' => $evidence, 'guard' => 'resident']))
        ->assertForbidden();
    $this->get(route('resident.complaints.show', $complaint))
        ->assertDontSee('private.jpg')
        ->assertDontSee('Supporting evidence');

    $this->actingAs($otherResident, 'resident')
        ->get(route('complaint-evidence.show', ['evidence' => $evidence, 'guard' => 'resident']))
        ->assertForbidden();

    $staff = User::factory()->create(['role' => 'staff']);
    $this->actingAs($staff, 'staff')
        ->get(route('complaint-evidence.show', ['evidence' => $evidence, 'guard' => 'staff']))
        ->assertOk();

    $admin = User::factory()->create(['role' => 'admin']);
    $this->actingAs($admin, 'admin')
        ->get(route('complaint-evidence.show', ['evidence' => $evidence, 'guard' => 'admin']))
        ->assertOk();
});

test('a resident can exchange private comments with the barangay on their complaint', function () {
    $resident = User::factory()->create();
    $staff = User::factory()->create(['role' => 'staff']);
    $complaint = Complaint::factory()->for($resident, 'resident')->create();
    $body = "<script>alert('xss')</script> Please confirm whether this can be approved.";

    $response = $this->actingAs($resident, 'resident')
        ->post(route('resident.complaints.comments.store', $complaint), ['body' => $body]);

    $response->assertRedirect(route('resident.complaints.show', $complaint));
    $this->assertDatabaseHas('complaint_comments', [
        'complaint_id' => $complaint->id,
        'author_id' => $resident->id,
        'body' => $body,
    ]);

    $this->get(route('resident.complaints.show', $complaint))
        ->assertSee('<script>alert(\'xss\')</script>')
        ->assertSee('Private discussion')
        ->assertSeeInOrder([
            'Description',
            $complaint->description,
            'Private discussion',
            'Add a message',
            'Status timeline',
        ]);

    $this->actingAs($staff, 'staff')
        ->get(route('staff.complaints.show', $complaint))
        ->assertSee('Please confirm whether this can be approved.');
});

test('staff and admins can comment on complaint discussions', function () {
    $resident = User::factory()->create();
    $complaint = Complaint::factory()->for($resident, 'resident')->create();
    $staff = User::factory()->create(['role' => 'staff']);
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($staff, 'staff')
        ->post(route('staff.complaints.comments.store', $complaint), ['body' => 'Staff follow-up'])
        ->assertRedirect(route('staff.complaints.show', $complaint));

    $this->actingAs($admin, 'admin')
        ->post(route('admin.complaints.comments.store', $complaint), ['body' => 'Admin follow-up'])
        ->assertRedirect(route('admin.complaints.show', $complaint));

    $this->assertDatabaseHas('complaint_comments', [
        'complaint_id' => $complaint->id,
        'author_id' => $staff->id,
        'body' => 'Staff follow-up',
    ]);
    $this->assertDatabaseHas('complaint_comments', [
        'complaint_id' => $complaint->id,
        'author_id' => $admin->id,
        'body' => 'Admin follow-up',
    ]);

    $this->get(route('admin.complaints.show', $complaint))
        ->assertSee('Admin follow-up')
        ->assertSee('Private discussion');
});

test('another resident cannot read or add comments to a complaint discussion', function () {
    $owner = User::factory()->create();
    $otherResident = User::factory()->create();
    $complaint = Complaint::factory()->for($owner, 'resident')->create();
    $complaint->comments()->create([
        'author_id' => $owner->id,
        'body' => 'Private complaint details',
    ]);

    $this->actingAs($otherResident, 'resident')
        ->get(route('resident.complaints.show', $complaint))
        ->assertForbidden();

    $this->post(route('resident.complaints.comments.store', $complaint), ['body' => 'Unauthorized message'])
        ->assertForbidden();

    $this->assertDatabaseMissing('complaint_comments', [
        'complaint_id' => $complaint->id,
        'body' => 'Unauthorized message',
    ]);
});

test('a complaint comment requires a non-empty message', function () {
    $resident = User::factory()->create();
    $complaint = Complaint::factory()->for($resident, 'resident')->create();

    $this->actingAs($resident, 'resident')
        ->post(route('resident.complaints.comments.store', $complaint), ['body' => ''])
        ->assertSessionHasErrors('body');

    $this->assertDatabaseMissing('complaint_comments', ['complaint_id' => $complaint->id]);
});

test('resident cannot resolve an approved complaint', function () {
    $resident = User::factory()->create();
    $complaint = Complaint::factory()->for($resident, 'resident')->create([
        'status' => 'approved',
        'approved_at' => now()->subDay(),
        'staff_responded_at' => now()->subHour(),
    ]);

    $this->actingAs($resident, 'resident')
        ->get(route('resident.complaints.show', $complaint))
        ->assertDontSee('Mark as solved')
        ->assertDontSee('Your confirmation');

    $this->actingAs($resident, 'resident')
        ->post('/resident/complaints/'.$complaint->id.'/resolve')
        ->assertNotFound();

    $this->assertDatabaseHas('complaints', ['id' => $complaint->id, 'status' => 'approved']);
});

test('staff can mark an approved complaint as solved and notify the resident', function () {
    Notification::fake();
    $resident = User::factory()->create();
    $staff = User::factory()->create(['role' => 'staff']);
    $complaint = Complaint::factory()->for($resident, 'resident')->create([
        'status' => 'approved',
        'approved_at' => now()->subDay(),
    ]);

    $this->actingAs($staff, 'staff')
        ->get(route('staff.complaints.show', $complaint))
        ->assertSee('Mark as solved');

    $this->post(route('staff.complaints.resolve', $complaint))
        ->assertRedirect(route('staff.complaints.show', $complaint))
        ->assertSessionHas('status', 'Complaint status updated.');

    $this->assertDatabaseHas('complaints', ['id' => $complaint->id, 'status' => 'resolved']);
    expect($complaint->fresh()->resolved_at)->not->toBeNull();
    $this->assertDatabaseHas('complaint_activities', [
        'complaint_id' => $complaint->id,
        'actor_id' => $staff->id,
        'to_status' => 'resolved',
        'description' => 'Staff marked the complaint as resolved.',
    ]);
    Notification::assertSentTo($resident, ComplaintAlert::class, function (ComplaintAlert $notification): bool {
        return str_contains($notification->message, 'Staff marked complaint')
            && str_contains($notification->message, 'as solved');
    });
});

test('staff comments or evidence count as a response and allow resident resolution', function () {
    Storage::fake('private');
    $resident = User::factory()->create();
    $staff = User::factory()->create(['role' => 'staff']);
    $complaint = Complaint::factory()->for($resident, 'resident')->create([
        'status' => 'approved',
        'approved_at' => now()->subDay(),
    ]);

    $this->actingAs($staff, 'staff')
        ->post(route('staff.complaints.comments.store', $complaint), [
            'attachments' => [
                UploadedFile::fake()->image('repair.jpg'),
                UploadedFile::fake()->create('repair.mp4', 100, 'video/mp4'),
            ],
        ])
        ->assertRedirect(route('staff.complaints.show', $complaint));

    $complaint->refresh();
    expect($complaint->staff_responded_at)->not->toBeNull();

    $evidence = ComplaintEvidence::query()->where('complaint_id', $complaint->id)->get();
    expect($evidence)->toHaveCount(2);
    foreach ($evidence as $file) {
        expect($file->complaint_comment_id)->not->toBeNull();
        Storage::disk('private')->assertExists($file->path);
    }

    $this->actingAs($resident, 'resident')
        ->get(route('resident.complaints.show', $complaint))
        ->assertDontSee('repair.jpg')
        ->assertDontSee('repair.mp4')
        ->assertDontSee('Mark as solved');

    $this->actingAs($staff, 'staff')
        ->get(route('staff.complaints.show', $complaint))
        ->assertSee('repair.jpg')
        ->assertSee('repair.mp4');
});

test('a resident cannot attach evidence to a complaint discussion', function () {
    Storage::fake('private');
    $resident = User::factory()->create();
    $complaint = Complaint::factory()->for($resident, 'resident')->create([
        'status' => 'resolved',
        'resolved_at' => now(),
    ]);

    $this->actingAs($resident, 'resident')
        ->post(route('resident.complaints.comments.store', $complaint), [
            'body' => 'The area looks safe now.',
            'attachments' => [UploadedFile::fake()->image('after-repair.png')],
        ])
        ->assertSessionHasErrors('attachments');

    $this->assertDatabaseMissing('complaint_comments', ['complaint_id' => $complaint->id, 'body' => 'The area looks safe now.']);
    expect(ComplaintEvidence::query()->where('complaint_id', $complaint->id)->exists())->toBeFalse();
    expect(Storage::disk('private')->allFiles())->toBe([]);
});

test('resident can see receipt status without a resolve action while proof is pending', function () {
    $resident = User::factory()->create();
    $complaint = Complaint::factory()->for($resident, 'resident')->create([
        'status' => 'approved',
        'approved_at' => now()->subDay(),
    ]);

    $this->actingAs($resident, 'resident')
        ->get(route('resident.complaints.show', $complaint))
        ->assertSee('collect your receipt')
        ->assertDontSee('Mark as solved')
        ->assertDontSee('Your confirmation');
});

test('approved complaints without a staff response are closed after three days', function () {
    Notification::fake();
    $resident = User::factory()->create();
    $complaint = Complaint::factory()->for($resident, 'resident')->create([
        'status' => 'approved',
        'approved_at' => now()->subDays(3)->subMinute(),
    ]);

    $this->artisan('complaints:close-unanswered')->assertSuccessful();

    $this->assertDatabaseHas('complaints', [
        'id' => $complaint->id,
        'status' => 'closed',
    ]);
    $this->assertDatabaseHas('complaint_activities', [
        'complaint_id' => $complaint->id,
        'action' => 'auto_closed',
        'from_status' => 'approved',
        'to_status' => 'closed',
    ]);
    Notification::assertSentTo($resident, ComplaintAlert::class);
});

test('a staff response prevents the unanswered complaint auto-close', function () {
    $resident = User::factory()->create();
    $complaint = Complaint::factory()->for($resident, 'resident')->create([
        'status' => 'approved',
        'approved_at' => now()->subDays(4),
        'staff_responded_at' => now()->subDays(3),
    ]);

    $this->artisan('complaints:close-unanswered')->assertSuccessful();

    $this->assertDatabaseHas('complaints', [
        'id' => $complaint->id,
        'status' => 'approved',
    ]);
});

test('a resident can mark a complaint notification as read and it leaves the unread list', function () {
    $resident = User::factory()->create();
    $complaint = Complaint::factory()->for($resident, 'resident')->create();
    $resident->notify(new ComplaintAlert($complaint, 'A staff member updated your complaint.'));
    $notification = $resident->unreadNotifications()->firstOrFail();

    $this->actingAs($resident, 'resident')
        ->get(route('resident.complaints.show', $complaint))
        ->assertSee('A staff member updated your complaint.')
        ->assertSee('Read all')
        ->assertSee('Mark as read');

    $this->post(route('notifications.read', $notification->id))
        ->assertRedirect(route('resident.complaints.show', $complaint))
        ->assertSessionHas('status', 'Notification marked as read.');

    expect($notification->fresh()->read_at)->not->toBeNull();
    $this->actingAs($resident, 'resident')
        ->get(route('resident.complaints.show', $complaint))
        ->assertDontSee('A staff member updated your complaint.');
});

test('the notification dropdown includes unread notifications beyond the first five', function () {
    $resident = User::factory()->create();
    $complaint = Complaint::factory()->for($resident, 'resident')->create();

    foreach (range(1, 7) as $index) {
        $resident->notify(new ComplaintAlert($complaint, "Unread update {$index}."));
    }

    $this->actingAs($resident, 'resident')
        ->get(route('resident.complaints.show', $complaint))
        ->assertSee('Unread update 7.')
        ->assertSee('overflow-y-auto')
        ->assertSee('overscroll-contain');
});

test('read all marks only the current users notifications as read', function () {
    $resident = User::factory()->create();
    $anotherResident = User::factory()->create();
    $complaint = Complaint::factory()->for($resident, 'resident')->create();
    $anotherComplaint = Complaint::factory()->for($anotherResident, 'resident')->create();
    $resident->notify(new ComplaintAlert($complaint, 'Read this update.'));
    $anotherResident->notify(new ComplaintAlert($anotherComplaint, 'Keep this unread.'));

    $notification = $resident->unreadNotifications()->firstOrFail();
    $otherNotification = $anotherResident->unreadNotifications()->firstOrFail();

    $this->actingAs($resident, 'resident')
        ->post(route('notifications.read', $otherNotification->id))
        ->assertNotFound();

    expect($otherNotification->fresh()->read_at)->toBeNull();

    $this->actingAs($resident, 'resident')
        ->post(route('notifications.read-all'))
        ->assertSessionHas('status', 'All notifications marked as read.');

    expect($notification->fresh()->read_at)->not->toBeNull();
    expect($otherNotification->fresh()->read_at)->toBeNull();
});

test('staff comments reject file types other than photos and videos', function () {
    Storage::fake('private');
    $resident = User::factory()->create();
    $staff = User::factory()->create(['role' => 'staff']);
    $complaint = Complaint::factory()->for($resident, 'resident')->create();

    $this->actingAs($staff, 'staff')
        ->post(route('staff.complaints.comments.store', $complaint), [
            'body' => 'Please see this document.',
            'attachments' => [UploadedFile::fake()->create('notes.txt', 10, 'text/plain')],
        ])
        ->assertSessionHasErrors('attachments.0');

    $this->assertDatabaseMissing('complaint_comments', ['complaint_id' => $complaint->id]);
    expect(Storage::disk('private')->allFiles())->toBe([]);
});
