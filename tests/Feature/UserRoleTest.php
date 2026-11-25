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

it('lets an admin list users and promote or demote another user', function () {
    $admin = User::factory()->admin()->create();
    $author = User::factory()->create([
        'name' => 'Auteur à promouvoir',
        'email' => 'auteur@example.com',
    ]);

    $this->withoutVite()
        ->actingAs($admin)
        ->get(route('admin.users.index'))
        ->assertOk()
        ->assertSeeText($author->name)
        ->assertSeeText($author->email)
        ->assertSeeText('author');

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('href="'.route('admin.users.index').'"', false);

    $this->patch(route('admin.users.role', $author), ['role' => 'admin'])
        ->assertRedirect(route('admin.users.index'))
        ->assertSessionHas('success', "Le rôle de {$author->name} a été mis à jour.");

    $this->assertDatabaseHas('users', [
        'id' => $author->id,
        'role' => 'admin',
    ]);

    $this->patch(route('admin.users.role', $author), ['role' => 'author'])
        ->assertRedirect(route('admin.users.index'))
        ->assertSessionHas('success', "Le rôle de {$author->name} a été mis à jour.");

    $this->assertDatabaseHas('users', [
        'id' => $author->id,
        'role' => 'author',
    ]);
});

it('forbids an admin from changing their own role', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->patch(route('admin.users.role', $admin), ['role' => 'author'])
        ->assertForbidden();

    $this->assertDatabaseHas('users', [
        'id' => $admin->id,
        'role' => 'admin',
    ]);
});
