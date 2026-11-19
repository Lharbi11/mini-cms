<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

beforeEach(function () {
    $this->withoutVite();
});

it('renders the registration form for guests', function () {
    $this->get(route('register'))
        ->assertOk()
        ->assertSeeText('Créer un compte')
        ->assertSee('name="password_confirmation"', false);
});

it('shows localized required errors when registration fields are empty', function () {
    $this->from(route('register'))
        ->followingRedirects()
        ->post(route('register.store'), [])
        ->assertOk()
        ->assertSeeText('Le champ nom est obligatoire.')
        ->assertSeeText('Le champ adresse e-mail est obligatoire.')
        ->assertSeeText('Le champ mot de passe est obligatoire.');

    $this->assertDatabaseCount('users', 0);
});

it('shows a localized error for an invalid email address', function () {
    $this->from(route('register'))
        ->post(route('register.store'), [
            'name' => 'Rayen',
            'email' => 'adresse-invalide',
            'password' => 'motdepasse123',
            'password_confirmation' => 'motdepasse123',
        ])
        ->assertRedirect(route('register'))
        ->assertSessionHasErrors([
            'email' => 'Le champ adresse e-mail doit être une adresse e-mail valide.',
        ]);

    $this->assertDatabaseCount('users', 0);
});

it('rejects an email address that is already registered', function () {
    User::factory()->create(['email' => 'rayen@example.com']);

    $this->from(route('register'))
        ->post(route('register.store'), [
            'name' => 'Rayen',
            'email' => 'rayen@example.com',
            'password' => 'motdepasse123',
            'password_confirmation' => 'motdepasse123',
        ])
        ->assertRedirect(route('register'))
        ->assertSessionHasErrors([
            'email' => 'La valeur du champ adresse e-mail est déjà utilisée.',
        ]);

    $this->assertDatabaseCount('users', 1);
});

it('rejects passwords that do not match their confirmation', function () {
    $this->from(route('register'))
        ->post(route('register.store'), [
            'name' => 'Rayen',
            'email' => 'rayen@example.com',
            'password' => 'motdepasse123',
            'password_confirmation' => 'autremotdepasse',
        ])
        ->assertRedirect(route('register'))
        ->assertSessionHasErrors([
            'password' => 'La confirmation du champ mot de passe ne correspond pas.',
        ]);

    $this->assertDatabaseCount('users', 0);
});

it('creates an author with a hashed password and logs them in', function () {
    $response = $this->post(route('register.store'), [
        'name' => 'Rayen',
        'email' => 'rayen@example.com',
        'password' => 'motdepasse123',
        'password_confirmation' => 'motdepasse123',
        'role' => 'admin',
    ]);

    $user = User::where('email', 'rayen@example.com')->firstOrFail();

    $response
        ->assertRedirect(route('posts.index'))
        ->assertSessionHas('success', 'Compte créé : bienvenue, Rayen.');

    $this->assertAuthenticatedAs($user);
    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'role' => 'author',
    ]);
    expect(Hash::check('motdepasse123', $user->password))->toBeTrue();

    $this->get(route('posts.index'))
        ->assertOk()
        ->assertSeeText('Compte créé : bienvenue, Rayen.');
});
