<?php

test('the application returns a successful response', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee('<title>Accueil - Mini-CMS</title>', false)
        ->assertSeeText('Bienvenue sur Mini-CMS')
        ->assertSeeText('Une petite plateforme de publication construite pas à pas')
        ->assertSee('href="'.route('posts.index').'"', false)
        ->assertSeeText('Lire les articles')
        ->assertSeeText('Sprint en cours')
        ->assertSeeText('Les composants Blade évitent de recopier le même code HTML dans chaque page.')
        ->assertSee('bg-green-50 text-green-900', false)
        ->assertSee('Mini-CMS - Atelier Framework Côté Serveur');
});

test('the home page renders the shared layout and links to the about page', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee('href="'.route('home').'"', false)
        ->assertSee('href="'.route('posts.index').'"', false)
        ->assertSee('href="'.route('time').'"', false)
        ->assertSee('href="'.route('about').'"', false)
        ->assertSeeInOrder([
            'href="'.route('home').'"',
            'aria-current="page"',
            'bg-indigo-600 text-white',
            'Accueil',
        ], false)
        ->assertSee('resources/css/app.css', false);
});

test('the posts page lists the in-memory articles and includes the shared navigation', function () {
    $response = $this->get(route('posts.index'));

    $response->assertOk()
        ->assertSee('<title>Tous les articles - Mini-CMS</title>', false)
        ->assertSeeText('Bienvenue sur Mini-CMS')
        ->assertSeeText('Comprendre le routage Laravel')
        ->assertSeeText('Les composants Blade')
        ->assertSeeText('Préparer la suite du projet')
        ->assertSeeText("Présentation du projet fil rouge de l'atelier et de ses objectifs.")
        ->assertSeeText('Équipe Mini-CMS - 01/10/2026')
        ->assertSeeText('Archive 2026')
        ->assertSeeText("Lire l'article")
        ->assertSee('<h1 class="text-3xl font-bold text-gray-900">Tous les articles</h1>', false)
        ->assertDontSeeText('Le projet Mini-CMS et ses objectifs.')
        ->assertSee('href="'.route('posts.index').'"', false)
        ->assertSee('href="'.route('posts.show', 'bienvenue-sur-mini-cms').'"', false)
        ->assertSee('href="'.route('posts.archive', 2026).'"', false);

    expect(substr_count($response->getContent(), '<article'))->toBe(4)
        ->and(substr_count($response->getContent(), 'À la une'))->toBe(1);
});

test('the post detail displays the article matching its slug', function () {
    $this->get(route('posts.show', 'comprendre-le-routage'))
        ->assertOk()
        ->assertSee('<title>Comprendre le routage Laravel - Mini-CMS</title>', false)
        ->assertSeeText('Équipe Mini-CMS - 03/10/2026')
        ->assertSeeText('Une route associe une URL à une action.')
        ->assertSeeText('Les paramètres, comme le slug')
        ->assertSee('href="'.route('posts.index').'" class="text-sm text-indigo-600 hover:underline">Retour aux articles</a>', false)
        ->assertSeeInOrder([
            'href="'.route('posts.index').'"',
            'aria-current="page"',
            'bg-indigo-600 text-white',
            'Articles',
        ], false);
});

test('the post archive filters articles by year and allows an empty year', function () {
    $this->get(route('posts.archive', 2026))
        ->assertOk()
        ->assertSee('<title>Articles de 2026 - Mini-CMS</title>', false)
        ->assertSeeText('Bienvenue sur Mini-CMS')
        ->assertSeeText('Préparer la suite du projet')
        ->assertSeeInOrder([
            'href="'.route('posts.index').'"',
            'aria-current="page"',
            'bg-indigo-600 text-white',
            'Articles',
        ], false)
        ->assertDontSeeText('Aucun article ne correspond à cette année.');

    $this->get(route('posts.archive', 2025))
        ->assertOk()
        ->assertSee('<title>Articles de 2025 - Mini-CMS</title>', false)
        ->assertSeeText('Aucun article')
        ->assertSeeText("Il n'y a pas encore d'article publié.")
        ->assertSee('<h1 class="text-3xl font-bold text-gray-900">Articles de 2025</h1>', false)
        ->assertDontSeeText('Le projet Mini-CMS et ses objectifs.')
        ->assertSee('role="status"', false)
        ->assertDontSeeText('Bienvenue sur Mini-CMS');
});

test('the 2026 archive shows four article cards with the articles navigation active', function () {
    $response = $this->get(route('posts.archive', 2026));

    $response->assertOk()
        ->assertSee('<title>Articles de 2026 - Mini-CMS</title>', false)
        ->assertSeeInOrder([
            'href="'.route('posts.index').'"',
            'aria-current="page"',
            'bg-indigo-600 text-white',
            'Articles',
        ], false);

    expect(substr_count($response->getContent(), '<article'))->toBe(4);
});

test('post routes return 404 for invalid years and unknown or invalid slugs', function () {
    $this->get('/posts/archive/abc')->assertNotFound();
    $this->get('/posts/Article')->assertNotFound();
    $this->get('/posts/article-inconnu')->assertNotFound();
    $this->get('/posts/inconnu')->assertNotFound();
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
        ->assertSee('<h1 class="text-3xl font-bold text-gray-900">À propos</h1>', false)
        ->assertSeeText('Le projet Mini-CMS et ses objectifs.')
        ->assertSeeText('Projet pédagogique')
        ->assertSeeText("Ce site évolue à chaque session : les pages que vous voyez aujourd'hui seront reliées à une base de données dans la suite du cours.")
        ->assertSeeText('Données provisoires')
        ->assertSeeText("Les articles affichés viennent d'un tableau PHP écrit dans le contrôleur. Ils ne sont pas enregistrés.")
        ->assertSee('role="status" class="border-l-4 p-4 rounded border-blue-500 bg-blue-50 text-blue-900"', false)
        ->assertSee('role="status" class="border-l-4 p-4 rounded border-amber-500 bg-amber-50 text-amber-900"', false)
        ->assertSeeText('rayen harabi')
        ->assertSeeText('MDW33')
        ->assertSeeInOrder([
            'href="'.route('about').'"',
            'aria-current="page"',
            'bg-indigo-600 text-white',
            'À propos',
        ], false)
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
