<?php

use App\Models\Post;
use App\Models\User;

beforeEach(function () {
    $this->withoutVite();
});

function authorizationPostData(array $overrides = []): array
{
    return array_merge([
        'title' => 'Article protege',
        'slug' => 'article-protege',
        'body' => 'Contenu assez long pour passer la validation.',
        'is_published' => '1',
    ], $overrides);
}

it('redirects a guest to the login page', function () {
    $post = Post::factory()->create();

    $this->get(route('posts.edit', $post))
        ->assertRedirect(route('login'));
});

it('records the logged-in user as the author', function () {
    $author = User::factory()->create();

    $this->actingAs($author)
        ->post(route('posts.store'), authorizationPostData())
        ->assertRedirect();

    $this->assertDatabaseHas('posts', [
        'slug' => 'article-protege',
        'user_id' => $author->id,
    ]);
});

it('lets an author edit their own post', function () {
    $author = User::factory()->create();
    $post = Post::factory()->create(['user_id' => $author->id]);

    $this->actingAs($author)
        ->get(route('posts.edit', $post))
        ->assertOk();
});

it('forbids an author to edit the post of another user', function () {
    $post = Post::factory()->create(['user_id' => User::factory()->create()->id]);

    $this->actingAs(User::factory()->create())
        ->get(route('posts.edit', $post))
        ->assertForbidden();
});

it('forbids an author to update the post of another user', function () {
    $post = Post::factory()->create(['user_id' => User::factory()->create()->id]);

    $this->actingAs(User::factory()->create())
        ->put(route('posts.update', $post), authorizationPostData())
        ->assertForbidden();

    $this->assertDatabaseMissing('posts', ['slug' => 'article-protege']);
});

it('forbids an author to delete the post of another user', function () {
    $post = Post::factory()->create(['user_id' => User::factory()->create()->id]);

    $this->actingAs(User::factory()->create())
        ->delete(route('posts.destroy', $post))
        ->assertForbidden();

    $this->assertDatabaseHas('posts', ['id' => $post->id]);
});

it('lets an admin update any post', function () {
    $post = Post::factory()->create(['user_id' => User::factory()->create()->id]);

    $this->actingAs(User::factory()->admin()->create())
        ->put(route('posts.update', $post), authorizationPostData())
        ->assertRedirect(route('posts.show', $post));

    $this->assertDatabaseHas('posts', ['id' => $post->id, 'slug' => 'article-protege']);
});

it('allows published posts to guests and draft posts only to their owner or admins', function () {
    $owner = User::factory()->create();
    $otherAuthor = User::factory()->create();
    $publishedPost = Post::factory()->published()->create();
    $ownedDraft = Post::factory()->for($owner)->create();
    $otherDraft = Post::factory()->for($otherAuthor)->create();

    $this->get(route('posts.show', $publishedPost))
        ->assertOk();

    $this->get(route('posts.show', $ownedDraft))
        ->assertForbidden();

    $this->actingAs($owner)
        ->get(route('posts.show', $ownedDraft))
        ->assertOk();

    $this->get(route('posts.show', $otherDraft))
        ->assertForbidden();

    $this->actingAs(User::factory()->admin()->create())
        ->get(route('posts.show', $otherDraft))
        ->assertOk();
});

it('filters the posts list by publication status and user ownership', function () {
    $owner = User::factory()->create();
    $otherAuthor = User::factory()->create();
    Post::factory()->published()->create(['title' => 'Article public']);
    Post::factory()->for($owner)->create(['title' => 'Brouillon du propriétaire']);
    Post::factory()->for($otherAuthor)->create(['title' => 'Brouillon différent']);

    $this->get(route('posts.index'))
        ->assertOk()
        ->assertSeeText('Article public')
        ->assertDontSeeText('Brouillon du propriétaire')
        ->assertDontSeeText('Brouillon différent');

    $this->actingAs($owner)
        ->get(route('posts.index'))
        ->assertOk()
        ->assertSeeText('Article public')
        ->assertSeeText('Brouillon du propriétaire')
        ->assertDontSeeText('Brouillon différent');

    $this->actingAs(User::factory()->admin()->create())
        ->get(route('posts.index'))
        ->assertOk()
        ->assertSeeText('Brouillon du propriétaire')
        ->assertSeeText('Brouillon différent');
});

it('restricts all admin back-office routes to administrators', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('admin.categories.index'))
        ->assertForbidden();

    $this->get(route('admin.tags.index'))
        ->assertForbidden()
        ->assertSeeText('Réservé aux administrateurs.');

    $this->get(route('home'))
        ->assertOk()
        ->assertDontSee('href="'.route('admin.categories.index').'"', false)
        ->assertDontSee('href="'.route('admin.tags.index').'"', false);

    $this->actingAs(User::factory()->admin()->create())
        ->get(route('admin.categories.index'))
        ->assertOk();

    $this->get(route('admin.tags.index'))
        ->assertOk();

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('href="'.route('admin.categories.index').'"', false)
        ->assertSee('href="'.route('admin.tags.index').'"', false);
});

it('lists only the authenticated user posts including drafts', function () {
    $author = User::factory()->create();
    $otherAuthor = User::factory()->create();
    Post::factory()->for($author)->create([
        'title' => 'Brouillon de mon compte',
        'is_published' => false,
    ]);
    Post::factory()->for($otherAuthor)->create([
        'title' => 'Article d’un autre compte',
    ]);

    $this->actingAs($author)
        ->get(route('my-posts'))
        ->assertOk()
        ->assertSeeText('Mes articles')
        ->assertSeeText('Brouillon de mon compte')
        ->assertSeeText('Brouillon')
        ->assertDontSeeText('Article d’un autre compte');
});

it('redirects guests from their posts page to login', function () {
    $this->get(route('my-posts'))
        ->assertRedirect(route('login'));
});
