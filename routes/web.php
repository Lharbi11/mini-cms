<?php

use App\Http\Controllers\PageController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PageController::class, 'home']);

Route::get('/heure', function () {
    $dateTime = now();

    return view('heure', [
        'heure' => $dateTime->format('H:i'),
        'date' => $dateTime->format('d/m/Y'),
    ]);
});

Route::get('/a-propos', [PageController::class, 'about']);
