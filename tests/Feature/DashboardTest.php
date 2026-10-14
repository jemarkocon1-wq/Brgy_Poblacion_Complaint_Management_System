<?php

use App\Models\Complaint;
use App\Models\User;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('login'));
});

test('authenticated users can visit the dashboard', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('dashboard'));
    $response->assertOk();
});

test('residents can search all of their complaints by reference', function () {
    $resident = User::factory()->create();
    $reference = 'BRGY-261010-PY8JRR';
    $matchingComplaint = Complaint::factory()->for($resident, 'resident')->create([
        'reference' => $reference,
        'title' => 'Older complaint',
        'created_at' => now()->subDays(10),
    ]);
    Complaint::factory()->count(5)->for($resident, 'resident')->create([
        'created_at' => now()->subDays(2),
    ]);
    Complaint::factory()->for(User::factory(), 'resident')->create([
        'title' => $reference,
    ]);

    $response = $this->actingAs($resident, 'resident')
        ->get(route('resident.dashboard', ['search' => $reference]));

    $response->assertOk()
        ->assertSee($matchingComplaint->title)
        ->assertSee($reference)
        ->assertDontSee('No complaints found');
    expect($response->viewData('recentComplaints')->pluck('id')->all())
        ->toBe([$matchingComplaint->id]);
});

test('residents see an empty state when no complaints match their search', function () {
    $resident = User::factory()->create();
    Complaint::factory()->for($resident, 'resident')->create();

    $response = $this->actingAs($resident, 'resident')
        ->get(route('resident.dashboard', ['search' => 'BRGY-261010-PY8JRR']));

    $response->assertOk()
        ->assertSee('No complaints found for')
        ->assertSee('BRGY-261010-PY8JRR');
});
