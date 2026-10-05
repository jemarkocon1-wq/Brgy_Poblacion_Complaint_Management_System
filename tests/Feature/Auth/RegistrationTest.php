<?php

use App\Models\User;
use Illuminate\Support\Facades\Notification;
use Laravel\Fortify\Features;

beforeEach(function () {
    $this->skipUnlessFortifyHas(Features::registration());
});

test('registration screen can be rendered', function () {
    $response = $this->get(route('register'));

    $response->assertOk();
});

test('new users can register', function () {
    Notification::fake();

    $response = $this->post(route('register.store'), [
        'name' => 'Test User',
        'email' => 'test.user@gmail.com',
        'phone' => '09171234567',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $this->assertAuthenticated();
    $this->assertAuthenticatedAs(User::query()->where('email', 'test.user@gmail.com')->first(), 'resident');
    $response->assertRedirect(route('resident.dashboard'));
    $this->assertDatabaseHas('users', [
        'email' => 'test.user@gmail.com',
        'role' => 'resident',
        'is_active' => true,
    ]);
    Notification::assertNothingSent();

    $this->get(route('resident.dashboard'))->assertOk();
});

test('registration rejects email addresses outside Gmail', function () {
    $this->post(route('register.store'), [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'phone' => '09171234567',
        'password' => 'password',
        'password_confirmation' => 'password',
    ])->assertSessionHasErrors('email');

    $this->assertDatabaseMissing('users', ['email' => 'test@example.com']);
});
