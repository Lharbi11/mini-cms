<?php

use App\Models\Category;
use App\Models\Post;

beforeEach(function () {
    $this->withoutVite();
});

it('lists categories alphabetically with their post counts', function () {
    $laravel = Category::factory()->create(['name' => 'Laravel', 'slug' => 'laravel']);
    $php = Category::factory()->create(['name' => 'PHP', 'slug' => 'php']);
    Post::factory()->count(2)->for($laravel)->create();
    Post::factory()->for($php)->create();

    $this->get(route('admin.categories.index'))
        ->assertOk()
        ->assertSeeInOrder(['Laravel', 'PHP'])
        ->assertSee('<td class="py-3 pr-4 text-gray-600">2</td>', false)
        ->assertSee('<td class="py-3 pr-4 text-gray-600">1</td>', false);
});

it('creates a category and makes it available in the post form', function () {
    $this->post(route('admin.categories.store'), [
        'name' => 'Laravel 13',
        'slug' => 'laravel-13',
    ])->assertRedirect(route('admin.categories.index'))
        ->assertSessionHas('success', 'Catégorie créée.');

    $category = Category::where('slug', 'laravel-13')->firstOrFail();

    $this->assertDatabaseHas('categories', [
        'id' => $category->id,
        'name' => 'Laravel 13',
    ]);

    $this->get(route('posts.create'))
        ->assertOk()
        ->assertSee('value="'.$category->id.'"', false)
        ->assertSeeText('Laravel 13');
});

it('shows existing category values in the edit form', function () {
    $category = Category::factory()->create([
        'name' => 'Laravel',
        'slug' => 'laravel',
    ]);

    $this->get(route('admin.categories.edit', $category))
        ->assertOk()
        ->assertSeeText('Modifier la catégorie')
        ->assertSee('value="Laravel"', false)
        ->assertSee('value="laravel"', false);
});

it('updates a category without changing its slug', function () {
    $category = Category::factory()->create([
        'name' => 'Laravel',
        'slug' => 'laravel',
    ]);

    $this->put(route('admin.categories.update', $category), [
        'name' => 'Laravel 13',
        'slug' => 'laravel',
    ])->assertRedirect(route('admin.categories.index'))
        ->assertSessionHas('success', 'Catégorie mise à jour.');

    $this->assertDatabaseHas('categories', [
        'id' => $category->id,
        'name' => 'Laravel 13',
        'slug' => 'laravel',
    ]);
});

it('rejects a category slug already used by another category', function () {
    Category::factory()->create(['name' => 'Laravel', 'slug' => 'laravel']);
    $category = Category::factory()->create(['name' => 'PHP', 'slug' => 'php']);

    $this->from(route('admin.categories.edit', $category))
        ->put(route('admin.categories.update', $category), [
            'name' => 'PHP',
            'slug' => 'laravel',
        ])
        ->assertRedirect(route('admin.categories.edit', $category))
        ->assertSessionHasErrors(['slug']);

    $this->assertDatabaseHas('categories', [
        'id' => $category->id,
        'name' => 'PHP',
        'slug' => 'php',
    ]);
});

it('deletes a category and leaves its posts uncategorized', function () {
    $category = Category::factory()->create();
    $post = Post::factory()->for($category)->create();

    $this->delete(route('admin.categories.destroy', $category))
        ->assertRedirect(route('admin.categories.index'))
        ->assertSessionHas('success', 'Catégorie supprimée.');

    $this->assertModelMissing($category);
    $this->assertDatabaseHas('posts', [
        'id' => $post->id,
        'category_id' => null,
    ]);
});

it('keeps category reads available and blocks writes in read-only mode', function () {
    config(['site.read_only' => true]);

    $this->get(route('admin.categories.index'))->assertOk();

    $this->post(route('admin.categories.store'), [
        'name' => 'Laravel',
        'slug' => 'laravel',
    ])->assertStatus(503)
        ->assertSeeText('Service momentanément indisponible')
        ->assertSeeText('Le site est en lecture seule.')
        ->assertSee('aria-label="Navigation principale"', false)
        ->assertSee('href="'.route('home').'"', false)
        ->assertSeeText("Retour à l'accueil");

    $this->assertDatabaseMissing('categories', ['slug' => 'laravel']);
});

it('uses French field labels for required category fields', function () {
    $this->from(route('admin.categories.create'))
        ->post(route('admin.categories.store'), [])
        ->assertRedirect(route('admin.categories.create'))
        ->assertSessionHasErrors([
            'name' => 'Le champ nom est obligatoire.',
            'slug' => 'Le champ slug est obligatoire.',
        ]);
});

it('uses a category-specific message for a duplicate slug', function () {
    Category::factory()->create(['name' => 'Laravel', 'slug' => 'laravel']);

    $this->from(route('admin.categories.create'))
        ->post(route('admin.categories.store'), [
            'name' => 'Laravel avancé',
            'slug' => 'laravel',
        ])
        ->assertRedirect(route('admin.categories.create'))
        ->assertSessionHasErrors([
            'slug' => 'Ce slug est déjà utilisé par une autre catégorie.',
        ]);

    $this->assertDatabaseMissing('categories', ['name' => 'Laravel avancé']);
});
