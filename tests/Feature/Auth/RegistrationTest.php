<?php

use Spatie\Permission\Models\Role;

beforeEach(function () {
    // Create roles needed for registration (only if they don't exist)
    Role::firstOrCreate(['name' => 'admin']);
    Role::firstOrCreate(['name' => 'user']);
});

test('registration screen can be rendered', function () {
    $response = $this->get('/inscription');

    $response->assertStatus(200);
});

test('new users can register', function () {
    $response = $this->post('/register', [
        'email' => 'test@example.com',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('verification.notice', absolute: false));
});
