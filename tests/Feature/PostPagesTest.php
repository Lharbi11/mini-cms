<?php

use App\Models\Post;

beforeEach(function () {
    $this->withoutVite();
});

it('shows the posts list', function () {
    Post::create([
        'title' => 'Premier article',
        'slug' => 'premier-article',
        'body' => 'Contenu du premier article de test.',
        'is_published' => true,
        'published_at' => now(),
    ]);

    $this->get(route('posts.index'))
        ->assertOk()
        ->assertSee('Premier article');
});

it('shows a message when there is no post', function () {
    $this->get(route('posts.index'))
        ->assertOk()
        ->assertSee('Aucun article');
});

it('shows a single post', function () {
    $post = Post::create([
        'title' => 'Article complet',
        'slug' => 'article-complet',
        'body' => 'Texte complet de cet article de test.',
        'is_published' => true,
        'published_at' => now(),
    ]);

    $this->get(route('posts.show', $post))
        ->assertOk()
        ->assertSee('Texte complet de cet article de test.');
});

it('returns 404 for an unknown post', function () {
    $this->get('/posts/999')->assertNotFound();
});

it('shows the create form', function () {
    $this->get(route('posts.create'))
        ->assertOk()
        ->assertSee('Nouvel article');
});

it('shows only the three most recently published posts on the home page', function () {
    $this->travelTo('2026-10-29 12:00:00');

    Post::create([
        'title' => 'Publication la plus ancienne',
        'slug' => 'publication-plus-ancienne',
        'body' => 'Premier article publié de la liste récente.',
        'is_published' => true,
        'published_at' => now()->subDays(4),
    ]);

    Post::create([
        'title' => 'Publication récente trois',
        'slug' => 'publication-recente-trois',
        'body' => 'Troisième article publié le plus récemment.',
        'is_published' => true,
        'published_at' => now()->subDays(3),
    ]);

    Post::create([
        'title' => 'Publication récente deux',
        'slug' => 'publication-recente-deux',
        'body' => 'Deuxième article publié le plus récemment.',
        'is_published' => true,
        'published_at' => now()->subDays(2),
    ]);

    Post::create([
        'title' => 'Publication récente une',
        'slug' => 'publication-recente-une',
        'body' => 'Article publié le plus récemment.',
        'is_published' => true,
        'published_at' => now()->subDay(),
    ]);

    Post::create([
        'title' => 'Brouillon à masquer',
        'slug' => 'brouillon-a-masquer',
        'body' => 'Ce brouillon ne doit pas apparaître sur l’accueil.',
        'is_published' => false,
        'published_at' => now(),
    ]);

    $this->get(route('home'))
        ->assertOk()
        ->assertSeeText('Publication récente une')
        ->assertSeeText('Publication récente deux')
        ->assertSeeText('Publication récente trois')
        ->assertDontSeeText('Publication la plus ancienne')
        ->assertDontSeeText('Brouillon à masquer')
        ->assertSeeInOrder([
            'Publication récente une',
            'Publication récente deux',
            'Publication récente trois',
        ]);
});
