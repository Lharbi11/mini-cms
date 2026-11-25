<?php

use App\Models\User;

beforeEach(function () {
    $this->withoutVite();
});

it('logs in and shows the authenticated user in the navigation', function () {
    $user = User::factory()->create([
        'name' => 'Amira Ben Salah',
        'email' => 'amira@example.com',
    ]);

    $this->post(route('login.store'), [
        'email' => 'amira@example.com',
        'password' => 'password',
    ])
        ->assertRedirect(route('posts.index'))
        ->assertSessionHas('success', 'Vous êtes connecté.');

    $this->assertAuthenticatedAs($user);

    $this->get(route('posts.index'))
        ->assertOk()
        ->assertSeeText('Amira Ben Salah')
        ->assertSeeText('Déconnexion')
        ->assertSeeText('Vous êtes connecté.');
});

it('shows a neutral error and keeps only the email after a failed login', function () {
    User::factory()->create(['email' => 'amira@example.com']);

    $this->from(route('login'))
        ->followingRedirects()
        ->post(route('login.store'), [
            'email' => 'amira@example.com',
            'password' => 'incorrect',
        ])
        ->assertOk()
        ->assertSeeText('Ces identifiants ne correspondent à aucun compte.')
        ->assertSee('value="amira@example.com"', false)
        ->assertDontSee('value="incorrect"', false);

    $this->assertGuest();
});

it('logs out and displays the guest navigation', function () {
    $user = User::factory()->create(['name' => 'Amira Ben Salah']);

    $this->actingAs($user)
        ->followingRedirects()
        ->post(route('logout'))
        ->assertOk()
        ->assertSeeText('Vous êtes déconnecté.')
        ->assertSeeText('Connexion')
        ->assertSeeText('Inscription')
        ->assertDontSeeText('Déconnexion')
        ->assertDontSeeText('Amira Ben Salah');

    $this->assertGuest();
});

it('redirects guests from post creation and admin pages to login', function () {
    $this->get(route('posts.create'))
        ->assertRedirect(route('login'));

    $this->post(route('posts.store'), [
        'title' => 'Article non autorisé',
    ])->assertRedirect(route('login'));

    $this->get(route('admin.categories.index'))
        ->assertRedirect(route('login'));

    $this->assertDatabaseCount('posts', 0);
});
