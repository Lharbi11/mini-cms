<?php

use App\Models\Post;

beforeEach(function () {
    $this->withoutVite();
});

function postData(array $overrides = []): array
{
    return array_merge([
        'title' => 'Article de test',
        'slug' => 'article-de-test',
        'excerpt' => 'Un extrait court.',
        'body' => 'Contenu assez long pour passer la validation.',
        'is_published' => '0',
    ], $overrides);
}

it('creates a post from the form', function () {
    $response = $this->post(route('posts.store'), postData());

    $post = Post::where('slug', 'article-de-test')->firstOrFail();

    $response->assertRedirect(route('posts.show', $post));
    $this->assertDatabaseHas('posts', [
        'id' => $post->id,
        'title' => 'Article de test',
        'slug' => 'article-de-test',
        'excerpt' => 'Un extrait court.',
    ]);
});

it('does not create a post without a title', function () {
    $this->from(route('posts.create'))
        ->post(route('posts.store'), postData(['title' => '']))
        ->assertRedirect(route('posts.create'))
        ->assertSessionHasErrors(['title' => 'The title field is required.']);

    $this->assertDatabaseMissing('posts', ['slug' => 'article-de-test']);
});

it('shows the edit form with the current values', function () {
    $post = Post::create(postData(['title' => 'Titre initial']));

    $this->get(route('posts.edit', $post))
        ->assertOk()
        ->assertSee('Titre initial')
        ->assertSee('value="article-de-test"', false)
        ->assertSee('value="Un extrait court."', false);
});

it('updates a post and keeps its own slug', function () {
    $post = Post::create(postData());

    $this->put(route('posts.update', $post), postData(['title' => 'Titre modifié']))
        ->assertRedirect(route('posts.show', $post));

    $this->assertDatabaseHas('posts', [
        'id' => $post->id,
        'title' => 'Titre modifié',
        'slug' => 'article-de-test',
        'excerpt' => 'Un extrait court.',
    ]);
});

it('refuses the slug of another post', function () {
    Post::create(postData(['slug' => 'slug-deja-pris']));
    $post = Post::create(postData());

    $this->from(route('posts.edit', $post))
        ->put(route('posts.update', $post), postData(['slug' => 'slug-deja-pris']))
        ->assertRedirect(route('posts.edit', $post))
        ->assertSessionHasErrors(['slug' => 'The slug has already been taken.']);

    $this->assertDatabaseHas('posts', [
        'id' => $post->id,
        'title' => 'Article de test',
        'slug' => 'article-de-test',
    ]);
});

it('deletes a post', function () {
    $post = Post::create(postData());

    $this->delete(route('posts.destroy', $post))
        ->assertRedirect(route('posts.index'));

    $this->assertDatabaseMissing('posts', ['id' => $post->id]);
});
