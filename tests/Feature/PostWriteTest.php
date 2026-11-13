<?php

use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;

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

    $response
        ->assertRedirect(route('posts.show', $post))
        ->assertSessionHas('success', 'Article créé.');

    $this->get(route('posts.show', $post))
        ->assertOk()
        ->assertSee('role="status"', false)
        ->assertSeeText('Article créé.');

    $this->get(route('posts.show', $post))
        ->assertDontSeeText('Article créé.');
    $this->assertDatabaseHas('posts', [
        'id' => $post->id,
        'title' => 'Article de test',
        'slug' => 'article-de-test',
        'excerpt' => 'Un extrait court.',
    ]);
});

it('creates a post with a selected category', function () {
    $category = Category::factory()->create(['name' => 'PHP']);

    $this->post(route('posts.store'), postData(['category_id' => $category->id]))
        ->assertRedirect(route('posts.show', Post::where('slug', 'article-de-test')->firstOrFail()));

    $this->assertDatabaseHas('posts', [
        'slug' => 'article-de-test',
        'category_id' => $category->id,
    ]);
});

it('lists categories alphabetically with an optional empty selection', function () {
    Category::factory()->create(['name' => 'Tutoriels']);
    Category::factory()->create(['name' => 'Laravel']);
    Tag::factory()->create(['name' => 'Sécurité', 'slug' => 'securite']);
    Tag::factory()->create(['name' => 'Astuce', 'slug' => 'astuce']);

    $response = $this->get(route('posts.create'));

    $response
        ->assertOk()
        ->assertSeeInOrder([
            'Aucune catégorie',
            'Laravel',
            'Tutoriels',
        ]);

    $response->assertSeeInOrder([
        'Astuce',
        'Sécurité',
    ]);
});

it('creates a post with selected tags', function () {
    $tags = Tag::factory()->count(2)->sequence(
        ['name' => 'Astuce', 'slug' => 'astuce'],
        ['name' => 'Sécurité', 'slug' => 'securite'],
    )->create();

    $this->post(route('posts.store'), postData([
        'tags' => $tags->pluck('id')->all(),
    ]))->assertRedirect(route('posts.show', Post::where('slug', 'article-de-test')->firstOrFail()));

    $post = Post::where('slug', 'article-de-test')->firstOrFail();

    expect($post->tags->pluck('name')->all())->toBe(['Astuce', 'Sécurité']);

    $this->get(route('posts.show', $post))
        ->assertOk()
        ->assertSeeText('Astuce')
        ->assertSeeText('Sécurité');
});

it('keeps submitted tags checked when post validation fails', function () {
    $tags = Tag::factory()->count(2)->sequence(
        ['name' => 'Astuce', 'slug' => 'astuce'],
        ['name' => 'Sécurité', 'slug' => 'securite'],
    )->create();

    $this->from(route('posts.create'))
        ->followingRedirects()
        ->post(route('posts.store'), postData([
            'title' => '',
            'tags' => $tags->pluck('id')->all(),
        ]))
        ->assertOk()
        ->assertSee('value="'.$tags[0]->id.'" checked', false)
        ->assertSee('value="'.$tags[1]->id.'" checked', false);

    $this->assertDatabaseMissing('posts', ['slug' => 'article-de-test']);
});

it('rejects tag identifiers that do not exist when creating a post', function () {
    $this->from(route('posts.create'))
        ->post(route('posts.store'), postData(['tags' => [999]]))
        ->assertRedirect(route('posts.create'))
        ->assertSessionHasErrors(['tags.0']);

    $this->assertDatabaseMissing('posts', ['slug' => 'article-de-test']);
});

it('rejects a non-array tag selection without breaking the form', function () {
    $this->from(route('posts.create'))
        ->followingRedirects()
        ->post(route('posts.store'), postData(['tags' => 'invalid']))
        ->assertOk()
        ->assertSeeText('The tags field must be an array.');

    $this->assertDatabaseMissing('posts', ['slug' => 'article-de-test']);
});

it('rejects a category that does not exist when creating a post', function () {
    $this->from(route('posts.create'))
        ->post(route('posts.store'), postData(['category_id' => 999]))
        ->assertRedirect(route('posts.create'))
        ->assertSessionHasErrors(['category_id']);

    $this->assertDatabaseMissing('posts', ['slug' => 'article-de-test']);
});

it('does not create a post without a title', function () {
    $this->from(route('posts.create'))
        ->post(route('posts.store'), postData(['title' => '']))
        ->assertRedirect(route('posts.create'))
        ->assertSessionHasErrors(['title' => 'The title field is required.']);

    $this->assertDatabaseMissing('posts', ['slug' => 'article-de-test']);
});

it('shows the edit form with the current values', function () {
    $category = Category::factory()->create(['name' => 'PHP']);
    $tags = Tag::factory()->count(2)->sequence(
        ['name' => 'Astuce', 'slug' => 'astuce'],
        ['name' => 'Sécurité', 'slug' => 'securite'],
    )->create();
    $post = Post::create(postData([
        'title' => 'Titre initial',
        'category_id' => $category->id,
    ]));
    $post->tags()->attach($tags);

    $this->get(route('posts.edit', $post))
        ->assertOk()
        ->assertSee('Titre initial')
        ->assertSee('value="article-de-test"', false)
        ->assertSee('value="Un extrait court."', false)
        ->assertSee('<option value="'.$category->id.'" selected>PHP</option>', false)
        ->assertSee('value="'.$tags[0]->id.'" checked', false)
        ->assertSee('value="'.$tags[1]->id.'" checked', false);
});

