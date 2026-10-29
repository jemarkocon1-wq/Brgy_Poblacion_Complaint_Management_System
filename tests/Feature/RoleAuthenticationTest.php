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

test('a staff member using resident login is signed in and shown the staff dashboard', function () {
    $staff = User::factory()->create([
        'email' => 'staff@poblacion.local',
        'role' => 'staff',
    ]);

    $this->followingRedirects()
        ->post(route('login.store'), [
            'email' => $staff->email,
            'password' => 'password',
        ])
        ->assertOk()
        ->assertSee('Every report is a chance to help.');

    $this->assertAuthenticatedAs($staff, 'staff');
});

test('residents staff and admins share one login page', function () {
    $this->get(route('login'))
        ->assertOk()
        ->assertViewIs('auth.login')
        ->assertSee('Community portal')
        ->assertSee(route('login.store'))
        ->assertDontSee('Resident portal');

    $this->get(route('staff.login'))
        ->assertRedirect(route('login'));

    $this->get(route('admin.login'))
        ->assertRedirect(route('login'));
});

test('an admin signs in through the shared login page and reaches the admin dashboard', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->post(route('login.store'), [
        'email' => $admin->email,
        'password' => 'password',
    ])->assertRedirect(route('admin.dashboard'));

    $this->assertAuthenticatedAs($admin, 'admin');
});

test('staff can sign in through the shared login page and reach the staff dashboard', function () {
    $staff = User::factory()->create(['role' => 'staff', 'email' => 'staff@poblacion.local']);

    $this->post(route('admin.login.store'), ['email' => $staff->email, 'password' => 'password'])
        ->assertRedirect(route('staff.dashboard'));

    $this->assertAuthenticatedAs($staff, 'staff');
    $this->assertGuest('admin');
});

test('admin can sign in through the shared login page and reach the admin dashboard', function () {
    $admin = User::factory()->create(['role' => 'admin', 'email' => 'admin@poblacion.local']);

    $this->post(route('admin.login.store'), ['email' => $admin->email, 'password' => 'password'])
        ->assertRedirect(route('admin.dashboard'));

    $this->assertAuthenticatedAs($admin, 'admin');
    $this->assertGuest('staff');
});

test('staff login goes to the staff dashboard when another portal was previously requested', function () {
    $staff = User::factory()->create(['role' => 'staff']);

    $this->get(route('admin.dashboard'))
        ->assertRedirect(route('login'));

    $this->post(route('staff.login.store'), [
        'email' => $staff->email,
        'password' => 'password',
    ])->assertRedirect(route('staff.dashboard'));
});

test('admin login goes to the admin dashboard when another portal was previously requested', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->get(route('staff.dashboard'))
        ->assertRedirect(route('login'));

    $this->post(route('admin.login.store'), [
        'email' => $admin->email,
        'password' => 'password',
    ])->assertRedirect(route('admin.dashboard'));
});

test('resident login goes to the resident dashboard when another portal was previously requested', function () {
    $resident = User::factory()->create(['role' => 'resident']);

    $this->get(route('admin.dashboard'))
        ->assertRedirect(route('login'));

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

test('reports are reviewable and include complainants and respondents for each complaint', function () {
    $resident = User::factory()->create(['name' => 'Ana Complainant']);
    $staff = User::factory()->create(['role' => 'staff', 'name' => 'Alpha Staff']);

    Complaint::factory()->for($resident, 'resident')->create([
        'title' => 'Broken streetlight along the road',
        'respondents' => 'Ramon Respondent',
        'witnesses' => 'Liza Witness',
        'assigned_staff_id' => $staff->id,
        'status' => 'approved',
    ]);

    $this->actingAs($staff, 'staff')
        ->get(route('staff.reports'))
        ->assertOk()
        ->assertSee('Complaint review')
        ->assertSee('Complainant')
        ->assertSee('Respondent(s)')
        ->assertSee('Ana Complainant')
        ->assertSee('Ramon Respondent')
        ->assertSee('Liza Witness');
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
