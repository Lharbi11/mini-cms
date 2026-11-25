<?php

use App\Models\Tag;
use App\Models\User;

beforeEach(function () {
    $this->withoutVite();
    $this->actingAs(User::factory()->admin()->create());
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
});

it('rejects an empty tag with French required messages', function () {
    $this->from(route('admin.tags.create'))
        ->post(route('admin.tags.store'), ['name' => '', 'slug' => ''])
        ->assertRedirect(route('admin.tags.create'))
        ->assertSessionHasErrors([
            'name' => 'Le champ nom est obligatoire.',
            'slug' => 'Le champ slug est obligatoire.',
        ]);

    $this->assertDatabaseCount('tags', 0);
});

it('rejects a slug already used by another tag with a tag-specific message', function () {
    Tag::factory()->create(['name' => 'Laravel', 'slug' => 'laravel']);

    $this->from(route('admin.tags.create'))
        ->post(route('admin.tags.store'), [
            'name' => 'Laravel avancé',
            'slug' => 'laravel',
        ])
        ->assertRedirect(route('admin.tags.create'))
        ->assertSessionHasErrors([
            'slug' => 'Ce slug est déjà utilisé par un autre tag.',
        ]);

    $this->assertDatabaseMissing('tags', ['name' => 'Laravel avancé']);
});

it('blocks tag writes with a 503 response when the site is read-only', function () {
    config(['site.read_only' => true]);

    $this->post(route('admin.tags.store'), [
        'name' => 'Laravel',
        'slug' => 'laravel',
    ])->assertStatus(503)
        ->assertSeeText('Le site est en lecture seule.');

    $this->assertDatabaseMissing('tags', ['slug' => 'laravel']);
});

it('keeps the tag list readable when the site is read-only', function () {
    config(['site.read_only' => true]);
    Tag::factory()->create(['name' => 'Laravel', 'slug' => 'laravel']);

    $this->get(route('admin.tags.index'))
        ->assertOk()
        ->assertSeeText('Laravel');
});
