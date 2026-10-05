<?php

use App\Models\Complaint;
use App\Models\ComplaintActivity;
use App\Models\ComplaintEvidence;
use App\Models\User;
use App\Notifications\ComplaintAlert;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;

test('resident complaint submission stores private evidence and notifies officials', function () {
    Storage::fake('private');
    Notification::fake();
    $resident = User::factory()->create();
    $staff = User::factory()->create(['role' => 'staff']);

    $response = $this->actingAs($resident, 'resident')->post(route('resident.complaints.store'), [
        'title' => 'Broken streetlight near the community hall',
        'category' => 'infrastructure',
        'description' => 'The streetlight has been out for several nights and the road is very dark.',
        'location' => 'Near the community hall',
        'attachments' => [UploadedFile::fake()->image('streetlight.png')->size(800)],
    ]);

    $complaint = Complaint::query()->firstOrFail();
    $evidence = ComplaintEvidence::query()->firstOrFail();

    $response->assertRedirect(route('resident.complaints.show', $complaint));
    $this->assertDatabaseHas('complaints', ['id' => $complaint->id, 'resident_id' => $resident->id, 'status' => 'pending']);
    $this->assertDatabaseHas('complaint_activities', ['complaint_id' => $complaint->id, 'action' => 'submitted']);
    Storage::disk('private')->assertExists($evidence->path);
    Notification::assertSentTo($staff, ComplaintAlert::class);
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
    Notification::assertSentTo($resident, ComplaintAlert::class);
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

test('a pending complaint cannot be resolved before approval', function () {
    $resident = User::factory()->create();
    $staff = User::factory()->create(['role' => 'staff']);
    $complaint = Complaint::factory()->for($resident, 'resident')->create();

    $this->actingAs($staff, 'staff')
        ->post(route('staff.complaints.resolve', $complaint))
        ->assertSessionHas('error', 'A complaint must be approved before it can be resolved.');

    $this->assertDatabaseHas('complaints', ['id' => $complaint->id, 'status' => 'pending']);
});

test('residents cannot approve a complaint', function () {
    $resident = User::factory()->create();
    $complaint = Complaint::factory()->for($resident, 'resident')->create();

    $this->actingAs($resident, 'resident')
        ->post(route('staff.complaints.approve', $complaint))
        ->assertRedirect(route('staff.login'));

    $this->assertDatabaseHas('complaints', ['id' => $complaint->id, 'status' => 'pending']);
});

test('residents cannot view another resident evidence file', function () {
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
        ->assertOk();

    $this->actingAs($otherResident, 'resident')
        ->get(route('complaint-evidence.show', ['evidence' => $evidence, 'guard' => 'resident']))
        ->assertForbidden();
});
