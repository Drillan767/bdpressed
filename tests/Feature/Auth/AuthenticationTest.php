<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

test('login screen can be rendered', function () {
    $response = $this->get('/connexion');

    $response->assertStatus(200);
});

test('users can authenticate using the login screen', function () {
    Role::create(['name' => 'user']);
    Role::create(['name' => 'admin']);

    $user = User::factory()->create();
    $user->assignRole('user');

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('user.dashboard', absolute: false));
});

test('users can not authenticate with invalid password', function () {
    $user = User::factory()->create();

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);

    $this->assertGuest();
});

test('users can logout', function () {
    Role::create(['name' => 'user']);
    Role::create(['name' => 'admin']);

    $user = User::factory()->create();
    $user->assignRole('user');

    $response = $this->actingAs($user)->post('/logout');

    $this->assertGuest();
    $response->assertRedirect('/');
});
