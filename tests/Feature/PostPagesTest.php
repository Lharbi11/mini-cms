<?php

use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;

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
        ->assertSee('Premier article')
        ->assertSee('Contenu du premier article de test.');
});

it('shows a post category, author, and tags on its card', function () {
    $user = User::factory()->create(['name' => 'Auteure associée']);
    $category = Category::factory()->create(['name' => 'Catégorie associée']);
    $tag = Tag::factory()->create(['name' => 'Tag associé']);
    $post = Post::factory()
        ->for($user)
        ->for($category)
        ->create(['title' => 'Article avec relations']);

    $post->tags()->attach($tag);

    $this->get(route('posts.index'))
        ->assertOk()
        ->assertSeeText('Catégorie associée')
        ->assertSeeText('Auteure associée')
        ->assertSeeText('Tag associé');
});

it('shows fallback labels when a post has no category or author', function () {
    Post::factory()->create(['title' => 'Article sans relations']);

    $this->get(route('posts.index'))
        ->assertOk()
        ->assertSeeText('Sans catégorie')
        ->assertSeeText('auteur inconnu');
});

it('shows a message when there is no post', function () {
    $this->get(route('posts.index'))
        ->assertOk()
        ->assertSee('Aucun article');
});

it('shows a single post', function () {
    $user = User::factory()->create(['name' => 'Auteur du détail']);
    $category = Category::factory()->create(['name' => 'Catégorie du détail']);
    $tag = Tag::factory()->create(['name' => 'Tag du détail']);
    $post = Post::factory()
        ->for($user)
        ->for($category)
        ->published()
        ->create([
            'title' => 'Article complet',
            'slug' => 'article-complet',
            'body' => 'Texte complet de cet article de test.',
        ]);

    $post->tags()->attach($tag);

    $this->get(route('posts.show', $post))
        ->assertOk()
        ->assertSee('Texte complet de cet article de test.')
        ->assertSeeText('Catégorie du détail')
        ->assertSeeText('Auteur du détail')
        ->assertSeeText('Tag du détail');
});

it('deletes a post after presenting a confirmation form', function () {
    $post = Post::factory()->create([
        'title' => 'Article à supprimer',
        'slug' => 'article-a-supprimer',
    ]);

    $this->get(route('posts.show', $post))
        ->assertOk()
        ->assertSee('action="'.route('posts.destroy', $post).'"', false)
        ->assertSee('name="_method" value="DELETE"', false)
        ->assertSeeText("Cette action est définitive : l'article sera effacé de la base.")
        ->assertSeeText('Confirmer la suppression');

    $this->delete(route('posts.destroy', $post))
        ->assertRedirect(route('posts.index'));

    $this->assertModelMissing($post);

    $this->get(route('posts.show', $post))
        ->assertNotFound();
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
