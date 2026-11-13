<?php

use App\Models\Post;
use App\Models\Tag;

beforeEach(function () {
    $this->withoutVite();
});

it('lists tags alphabetically with their post counts', function () {
    $blade = Tag::factory()->create(['name' => 'Blade', 'slug' => 'blade']);
    $laravel = Tag::factory()->create(['name' => 'Laravel', 'slug' => 'laravel']);
    $posts = Post::factory()->count(2)->create();
    $posts->each(fn (Post $post) => $post->tags()->attach($blade));
    Post::factory()->create()->tags()->attach($laravel);

    $this->get(route('admin.tags.index'))
        ->assertOk()
        ->assertSeeInOrder(['Blade', 'Laravel'])
        ->assertSee('<td class="py-3 pr-4 text-gray-600">2</td>', false)
        ->assertSee('<td class="py-3 pr-4 text-gray-600">1</td>', false);
});

it('creates a tag and shows a confirmation message', function () {
    $this->post(route('admin.tags.store'), [
        'name' => 'Accessibilité',
        'slug' => 'accessibilite',
    ])->assertRedirect(route('admin.tags.index'))
        ->assertSessionHas('success', 'Tag créé.');

    $this->assertDatabaseHas('tags', [
        'name' => 'Accessibilité',
        'slug' => 'accessibilite',
    ]);

    $this->get(route('admin.tags.index'))
        ->assertOk()
        ->assertSeeText('Tag créé.')
        ->assertSeeText('Accessibilité');
});

it('shows existing tag values in the edit form', function () {
    $tag = Tag::factory()->create([
        'name' => 'Laravel',
        'slug' => 'laravel',
    ]);

    $this->get(route('admin.tags.edit', $tag))
        ->assertOk()
        ->assertSeeText('Modifier le tag')
        ->assertSee('value="Laravel"', false)
        ->assertSee('value="laravel"', false);
});

it('updates a tag without changing its slug', function () {
    $tag = Tag::factory()->create([
        'name' => 'Laravel',
        'slug' => 'laravel',
    ]);

    $this->put(route('admin.tags.update', $tag), [
        'name' => 'Laravel avancé',
        'slug' => 'laravel',
    ])->assertRedirect(route('admin.tags.index'))
        ->assertSessionHas('success', 'Tag mis à jour.');

    $this->assertDatabaseHas('tags', [
        'id' => $tag->id,
        'name' => 'Laravel avancé',
        'slug' => 'laravel',
    ]);
});

it('rejects a tag slug already used by another tag', function () {
    Tag::factory()->create(['name' => 'Laravel', 'slug' => 'laravel']);
    $tag = Tag::factory()->create(['name' => 'Blade', 'slug' => 'blade']);

    $this->from(route('admin.tags.edit', $tag))
        ->put(route('admin.tags.update', $tag), [
            'name' => 'Blade',
            'slug' => 'laravel',
        ])
        ->assertRedirect(route('admin.tags.edit', $tag))
        ->assertSessionHasErrors(['slug']);

    $this->assertDatabaseHas('tags', [
        'id' => $tag->id,
        'name' => 'Blade',
        'slug' => 'blade',
    ]);
});

it('deletes a tag and removes its links without deleting its posts', function () {
    $tag = Tag::factory()->create();
    $post = Post::factory()->create();
    $post->tags()->attach($tag);

    $this->delete(route('admin.tags.destroy', $tag))
        ->assertRedirect(route('admin.tags.index'))
        ->assertSessionHas('success', 'Tag supprimé.');

    $this->assertModelMissing($tag);
    $this->assertModelExists($post);
    $this->assertDatabaseMissing('post_tag', [
        'post_id' => $post->id,
        'tag_id' => $tag->id,
    ]);
});

it('keeps tag reads available and blocks writes in read-only mode', function () {
    config(['site.read_only' => true]);

    $this->get(route('admin.tags.index'))->assertOk();

    $this->post(route('admin.tags.store'), [
        'name' => 'Laravel',
        'slug' => 'laravel',
    ])->assertStatus(503);

    $this->assertDatabaseMissing('tags', ['slug' => 'laravel']);
});
