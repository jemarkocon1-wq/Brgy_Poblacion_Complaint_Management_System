<?php

use App\Models\Complaint;
use App\Models\ComplaintActivity;
use App\Models\User;

test('wrong credentials for a staff account do not reveal its portal from resident login', function () {
    $staff = User::factory()->create([
        'email' => 'staff@poblacion.local',
        'role' => 'staff',
    ]);

    $this->post(route('login.store'), [
        'email' => $staff->email,
        'password' => 'wrong-password',
    ])->assertSessionHasErrors('email');

    $this->assertGuest('resident');
});

test('a staff member using resident login is directed to the staff portal and can sign in', function () {
    $staff = User::factory()->create([
        'email' => 'staff@poblacion.local',
        'role' => 'staff',
    ]);

    $this->post(route('login.store'), [
        'email' => $staff->email,
        'password' => 'password',
    ])->assertRedirect(route('staff.login'));

    $this->get(route('staff.login'))
        ->assertOk()
        ->assertSee('This is a staff account.')
        ->assertSee('value="'.$staff->email.'"', false);

    $this->post(route('staff.login.store'), [
        'email' => $staff->email,
        'password' => 'password',
    ])->assertRedirect(route('staff.dashboard'));

    $this->assertAuthenticatedAs($staff, 'staff');
});

test('an admin using resident login is directed to the admin portal', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->post(route('login.store'), [
        'email' => $admin->email,
        'password' => 'password',
    ])->assertRedirect(route('admin.login'));
});

test('admin login has a distinct guard and remains available beside staff login', function () {
    $staff = User::factory()->create(['role' => 'staff', 'email' => 'staff@poblacion.local']);
    $admin = User::factory()->create(['role' => 'admin', 'email' => 'admin@poblacion.local']);

    $this->post(route('staff.login.store'), ['email' => $staff->email, 'password' => 'password']);
    $this->post(route('admin.login.store'), ['email' => $admin->email, 'password' => 'password']);

    $this->assertAuthenticatedAs($staff, 'staff');
    $this->assertAuthenticatedAs($admin, 'admin');
});

test('staff login goes to the staff dashboard when another portal was previously requested', function () {
    $staff = User::factory()->create(['role' => 'staff']);

    $this->get(route('admin.dashboard'))
        ->assertRedirect(route('admin.login'));

    $this->post(route('staff.login.store'), [
        'email' => $staff->email,
        'password' => 'password',
    ])->assertRedirect(route('staff.dashboard'));
});

test('admin login goes to the admin dashboard when another portal was previously requested', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->get(route('staff.dashboard'))
        ->assertRedirect(route('staff.login'));

    $this->post(route('admin.login.store'), [
        'email' => $admin->email,
        'password' => 'password',
    ])->assertRedirect(route('admin.dashboard'));
});

test('resident login goes to the resident dashboard when another portal was previously requested', function () {
    $resident = User::factory()->create(['role' => 'resident']);

    $this->get(route('admin.dashboard'))
        ->assertRedirect(route('admin.login'));

    $this->post(route('login.store'), [
        'email' => $resident->email,
        'password' => 'password',
    ])->assertRedirect(route('resident.dashboard'));
});

test('resident staff and admin workspaces render their pages', function () {
    $resident = User::factory()->create();
    $staff = User::factory()->create(['role' => 'staff', 'email' => 'workspace-staff@poblacion.local']);
    $admin = User::factory()->create(['role' => 'admin', 'email' => 'workspace-admin@poblacion.local']);
    $complaint = Complaint::factory()->for($resident, 'resident')->create();

    $this->actingAs($resident, 'resident')
        ->get(route('resident.dashboard'))
        ->assertOk()
        ->assertSee('Need assistance?')
        ->assertSee('Contact the barangay');
    $this->get(route('resident.complaints.index'))->assertOk();
    $this->get(route('resident.complaints.create'))->assertOk();
    $this->get(route('resident.complaints.show', $complaint))->assertOk();
    $this->get(route('resident.profile.edit'))->assertOk();

    $this->actingAs($staff, 'staff')
        ->get(route('staff.dashboard'))
        ->assertOk()
        ->assertDontSee('Need assistance?')
        ->assertDontSee('Contact the barangay');
    $this->get(route('staff.complaints.index'))->assertOk();
    $this->get(route('staff.assigned'))->assertOk();
    $this->get(route('staff.residents.index'))->assertOk();
    $this->get(route('staff.reports'))->assertOk();
    $this->get(route('staff.settings'))->assertOk();

    $this->actingAs($admin, 'admin')
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertDontSee('Need assistance?')
        ->assertDontSee('Contact the barangay');
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
