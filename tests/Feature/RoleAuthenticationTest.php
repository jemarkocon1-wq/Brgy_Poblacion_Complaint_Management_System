<?php

use App\Models\Complaint;
use App\Models\ComplaintActivity;
use App\Models\User;

test('staff accounts cannot sign in through the resident login page', function () {
    $staff = User::factory()->create([
        'email' => 'staff@poblacion.local',
        'role' => 'staff',
    ]);

    $this->post(route('login.store'), [
        'email' => $staff->email,
        'password' => 'password',
    ])->assertSessionHasErrors('email');

    $this->assertGuest('resident');
});

test('admin login has a distinct guard and remains available beside staff login', function () {
    $staff = User::factory()->create(['role' => 'staff', 'email' => 'staff@poblacion.local']);
    $admin = User::factory()->create(['role' => 'admin', 'email' => 'admin@poblacion.local']);

    $this->post(route('staff.login.store'), ['email' => $staff->email, 'password' => 'password']);
    $this->post(route('admin.login.store'), ['email' => $admin->email, 'password' => 'password']);

    $this->assertAuthenticatedAs($staff, 'staff');
    $this->assertAuthenticatedAs($admin, 'admin');
});

test('resident staff and admin workspaces render their pages', function () {
    $resident = User::factory()->create();
    $staff = User::factory()->create(['role' => 'staff', 'email' => 'workspace-staff@poblacion.local']);
    $admin = User::factory()->create(['role' => 'admin', 'email' => 'workspace-admin@poblacion.local']);
    $complaint = Complaint::factory()->for($resident, 'resident')->create();

    $this->actingAs($resident, 'resident')
        ->get(route('resident.dashboard'))
        ->assertOk();
    $this->get(route('resident.complaints.index'))->assertOk();
    $this->get(route('resident.complaints.create'))->assertOk();
    $this->get(route('resident.complaints.show', $complaint))->assertOk();
    $this->get(route('resident.profile.edit'))->assertOk();

    $this->actingAs($staff, 'staff')
        ->get(route('staff.dashboard'))
        ->assertOk();
    $this->get(route('staff.complaints.index'))->assertOk();
    $this->get(route('staff.assigned'))->assertOk();
    $this->get(route('staff.residents.index'))->assertOk();
    $this->get(route('staff.reports'))->assertOk();
    $this->get(route('staff.settings'))->assertOk();

    $this->actingAs($admin, 'admin')
        ->get(route('admin.dashboard'))
        ->assertOk();
    $this->get(route('admin.complaints.index'))->assertOk();
    $this->get(route('admin.users.index'))->assertOk();
    $this->get(route('admin.reports'))->assertOk();
    $this->get(route('admin.information'))->assertOk();
    $this->get(route('admin.activity'))->assertOk();
    $this->get(route('admin.settings'))->assertOk();
    $this->get(route('admin.reports.csv'))->assertDownload('barangay-complaints.csv');
    $this->get(route('admin.reports.pdf'))->assertDownload('barangay-complaints.pdf');
});

test('admin can filter users, reports and activity logs', function () {
    $admin = User::factory()->create(['role' => 'admin', 'email' => 'filter-admin@poblacion.local']);
    $staff = User::factory()->create([
        'role' => 'staff',
        'name' => 'Alpha Staff',
        'email' => 'alpha-staff@poblacion.local',
    ]);

    ComplaintActivity::query()->create([
        'actor_id' => $admin->id,
        'action' => 'user.created',
        'description' => 'Created staff account for Alpha Staff.',
    ]);

    $this->actingAs($admin, 'admin')
        ->get(route('admin.users.index', ['role' => 'staff', 'search' => 'Alpha']))
        ->assertOk();

    $this->get(route('admin.activity', ['action' => 'user.created', 'search' => 'Alpha']))
        ->assertOk();

    $this->get(route('admin.reports', ['status' => 'pending', 'start_date' => now()->subWeek()->toDateString(), 'end_date' => now()->toDateString()]))
        ->assertOk();

    $this->get(route('admin.settings'))->assertOk();
});
