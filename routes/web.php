<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/heure', function () {
    $dateTime = now();

    return view('heure', [
        'heure' => $dateTime->format('H:i'),
        'date' => $dateTime->format('d/m/Y'),
    ]);
});

Route::get('/bonjour', function () {
    return 'Bonjour MDW3 ! Voici ma première route Laravel 13.';
});

Route::get('/bonjour-court', fn () => 'Même résultat, écrit avec une fonction fléchée.');

Route::get('/version', function () {
    return 'Laravel '.app()->version().' - PHP '.PHP_VERSION;
});

Route::get('/bienvenue', function () {
    return view('bienvenue', [
        'etudiant' => 'rayen harabi',
        'groupe' => 'MDW33',
        'cours' => 'Atelier Framework Côté Serveur',
    ]);
});

Route::get('/a-propos', function () {
    return view('a-propos', [
        'auteur' => 'rayen harabi',
        'groupe' => 'MDW33',
    ]);
});
