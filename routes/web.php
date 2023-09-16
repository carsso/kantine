<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\DarkmodeController;
use App\Http\Controllers\Auth\VerificationController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', [MenuController::class, 'index'])
    ->name('home');

Route::get('/menu', [MenuController::class, 'index'])
    ->name('menu');

Route::get('/menu/{date}', [MenuController::class, 'menu'])
    ->name('menu.date');

Route::get('/notifications', [MenuController::class, 'notifications'])
    ->name('notifications');

Route::get('/notifications/webex/{day}', [MenuController::class, 'webexMenu'])
    ->name('notifications.webex.day');

Route::get('/legal', [MenuController::class, 'legal'])
    ->name('legal');

Route::name('sentry')
    ->any('/sentry', [MenuController::class, 'sentry']);

Route::get('/darkmode/{enable}', [DarkmodeController::class, 'json'])
    ->name('darkmode');

Auth::routes(['verify' => true]);

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/account', [AccountController::class, 'index'])
        ->name('account');

    Route::get('/file', [MenuController::class, 'files'])
        ->name('files');

    Route::get('/file/{hash}', [MenuController::class, 'file'])
        ->name('file');

    Route::get('/file/{hash}/relaunch', [MenuController::class, 'fileRelaunch'])
        ->name('file.relaunch');

    Route::get('/file/{hash}/delete', [MenuController::class, 'fileDelete'])
        ->name('file.delete');

    Route::post('/upload', [MenuController::class, 'upload'])
        ->name('upload');
});