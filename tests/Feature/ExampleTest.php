<?php

test('landing page provides one shared sign in entry point', function () {
    $response = $this->get(route('home'));

    $response->assertOk()
        ->assertSee('For urgent concerns, visit your barangay office.')
        ->assertSee(route('login'))
        ->assertDontSee(route('admin.login'))
        ->assertSee('Sign in')
        ->assertDontSee('Staff / Admin')
        ->assertDontSee('Staff portal');
});
