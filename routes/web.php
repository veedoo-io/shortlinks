<?php declare(strict_types=1);

use App\Http\Controllers\Beyond\BeyondAudioController;
use App\Http\Controllers\GPT\GPTController;
use Illuminate\Support\Facades\Route;

//Route::redirect('/', env('REDIRECT_URL', 'https://euvsdisinfo.eu'), 301);

Route::get('/', [GPTController::class, 'index'])->name('gpt.index');
Route::post('/', [GPTController::class, 'create'])->name('gpt.create');

Route::view('/gpt/auth', 'gpt.auth.index')->name('gpt.auth.index');
Route::post('/gpt/auth', [GPTController::class, 'auth'])->name('gpt.auth.save');

Route::get('/beyond', [BeyondAudioController::class, 'index'])->name('beyond.index');
Route::post('/beyond/download', [BeyondAudioController::class, 'downloadAudioByProject'])->name('beyond.downloadAudioByProject');
