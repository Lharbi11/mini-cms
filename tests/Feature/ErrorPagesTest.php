<?php

use App\Models\Category;
use App\Models\User;

beforeEach(function () {
    $this->withoutVite();
});

it('shows the styled 404 page for an unknown URL', function () {
    $this->get('/page-inexistante')
        ->assertNotFound()
        ->assertSee('Page introuvable')
        ->assertSee('aria-label="Navigation principale"', false)
        ->assertSee("Retour à l'accueil", false);
});

it('shows existing categories when a category slug is unknown', function () {
    $php = Category::factory()->create(['name' => 'PHP', 'slug' => 'php']);
    $laravel = Category::factory()->create(['name' => 'Laravel', 'slug' => 'laravel']);

    $this->get(route('posts.category', 'inconnue'))
        ->assertNotFound()
        ->assertSee('Catégorie introuvable')
        ->assertSeeInOrder(['Laravel', 'PHP'])
        ->assertSee('href="'.route('posts.category', $laravel).'"', false)
        ->assertSee('href="'.route('posts.category', $php).'"', false);
});

it('keeps category not-found responses as JSON when requested', function () {
    $this->getJson(route('posts.category', 'inconnue'))
        ->assertNotFound()
        ->assertJsonStructure(['message']);

    $this->getJson('/api/inconnue')
        ->assertNotFound()
        ->assertJsonStructure(['message']);
});

it('shows the styled 503 page with its message when the site is read-only', function () {
    config(['site.read_only' => true]);

    $this->actingAs(User::factory()->create())
        ->post(route('posts.store'), [])
        ->assertStatus(503)
        ->assertSee('Service momentanément indisponible')
        ->assertSeeText('Le site est en lecture seule.')
        ->assertSee('aria-label="Navigation principale"', false)
        ->assertSee("Retour à l'accueil", false);
});
