<?php

use App\Http\Controllers\Api\WatchmanMobileApiController;
use Illuminate\Support\Facades\Route;

/*
| Watchman mobile app (Bearer token). Mirrors /watchman/visitors on the website.
| Hand-off: docs/WATCHMAN_MOBILE_API.md
*/

Route::get('/profile', [WatchmanMobileApiController::class, 'profile'])->name('api.v1.watchman.profile');
Route::get('/visitors', [WatchmanMobileApiController::class, 'hub'])->name('api.v1.watchman.visitors');
Route::get('/visitors/today', [WatchmanMobileApiController::class, 'today'])->name('api.v1.watchman.visitors.today');
Route::get('/visitors/search', [WatchmanMobileApiController::class, 'search'])->name('api.v1.watchman.visitors.search');
Route::post('/visitors', [WatchmanMobileApiController::class, 'store'])->name('api.v1.watchman.visitors.store');
Route::post('/visitors/sign-in', [WatchmanMobileApiController::class, 'signIn'])->name('api.v1.watchman.visitors.sign_in');
Route::post('/visitors/sign-out', [WatchmanMobileApiController::class, 'signOut'])->name('api.v1.watchman.visitors.sign_out');
