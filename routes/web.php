<?php declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::redirect('/', env('REDIRECT_URL', 'https://euvsdisinfo.eu'), 301);

Route::get('/gpt/create', [\App\Http\Controllers\GPT\GPTController::class, 'index'])->name('gpt.index');
Route::post('/gpt/create', [\App\Http\Controllers\GPT\GPTController::class, 'create'])->name('gpt.create');

Route::view('/gpt/auth', 'gpt.auth.index')->name('gpt.auth.index');
Route::post('/gpt/auth', [\App\Http\Controllers\GPT\GPTController::class, 'auth'])->name('gpt.auth.save');
