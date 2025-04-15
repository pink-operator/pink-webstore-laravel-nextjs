<?php

use App\Http\Controllers\Auth\VerifyEmailController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

// Simple routes replacing Livewire/Volt functionality
Route::middleware('guest')->group(function () {
    Route::get('login', function() {
        return redirect('/');
    })->name('login');

    Route::get('register', function() {
        return redirect('/');
    })->name('register');

    Route::get('forgot-password', function() {
        return redirect('/');
    })->name('password.request');

    Route::get('reset-password/{token}', function() {
        return redirect('/');
    })->name('password.reset');
});

Route::middleware('auth')->group(function () {
    Route::get('verify-email', function() {
        return redirect('/');
    })->name('verification.notice');

    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    Route::get('confirm-password', function() {
        return redirect('/');
    })->name('password.confirm');
});

// Simple logout route
Route::post('logout', function() {
    Auth::guard('web')->logout();
    return redirect('/');
})->name('logout');
