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

test('the version route displays the Laravel and PHP versions', function () {
    $this->get('/version')
        ->assertOk()
        ->assertSeeText('Laravel '.app()->version().' - PHP '.PHP_VERSION);
});
