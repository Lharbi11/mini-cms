<?php

use App\Models\User;
use Database\Seeders\DatabaseSeeder;

it('creates authors by default and admins through the factory state', function () {
    $author = User::factory()->create();
    $admin = User::factory()->admin()->create();

    expect($author->role)->toBe('author')
        ->and($author->isAdmin())->toBeFalse()
        ->and($admin->role)->toBe('admin')
        ->and($admin->isAdmin())->toBeTrue();
});

it('does not allow the role to be mass assigned', function () {
    $user = User::create([
        'name' => 'Test User',
        'email' => 'mass-assignment@example.com',
        'password' => 'password',
        'role' => 'admin',
    ]);

    expect($user->fresh()->role)->toBe('author')
        ->and($user->isAdmin())->toBeFalse();
});

it('seeds the designated test account as an admin', function () {
    $this->seed(DatabaseSeeder::class);

    $this->assertDatabaseHas('users', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'role' => 'admin',
    ]);
});
