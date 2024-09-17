<?php declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::redirect('/', env('REDIRECT_URL', 'https://euvsdisinfo.eu'), 301);
