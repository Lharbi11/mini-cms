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
        ->assertSee('href="'.route('time').'"', false)
        ->assertSee('href="'.route('about').'"', false)
        ->assertSee('<a href="'.route('home').'" class="font-semibold text-red-600" aria-current="page">', false)
        ->assertSee('resources/css/app.css', false);
});

test('the posts page lists the in-memory articles and includes the shared navigation', function () {
    $this->get(route('posts.index'))
        ->assertOk()
        ->assertSee('<title>Tous les articles - Mini-CMS</title>', false)
        ->assertSeeText('Bienvenue sur Mini-CMS')
        ->assertSeeText('Comprendre le routage Laravel')
        ->assertSeeText('Les composants Blade')
        ->assertSeeText('Préparer la suite du projet')
        ->assertSeeText("Présentation du projet fil rouge de l'atelier et de ses objectifs.")
        ->assertSeeText('Par Équipe Mini-CMS · 01/10/2026')
        ->assertSeeText('À la une')
        ->assertSeeText('01/10/2026')
        ->assertSee('href="'.route('posts.index').'"', false)
        ->assertSee('href="'.route('posts.show', 'bienvenue-sur-mini-cms').'"', false)
        ->assertSee('href="'.route('posts.archive', 2026).'"', false);
});

test('the post detail displays the article matching its slug', function () {
    $this->get(route('posts.show', 'comprendre-le-routage'))
        ->assertOk()
        ->assertSee('<title>Comprendre le routage Laravel - Mini-CMS</title>', false)
        ->assertSeeText('03/10/2026')
        ->assertSeeText("Comment une URL arrive jusqu'à un contrôleur et une vue.")
        ->assertSeeText('Par Équipe Mini-CMS · 03/10/2026')
        ->assertSeeText('Les paramètres, comme le slug')
        ->assertSee('href="'.route('posts.index').'"', false);
});

test('the post archive filters articles by year and allows an empty year', function () {
    $this->get(route('posts.archive', 2026))
        ->assertOk()
        ->assertSee('<title>Articles de 2026 - Mini-CMS</title>', false)
        ->assertSeeText('Bienvenue sur Mini-CMS')
        ->assertSeeText('Préparer la suite du projet')
        ->assertSee('<a href="'.route('posts.index').'" class="font-semibold text-red-600" aria-current="page">', false)
        ->assertDontSeeText('Aucun article ne correspond à cette année.');

    $this->get(route('posts.archive', 2025))
        ->assertOk()
        ->assertSee('<title>Articles de 2025 - Mini-CMS</title>', false)
        ->assertSeeText('Aucun article ne correspond à cette année.')
        ->assertDontSeeText('Bienvenue sur Mini-CMS');
});

test('post routes return 404 for invalid years and unknown or invalid slugs', function () {
    $this->get('/posts/archive/abc')->assertNotFound();
    $this->get('/posts/Article')->assertNotFound();
    $this->get('/posts/article-inconnu')->assertNotFound();
});

test('the named time route displays the current time in the shared layout', function () {
    $this->travelTo(now()->setDate(2025, 1, 1)->setTime(14, 30));

    $this->get(route('time'))
        ->assertOk()
        ->assertSee('<title>Heure du serveur - Mini-CMS</title>', false)
        ->assertSeeText('Il est 14:30, le 01/01/2025.')
        ->assertSee('href="'.route('time').'"', false)
        ->assertSee('href="'.route('home').'"', false)
        ->assertSee('href="'.route('posts.index').'"', false)
        ->assertSee('href="'.route('about').'"', false);
});

test('the about page displays the author and group', function () {
    $this->get('/a-propos')
        ->assertOk()
        ->assertSee('<title>À propos - Mini-CMS</title>', false)
        ->assertSeeText('Projet pédagogique')
        ->assertSeeText("Ce site évolue à chaque session : les pages que vous voyez aujourd'hui seront reliées à une base de données dans la suite du cours.")
        ->assertSeeText('Données provisoires')
        ->assertSeeText("Les articles affichés viennent d'un tableau PHP écrit dans le contrôleur. Ils ne sont pas enregistrés.")
        ->assertSee('role="status" class="border-l-4 p-4 rounded border-blue-500 bg-blue-50 text-blue-900"', false)
        ->assertSee('role="status" class="border-l-4 p-4 rounded border-amber-500 bg-amber-50 text-amber-900"', false)
        ->assertSeeText('rayen harabi')
        ->assertSeeText('MDW33')
        ->assertSee('<a href="'.route('about').'" class="font-semibold text-red-600" aria-current="page">', false)
        ->assertSee('href="'.route('home').'"', false)
        ->assertSee('href="'.route('posts.index').'"', false)
        ->assertSee('href="'.route('time').'"', false)
        ->assertSee('href="'.route('about').'"', false)
        ->assertSee('Mini-CMS - Atelier Framework Côté Serveur');
});

test('the exercise routes are no longer available', function () {
    foreach (['/bonjour', '/bonjour-court', '/bienvenue', '/version'] as $uri) {
        $this->get($uri)->assertNotFound();
    }
});
