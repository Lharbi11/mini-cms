<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/heure', function () {
    return 'Il est ' . now()->format('H:i') . '.';
});

Route::get('/bonjour', function () {
    return 'Bonjour MDW3 ! Voici ma première route Laravel 13.';
});

Route::get('/bonjour-court', fn () => 'Même résultat, écrit avec une fonction fléchée.');

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