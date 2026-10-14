<?php

use App\Models\Complaint;
use App\Models\User;

test('admins can find older complaints by reference from the dashboard', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $matchingComplaint = Complaint::factory()->create([
        'reference' => 'BRGY-261010-PY8JRR',
        'title' => 'Older complaint',
        'created_at' => now()->subDays(10),
    ]);
    Complaint::factory()->count(8)->create([
        'created_at' => now()->subDays(2),
    ]);

    $response = $this->actingAs($admin, 'admin')
        ->get(route('admin.dashboard', ['search' => 'BRGY-261010-PY8JRR']));

    $response->assertOk()
        ->assertSee($matchingComplaint->title)
        ->assertSee('BRGY-261010-PY8JRR');
    expect($response->viewData('recentComplaints')->pluck('id')->all())
        ->toBe([$matchingComplaint->id]);
});

test('admins can search the complaints page by reference', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $matchingComplaint = Complaint::factory()->create([
        'reference' => 'BRGY-261010-PY8JRR',
        'title' => 'Searchable complaint',
    ]);
    Complaint::factory()->create(['reference' => 'BRGY-OTHER-123456']);

    $response = $this->actingAs($admin, 'admin')
        ->get(route('admin.complaints.index', ['search' => 'BRGY-261010-PY8JRR']));

    $response->assertOk()
        ->assertSee($matchingComplaint->title)
        ->assertSee('BRGY-261010-PY8JRR')
        ->assertDontSee('BRGY-OTHER-123456');
});

test('admins can add barangay staff accounts', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $response = $this->actingAs($admin, 'admin')
        ->from(route('admin.users.index'))
        ->post(route('admin.users.store'), [
            'name' => 'New Barangay Staff',
            'email' => 'new-staff@barangay.gov.ph',
            'phone' => '09123456789',
            'role' => 'staff',
            'password' => 'secure-password',
            'password_confirmation' => 'secure-password',
        ]);

    $response->assertRedirect(route('admin.users.index'));
    $response->assertSessionHas('status', 'User account created.');
    $this->assertDatabaseHas('users', [
        'name' => 'New Barangay Staff',
        'email' => 'new-staff@barangay.gov.ph',
        'role' => 'staff',
    ]);
});

test('admins cannot add resident accounts through add an account', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $response = $this->actingAs($admin, 'admin')
        ->post(route('admin.users.store'), [
            'name' => 'Resident Account',
            'email' => 'resident@gmail.com',
            'role' => 'resident',
            'password' => 'secure-password',
            'password_confirmation' => 'secure-password',
        ]);

    $response->assertSessionHasErrors('role');
    $this->assertDatabaseMissing('users', ['email' => 'resident@gmail.com']);
});
