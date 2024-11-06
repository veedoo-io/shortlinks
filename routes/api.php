<?php declare(strict_types=1);

//use App\Http\Controllers\Url\UrlController;
use App\Http\Controllers\Beyond\BeyondWebhookController;
use Illuminate\Support\Facades\Route;

//Route::get('/{key_url}', [UrlController::class, 'index'])->name('url.index');
//Route::post('/url', [UrlController::class, 'create'])->name('url.create');
Route::post('/beyond/webhook/{projectId}', [BeyondWebhookController::class, 'create'])->name('beyond.webhook');
