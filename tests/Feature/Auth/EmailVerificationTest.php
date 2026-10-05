<?php

use App\Models\User;
use Illuminate\Support\Facades\Notification;

test('resident dashboard is available to accounts without a verified email', function () {
    $resident = User::factory()->unverified()->create(['role' => 'resident']);

    $this->actingAs($resident, 'resident')
        ->get(route('resident.dashboard'))
        ->assertOk();
});

test('email verification endpoints are no longer available', function () {
    $this->get('/email/verify')->assertNotFound();
    $this->post('/email/verification-notification')->assertNotFound();
});

test('resident can update their Gmail address without receiving a verification email', function () {
    $resident = User::factory()->unverified()->create(['role' => 'resident']);
    Notification::fake();

    $this->actingAs($resident, 'resident')
        ->from(route('resident.profile.edit'))
        ->patch(route('resident.profile.update'), [
            'name' => $resident->name,
            'email' => 'updated.resident@gmail.com',
            'phone' => $resident->phone,
        ])
        ->assertRedirect(route('resident.profile.edit'));

    $this->assertDatabaseHas('users', [
        'id' => $resident->id,
        'email' => 'updated.resident@gmail.com',
        'email_verified_at' => null,
    ]);
    Notification::assertNothingSent();
});
