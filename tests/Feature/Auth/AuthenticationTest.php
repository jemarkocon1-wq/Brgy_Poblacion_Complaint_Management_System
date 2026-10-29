<?php

use App\Models\User;
use Illuminate\Support\Facades\RateLimiter;

test('login screen can be rendered', function () {
    $response = $this->get(route('login'));

    $response->assertOk();
});

test('users can authenticate using the login screen', function () {
    $user = User::factory()->create();

    $response = $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('resident.dashboard'));
});

test('residents and staff stay signed in through separate guards', function () {
    $resident = User::factory()->create();
    $staff = User::factory()->create(['role' => 'staff', 'email' => 'official@poblacion.local']);

    $this->post(route('login.store'), ['email' => $resident->email, 'password' => 'password'])
        ->assertRedirect(route('resident.dashboard'));
    $this->assertAuthenticatedAs($resident, 'resident');

    $this->post(route('admin.login.store'), ['email' => $staff->email, 'password' => 'password'])
        ->assertRedirect(route('staff.dashboard'));
    $this->assertAuthenticatedAs($resident, 'resident');
    $this->assertAuthenticatedAs($staff, 'staff');

    $this->post(route('staff.logout'))->assertRedirect(route('login'));
    $this->assertAuthenticatedAs($resident, 'resident');
    $this->assertGuest('staff');
});

test('users can not authenticate with invalid password', function () {
    $user = User::factory()->create();

    $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);

    $this->assertGuest();
});

test('users can logout', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('logout'));

    $response->assertRedirect(route('home'));

    $this->assertGuest();
});

test('users are rate limited', function () {
    $user = User::factory()->create();

    RateLimiter::increment(md5('role-login'.$user->email.'|127.0.0.1'), amount: 5);

    $response = $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);

    $response->assertTooManyRequests();
});
