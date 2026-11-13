<?php

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

it('shows the styled 503 page with its message when the site is read-only', function () {
    config(['site.read_only' => true]);

    $this->post(route('posts.store'), [])
        ->assertStatus(503)
        ->assertSee('Service momentanément indisponible')
        ->assertSeeText('Le site est en lecture seule.')
        ->assertSee('aria-label="Navigation principale"', false)
        ->assertSee("Retour à l'accueil", false);
});
