<?php

use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

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

test('the posts page lists database articles with pagination and shared navigation', function () {
    Post::factory()->count(6)->sequence(
        ['title' => 'Article de test 4', 'slug' => 'article-de-test-4', 'body' => 'Corps de test 4', 'is_published' => true, 'published_at' => '2026-10-04'],
        ['title' => 'Article de test 3', 'slug' => 'article-de-test-3', 'body' => 'Corps de test 3'],
        ['title' => 'Article de test 2', 'slug' => 'article-de-test-2', 'body' => 'Corps de test 2', 'is_published' => true, 'published_at' => '2026-10-02'],
        ['title' => 'Article de test 1', 'slug' => 'article-de-test-1', 'body' => 'Corps de test 1'],
        ['title' => 'Les migrations Laravel', 'slug' => 'les-migrations-laravel', 'body' => 'Les migrations créent les tables.'],
        ['title' => 'Bienvenue sur Mini-CMS', 'slug' => 'bienvenue-sur-mini-cms', 'body' => 'Présentation du projet Mini-CMS.', 'is_published' => true, 'published_at' => '2026-10-01'],
    )->create();

    $response = $this->get(route('posts.index'));

    $response->assertOk()
        ->assertSee('<title>Tous les articles - Mini-CMS</title>', false)
        ->assertSeeText('Bienvenue sur Mini-CMS')
        ->assertSeeText('Les migrations Laravel')
        ->assertSeeText('Article de test 1')
        ->assertSeeText('Article de test 2')
        ->assertSeeText('Corps de test 2')
        ->assertSeeText('Publié le 02/10/2026')
        ->assertSeeText('Non publié')
        ->assertSeeText('Brouillon')
        ->assertSeeText('Showing 1 to 4 of 6 results')
        ->assertSeeText('Next')
        ->assertSeeText('Archive 2026')
        ->assertSeeText("Lire l'article")
        ->assertSee('<h1 class="text-3xl font-bold text-gray-900">Tous les articles</h1>', false)
        ->assertDontSeeText('Le projet Mini-CMS et ses objectifs.')
        ->assertSee('href="'.route('posts.index').'"', false)
        ->assertSee('href="'.route('posts.show', Post::where('slug', 'bienvenue-sur-mini-cms')->first()).'"', false)
        ->assertSee('href="'.route('posts.archive', 2026).'"', false);

    expect(substr_count($response->getContent(), '<article'))->toBe(4);

    $secondPage = $this->get(route('posts.index', ['page' => 2]));

    $secondPage->assertOk()
        ->assertSeeText('Article de test 3')
        ->assertSeeText('Article de test 4')
        ->assertSeeText('Previous')
        ->assertDontSeeText('Showing 1 to 4 of 6 results');

    expect(substr_count($secondPage->getContent(), '<article'))->toBe(2);
});

test('the post detail resolves its model from the route and displays database attributes', function () {
    $post = Post::factory()->create([
        'title' => 'Comprendre le routage Laravel',
        'slug' => 'comprendre-le-routage',
        'body' => "Une route associe une URL à une action.\nLe slug est transmis au contrôleur.",
        'is_published' => true,
        'published_at' => '2026-10-03 12:00:00',
    ]);

    $this->get(route('posts.show', $post))
        ->assertOk()
        ->assertSee('<title>Comprendre le routage Laravel - Mini-CMS</title>', false)
        ->assertSeeText('Publié le 03/10/2026')
        ->assertSeeText('Une route associe une URL à une action.')
        ->assertSeeText('Le slug est transmis au contrôleur.')
        ->assertSee('href="'.route('posts.index').'" class="text-sm text-indigo-600 hover:underline">Retour aux articles</a>', false)
        ->assertSeeInOrder([
            'href="'.route('posts.index').'"',
            'aria-current="page"',
            'bg-indigo-600 text-white',
            'Articles',
        ], false);
});

test('the post archive filters articles by year and allows an empty year', function () {
    Post::factory()->count(5)->sequence(
        ['title' => 'Article publié janvier', 'slug' => 'article-publie-janvier', 'is_published' => true, 'published_at' => '2026-01-10'],
        ['title' => 'Article publié juin', 'slug' => 'article-publie-juin', 'is_published' => true, 'published_at' => '2026-06-15'],
        ['title' => 'Article publié octobre', 'slug' => 'article-publie-octobre', 'is_published' => true, 'published_at' => '2026-10-20'],
        ['title' => 'Article publié 2025', 'slug' => 'article-publie-2025', 'is_published' => true, 'published_at' => '2025-12-31'],
        ['title' => 'Brouillon sans date', 'slug' => 'brouillon-sans-date'],
    )->create();

    $this->get(route('posts.archive', 2026))
        ->assertOk()
        ->assertSee('<title>Articles de 2026 - Mini-CMS</title>', false)
        ->assertSeeText('Article publié janvier')
        ->assertSeeText('Article publié juin')
        ->assertSeeText('Article publié octobre')
        ->assertDontSeeText('Article publié 2025')
        ->assertDontSeeText('Brouillon sans date')
        ->assertSeeInOrder([
            'href="'.route('posts.index').'"',
            'aria-current="page"',
            'bg-indigo-600 text-white',
            'Articles',
        ], false);

    $archive2025 = $this->get(route('posts.archive', 2025));

    $archive2025
        ->assertOk()
        ->assertSee('<title>Articles de 2025 - Mini-CMS</title>', false)
        ->assertSeeText('Article publié 2025')
        ->assertDontSeeText('Article publié janvier');

    Post::query()->delete();

    $this->get(route('posts.archive', 2025))
        ->assertOk()
        ->assertSeeText('Aucun article')
        ->assertSee('<h1 class="text-3xl font-bold text-gray-900">Articles de 2025</h1>', false)
        ->assertDontSeeText('Le projet Mini-CMS et ses objectifs.')
        ->assertSee('role="status"', false)
        ->assertDontSeeText('Bienvenue sur Mini-CMS');
});

test('the archive shows three published 2026 articles with the articles navigation active', function () {
    Post::factory()->count(3)->sequence(
        ['title' => 'Article publié 1', 'published_at' => '2026-01-10', 'is_published' => true],
        ['title' => 'Article publié 2', 'published_at' => '2026-06-15', 'is_published' => true],
        ['title' => 'Article publié 3', 'published_at' => '2026-10-20', 'is_published' => true],
    )->create();

    $response = $this->get(route('posts.archive', 2026));

    $response->assertOk()
        ->assertSee('<title>Articles de 2026 - Mini-CMS</title>', false)
        ->assertSeeText('Article publié 1')
        ->assertSeeText('Article publié 2')
        ->assertSeeText('Article publié 3')
        ->assertSeeInOrder([
            'href="'.route('posts.index').'"',
            'aria-current="page"',
            'bg-indigo-600 text-white',
            'Articles',
        ], false);
});

test('post routes return 404 for invalid years and unknown model ids', function () {
    $this->get('/posts/archive/abc')->assertNotFound();
    $this->get('/posts/999')->assertNotFound();
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
        ->assertSeeText("Ce site évolue à chaque session de l'atelier.")
        ->assertSee('role="status" class="border-l-4 p-4 rounded border-blue-500 bg-blue-50 text-blue-900"', false)
        ->assertSee('role="status" class="border-l-4 p-4 rounded border-green-500 bg-green-50 text-green-900"', false)
        ->assertSeeText('Données en base')
        ->assertSeeText('Les articles sont enregistrés dans une base SQLite et lus avec Eloquent.')
        ->assertDontSeeText('Données provisoires')
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
