<?php

use App\Models\Post;

beforeEach(function () {
    $this->withoutVite();
});

function feedbackPostData(array $overrides = []): array
{
    return array_merge([
        'title' => 'Article avec message',
        'slug' => 'article-avec-message',
        'body' => 'Contenu assez long pour passer la validation.',
        'is_published' => '1',
    ], $overrides);
}

it('shows a success message after creating a post', function () {
    $this->followingRedirects()
        ->post(route('posts.store'), feedbackPostData())
        ->assertOk()
        ->assertSee('Article créé.');
});

it('shows a success message after deleting a post', function () {
    $post = Post::factory()->create();

    $this->followingRedirects()
        ->delete(route('posts.destroy', $post))
        ->assertOk()
        ->assertSee('Article supprimé.');
});

it('shows the custom page for an unknown post', function () {
    $this->get('/posts/999')
        ->assertNotFound()
        ->assertSee('Article introuvable');
});

it('keeps the standard 404 page for an unknown category', function () {
    $this->get('/posts/categorie/inconnue')
        ->assertNotFound()
        ->assertDontSee('Article introuvable');
});

it('shows the read-only banner only when the site is read-only', function () {
    config(['site.read_only' => true]);

    $this->get(route('home'))
        ->assertOk()
        ->assertSeeText('Site en lecture seule');

    config(['site.read_only' => false]);

    $this->get(route('home'))
        ->assertOk()
        ->assertDontSeeText('Site en lecture seule');
});

it('shows recent published posts but not drafts on the missing post page', function () {
    $olderPublishedPost = Post::factory()->published()->create([
        'title' => 'Article publié le plus ancien',
        'published_at' => '2026-11-10 12:00:00',
    ]);
    $recentPublishedPosts = collect([
        Post::factory()->published()->create([
            'title' => 'Article publié récent 1',
            'published_at' => '2026-11-11 12:00:00',
        ]),
        Post::factory()->published()->create([
            'title' => 'Article publié récent 2',
            'published_at' => '2026-11-12 12:00:00',
        ]),
        Post::factory()->published()->create([
            'title' => 'Article publié récent 3',
            'published_at' => '2026-11-13 12:00:00',
        ]),
    ]);
    Post::factory()->create([
        'title' => 'Brouillon récent',
        'created_at' => '2026-11-14 12:00:00',
    ]);

    $this->get('/posts/999')
        ->assertNotFound()
        ->assertSeeText('Articles récents')
        ->assertSeeInOrder([
            'Article publié récent 3',
            'Article publié récent 2',
            'Article publié récent 1',
        ])
        ->assertDontSeeText('Article publié le plus ancien')
        ->assertDontSeeText('Brouillon récent');

    $olderPublishedPost->delete();
    $recentPublishedPosts->each->delete();

    $this->get('/posts/999')
        ->assertNotFound()
        ->assertDontSeeText('Articles récents');
});
