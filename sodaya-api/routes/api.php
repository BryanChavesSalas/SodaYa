<?php

use App\Http\Controllers\Api\V1\PlatoController;
use Illuminate\Support\Facades\Route;

Route::name('v1.')->group(function () {
    Route::get('sodas/{soda}/platos', [PlatoController::class, 'index'])->name('sodas.platos.index');
    Route::get('sodas/{soda}/platos/{plato}', [PlatoController::class, 'show'])
        ->scopeBindings()
        ->name('sodas.platos.show');
});
