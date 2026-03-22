<?php

use App\Http\Controllers\Gallery\DeletePhotoController;
use App\Http\Controllers\Gallery\ListPhotosController;
use App\Http\Controllers\Gallery\ShowPhotoController;
use App\Http\Controllers\Gallery\StorePhotoController;
use App\Http\Controllers\Gallery\UpdatePhotoController;
use App\Http\Controllers\Gallery\UserGalleryController;
use App\Http\Controllers\Reactions\ToggleReactionController;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('gallery', ListPhotosController::class)->name('gallery.index');
    Route::post('gallery', StorePhotoController::class)->name('gallery.store');
    Route::get('gallery/{photo}', ShowPhotoController::class)->name('gallery.show');
    Route::patch('gallery/{photo}', UpdatePhotoController::class)->name('gallery.update');
    Route::delete('gallery/{photo}', DeletePhotoController::class)->name('gallery.destroy');
    Route::post('gallery/{photo}/react', ToggleReactionController::class)->name('gallery.react');

    Route::get('users/{user}/gallery', UserGalleryController::class)->name('users.gallery');
});
