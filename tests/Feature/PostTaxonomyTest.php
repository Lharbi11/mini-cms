<?php

use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;

beforeEach(function () {
    $this->withoutVite();
});

function taxonomyPostData(array $overrides = []): array
{
    return array_merge([
        'title' => 'Article classé',
        'slug' => 'article-classe',
        'body' => 'Contenu assez long pour passer la validation.',
        'is_published' => '1',
    ], $overrides);
}

it('stores the category and tags selected in the form', function () {
    $category = Category::factory()->create();
    $tags = Tag::factory(2)->create();

    $this->post(route('posts.store'), taxonomyPostData([
        'category_id' => $category->id,
        'tags' => $tags->modelKeys(),
    ]))->assertRedirect(route('posts.show', Post::where('slug', 'article-classe')->firstOrFail()));

    $post = Post::where('slug', 'article-classe')->firstOrFail();

    $this->assertDatabaseHas('posts', [
        'id' => $post->id,
        'category_id' => $category->id,
    ]);
    $this->assertDatabaseHas('post_tag', [
        'post_id' => $post->id,
        'tag_id' => $tags[0]->id,
    ]);
    $this->assertDatabaseHas('post_tag', [
        'post_id' => $post->id,
        'tag_id' => $tags[1]->id,
    ]);
});

it('replaces the selected tags on update', function () {
    $post = Post::factory()->create(['slug' => 'article-classe']);
    [$oldTag, $keptTag, $newTag] = Tag::factory(3)->create();
    $post->tags()->attach([$oldTag->id, $keptTag->id]);

    $this->put(route('posts.update', $post), taxonomyPostData([
        'tags' => [$keptTag->id, $newTag->id],
    ]))->assertRedirect(route('posts.show', $post));

    $this->assertDatabaseMissing('post_tag', [
        'post_id' => $post->id,
        'tag_id' => $oldTag->id,
    ]);
    $this->assertDatabaseHas('post_tag', [
        'post_id' => $post->id,
        'tag_id' => $keptTag->id,
    ]);
    $this->assertDatabaseHas('post_tag', [
        'post_id' => $post->id,
        'tag_id' => $newTag->id,
    ]);
});

it('removes all tags when no checkbox is selected', function () {
    $post = Post::factory()->create(['slug' => 'article-classe']);
    $post->tags()->attach(Tag::factory(2)->create());

    $this->put(route('posts.update', $post), taxonomyPostData())
        ->assertRedirect(route('posts.show', $post));

    expect($post->tags()->count())->toBe(0);
});

it('rejects a category that does not exist', function () {
    $this->from(route('posts.create'))
        ->post(route('posts.store'), taxonomyPostData(['category_id' => 999]))
        ->assertRedirect(route('posts.create'))
        ->assertSessionHasErrors(['category_id']);

    $this->assertDatabaseMissing('posts', ['slug' => 'article-classe']);
});

it('lists only published posts in a category resolved by its slug', function () {
    $category = Category::factory()->create([
        'name' => 'Tutoriels',
        'slug' => 'tutoriels',
    ]);
    Post::factory()->published()->for($category)->create([
        'title' => 'Dans la catégorie',
    ]);
    Post::factory()->for($category)->create([
        'title' => 'Brouillon de la catégorie',
    ]);
    Post::factory()->published()->create([
        'title' => 'Hors catégorie',
    ]);

    $this->get('/posts/categorie/tutoriels')
        ->assertOk()
        ->assertSeeText('Dans la catégorie')
        ->assertDontSeeText('Brouillon de la catégorie')
        ->assertDontSeeText('Hors catégorie');
});

it('returns 404 for an unknown category slug', function () {
    $this->get('/posts/categorie/inconnue')->assertNotFound();
});

it('counts only published posts for each category on the home page', function () {
    $tutorials = Category::factory()->create([
        'name' => 'Tutoriels',
        'slug' => 'tutoriels',
    ]);
    $laravel = Category::factory()->create([
        'name' => 'Laravel',
        'slug' => 'laravel',
    ]);
    Post::factory(2)->published()->for($tutorials)->create();
    Post::factory()->for($tutorials)->create();
    Post::factory()->published()->for($laravel)->create();

    $response = $this->get(route('home'));

    $response->assertOk()
        ->assertSeeText('Laravel (1)')
        ->assertSeeText('Tutoriels (2)')
        ->assertSeeInOrder([
            'Laravel (1)',
            'Tutoriels (2)',
        ]);
    $response->assertViewHas('categories', fn ($categories) => $categories->every(
        fn (Category $category) => ! $category->relationLoaded('posts')
    ));
});
