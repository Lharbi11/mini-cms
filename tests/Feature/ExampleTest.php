<?php

test('the application returns a successful response', function () {
    $response = $this->get('/');

    $response->assertStatus(200);
});

test('the heure route displays the current time', function () {
    $this->travelTo(now()->setDate(2025, 1, 1)->setTime(14, 30));

    $this->get('/heure')
        ->assertOk()
        ->assertSeeText('Il est 14:30.');
});
