<?php

test('the application returns a successful response', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee('<title>Accueil - Mini-CMS</title>', false)
        ->assertSee('Mini-CMS - Atelier Framework Côté Serveur');
});

test('the home page renders the shared layout and links to the about page', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee('href="'.route('home').'"', false)
        ->assertSee('href="'.route('posts.index').'"', false)
        ->assertSee('href="'.route('about').'"', false)
        ->assertSee('resources/css/app.css', false);
});

test('the posts page lists the in-memory articles and includes the shared navigation', function () {
    $this->get(route('posts.index'))
        ->assertOk()
        ->assertSee('<title>Tous les articles - Mini-CMS</title>', false)
        ->assertSeeText('Découvrir Laravel 13')
        ->assertSeeText('Routes et contrôleurs')
        ->assertSeeText('Premiers pas avec Blade')
        ->assertSeeText('Publié en 2026')
        ->assertSeeText('Publié en 2025')
        ->assertSee('href="'.route('posts.index').'"', false);
});

test('the heure route displays the current time', function () {
    $this->travelTo(now()->setDate(2025, 1, 1)->setTime(14, 30));

    $this->get('/heure')
        ->assertOk()
        ->assertSeeText('14:30')
        ->assertSeeText('01/01/2025');
});

test('the about page displays the author and group', function () {
    $this->get('/a-propos')
        ->assertOk()
        ->assertSee('<title>À propos - Mini-CMS</title>', false)
        ->assertSeeText('rayen harabi')
        ->assertSeeText('MDW33')
        ->assertSee('href="'.route('home').'"', false)
        ->assertSee('href="'.route('posts.index').'"', false)
        ->assertSee('href="'.route('about').'"', false)
        ->assertSee('Mini-CMS - Atelier Framework Côté Serveur');
});

test('the exercise routes are no longer available', function () {
    foreach (['/bonjour', '/bonjour-court', '/bienvenue', '/version'] as $uri) {
        $this->get($uri)->assertNotFound();
    }
});
