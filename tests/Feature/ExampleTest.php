<?php

test('the application returns a successful response', function () {
    $response = $this->get('/');

    $response->assertStatus(200);
});

test('the heure route displays the current time', function () {
    $this->travelTo(now()->setDate(2025, 1, 1)->setTime(14, 30));

    $this->get('/heure')
        ->assertOk()
        ->assertSeeText('14:30')
        ->assertSeeText('01/01/2025');
});

test('the about page displays the author and group', function () {
    $this->get('/a-propos')
        ->assertOk()
        ->assertSeeText('rayen harabi')
        ->assertSeeText('MDW33')
        ->assertSee('href="'.route('home').'"', false);
});

test('the exercise routes are no longer available', function () {
    foreach (['/bonjour', '/bonjour-court', '/bienvenue', '/version'] as $uri) {
        $this->get($uri)->assertNotFound();
    }
});