it('synchronizes selected tags on update and removes them when none are selected', function () {
    $tags = Tag::factory()->count(3)->sequence(
        ['name' => 'Astuce', 'slug' => 'astuce'],
        ['name' => 'Sécurité', 'slug' => 'securite'],
        ['name' => 'Tests', 'slug' => 'tests'],
    )->create();
    $post = Post::create(postData());
    $post->tags()->attach([$tags[0]->id, $tags[1]->id]);

    $this->put(route('posts.update', $post), postData([
        'tags' => [$tags[1]->id, $tags[2]->id],
    ]))->assertRedirect(route('posts.show', $post));

    expect($post->fresh()->tags->pluck('id')->sort()->values()->all())
        ->toBe([$tags[1]->id, $tags[2]->id]);

    $this->put(route('posts.update', $post), postData())
        ->assertRedirect(route('posts.show', $post));

    expect($post->fresh()->tags)->toBeEmpty();
    $this->get(route('posts.show', $post))
        ->assertOk()
        ->assertDontSeeText('Astuce')
        ->assertDontSeeText('Sécurité')
        ->assertDontSeeText('Tests');
});

it('rejects tag identifiers that do not exist when updating a post', function () {
    $tag = Tag::factory()->create();
    $post = Post::create(postData());
    $post->tags()->attach($tag);

    $this->from(route('posts.edit', $post))
        ->put(route('posts.update', $post), postData(['tags' => [999]]))
        ->assertRedirect(route('posts.edit', $post))
        ->assertSessionHasErrors(['tags.0']);

    expect($post->fresh()->tags->modelKeys())->toBe([$tag->id]);
});

it('updates a post category and can remove it', function () {
    $initialCategory = Category::factory()->create();
    $newCategory = Category::factory()->create();
    $post = Post::create(postData(['category_id' => $initialCategory->id]));

    $this->put(route('posts.update', $post), postData(['category_id' => $newCategory->id]))
        ->assertRedirect(route('posts.show', $post));

    $this->assertDatabaseHas('posts', [
        'id' => $post->id,
        'category_id' => $newCategory->id,
    ]);

    $this->put(route('posts.update', $post), postData(['category_id' => '']))
        ->assertRedirect(route('posts.show', $post));

    $this->assertDatabaseHas('posts', [
        'id' => $post->id,
        'category_id' => null,
    ]);
});

it('rejects a category that does not exist when updating a post', function () {
    $category = Category::factory()->create();
    $post = Post::create(postData(['category_id' => $category->id]));

    $this->from(route('posts.edit', $post))
        ->put(route('posts.update', $post), postData(['category_id' => 999]))
        ->assertRedirect(route('posts.edit', $post))
        ->assertSessionHasErrors(['category_id']);

    $this->assertDatabaseHas('posts', [
        'id' => $post->id,
        'category_id' => $category->id,
    ]);
});

it('updates a post and keeps its own slug', function () {
    $post = Post::create(postData());

    $this->put(route('posts.update', $post), postData(['title' => 'Titre modifié']))
        ->assertRedirect(route('posts.show', $post))
        ->assertSessionHas('success', 'Article mis à jour.');

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
        ->assertRedirect(route('posts.index'))
        ->assertSessionHas('success', 'Article supprimé.');

    $this->assertDatabaseMissing('posts', ['id' => $post->id]);
});

it('publishes a draft and sets its publication date', function () {
    $this->travelTo('2026-10-29 12:00:00');

    $post = Post::create(postData());

    $this->get(route('posts.show', $post))
        ->assertOk()
        ->assertSee('action="'.route('posts.publication', $post).'"', false)
        ->assertSee('name="_method" value="PATCH"', false)
        ->assertSeeText('Publier')
        ->assertDontSeeText('Dépublier');

    $this->patch(route('posts.publication', $post))
        ->assertRedirect(route('posts.show', $post))
        ->assertSessionHas('success', 'Article publié.');

    $this->assertDatabaseHas('posts', [
        'id' => $post->id,
        'is_published' => true,
        'published_at' => '2026-10-29 12:00:00',
    ]);

    $this->get(route('posts.show', $post))
        ->assertOk()
        ->assertSeeText('Dépublier')
        ->assertDontSeeText('Publier');
});

it('unpublishes a post and clears its publication date', function () {
    $post = Post::create(postData([
        'is_published' => true,
        'published_at' => '2026-10-20 09:30:00',
    ]));

    $this->get(route('posts.show', $post))
        ->assertOk()
        ->assertSeeText('Dépublier')
        ->assertDontSeeText('Publier');

    $this->patch(route('posts.publication', $post))
        ->assertRedirect(route('posts.show', $post))
        ->assertSessionHas('success', 'Article dépublié.');

    $this->assertDatabaseHas('posts', [
        'id' => $post->id,
        'is_published' => false,
        'published_at' => null,
    ]);

    $this->get(route('posts.show', $post))
        ->assertOk()
        ->assertSeeText('Publier')
        ->assertDontSeeText('Dépublier');
});

it('allows read requests but rejects post writes when the site is read-only', function () {
    $post = Post::create(postData());
    config(['site.read_only' => true]);

    $this->get(route('posts.index'))->assertOk();
    $this->get(route('posts.edit', $post))->assertOk();

    $this->post(route('posts.store'), postData(['slug' => 'article-nouveau']))
        ->assertStatus(503);
    $this->put(route('posts.update', $post), postData(['title' => 'Titre modifié']))
        ->assertStatus(503);
    $this->patch(route('posts.publication', $post))
        ->assertStatus(503);
    $this->delete(route('posts.destroy', $post))
        ->assertStatus(503);

    $this->assertDatabaseMissing('posts', ['slug' => 'article-nouveau']);
    $this->assertDatabaseHas('posts', ['id' => $post->id, 'title' => 'Article de test']);
});
