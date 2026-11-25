<?php

use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;

beforeEach(function () {
    $this->withoutVite();
    $this->actingAs(User::factory()->admin()->create());
});

it('lists posts with their category, author and tags without lazy loading', function () {
    $category = Category::factory()->create(['name' => 'Tutoriels']);
    $tag = Tag::factory()->create(['name' => 'Astuce']);
    $author = User::factory()->create(['name' => 'Amira Ben Salah']);

    $posts = Post::factory(3)->published()->create([
        'category_id' => $category->id,
        'user_id' => $author->id,
    ]);

    $posts->each(fn (Post $post) => $post->tags()->attach($tag));

    $this->get(route('posts.index'))
        ->assertOk()
        ->assertSee('Tutoriels')
        ->assertSee('Amira Ben Salah')
        ->assertSee('Astuce');
});

it('sets optional post foreign keys to null when their parent is deleted', function () {
    $category = Category::factory()->create();
    $author = User::factory()->create();
    $post = Post::factory()
        ->for($category)
        ->for($author)
        ->create();

    $category->delete();
    $author->delete();

    $this->assertDatabaseHas('posts', [
        'id' => $post->id,
        'category_id' => null,
        'user_id' => null,
    ]);
});

it('replaces the tags of a post with sync', function () {
    $post = Post::factory()->create();
    $tags = Tag::factory(3)->create();

    $post->tags()->attach([$tags[0]->id, $tags[1]->id]);
    $post->tags()->sync([$tags[1]->id, $tags[2]->id]);

    expect($post->tags()->count())->toBe(2);
    $this->assertDatabaseMissing('post_tag', ['post_id' => $post->id, 'tag_id' => $tags[0]->id]);
    $this->assertDatabaseHas('post_tag', ['post_id' => $post->id, 'tag_id' => $tags[2]->id]);
});

it('removes pivot rows when a post is deleted while preserving its tags', function () {
    $post = Post::factory()->create();
    $tag = Tag::factory()->create();

    $post->tags()->attach($tag);

    $this->delete(route('posts.destroy', $post))
        ->assertRedirect(route('posts.index'));

    $this->assertDatabaseMissing('post_tag', ['post_id' => $post->id]);
    $this->assertModelExists($tag);
});

it('shows up to three other published posts from the same category in publication order', function () {
    $category = Category::factory()->create();
    $post = Post::factory()->published()->create([
        'category_id' => $category->id,
        'title' => 'Article principal',
        'published_at' => '2026-10-10 12:00:00',
    ]);

    Post::factory()->published()->create([
        'category_id' => $category->id,
        'title' => 'Voisin récent',
        'published_at' => '2026-10-09 12:00:00',
    ]);
    Post::factory()->published()->create([
        'category_id' => $category->id,
        'title' => 'Voisin intermédiaire',
        'published_at' => '2026-10-08 12:00:00',
    ]);
    Post::factory()->published()->create([
        'category_id' => $category->id,
        'title' => 'Voisin ancien',
        'published_at' => '2026-10-07 12:00:00',
    ]);
    Post::factory()->published()->create([
        'category_id' => $category->id,
        'title' => 'Voisin hors limite',
        'published_at' => '2026-10-06 12:00:00',
    ]);
    Post::factory()->create([
        'category_id' => $category->id,
        'title' => 'Brouillon de la catégorie',
    ]);
    Post::factory()->published()->create([
        'title' => 'Article d’une autre catégorie',
        'published_at' => '2026-10-11 12:00:00',
    ]);

    $response = $this->get(route('posts.show', $post));

    $response->assertOk()
        ->assertSeeText('Dans la même catégorie')
        ->assertSeeInOrder([
            'Voisin récent',
            'Voisin intermédiaire',
            'Voisin ancien',
        ])
        ->assertDontSeeText('Voisin hors limite')
        ->assertDontSeeText('Brouillon de la catégorie')
        ->assertDontSeeText('Article d’une autre catégorie');

    expect(substr_count($response->getContent(), '<article'))->toBe(4);
    expect(substr_count($response->getContent(), 'Article principal'))->toBe(2);
});

it('hides related posts when the shown post has no category or has no published neighbors', function () {
    $postWithoutCategory = Post::factory()->published()->create([
        'title' => 'Article sans catégorie',
    ]);

    $categoryWithoutPublishedNeighbors = Category::factory()->create();
    $postWithoutPublishedNeighbors = Post::factory()->published()->for($categoryWithoutPublishedNeighbors)->create([
        'title' => 'Seul article publié de sa catégorie',
    ]);

    Post::factory()->for($categoryWithoutPublishedNeighbors)->create([
        'title' => 'Brouillon voisin',
    ]);

    $this->get(route('posts.show', $postWithoutCategory))
        ->assertOk()
        ->assertDontSeeText('Dans la même catégorie');

    $this->get(route('posts.show', $postWithoutPublishedNeighbors))
        ->assertOk()
        ->assertDontSeeText('Dans la même catégorie');
});
