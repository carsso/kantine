<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\DarkmodeController;
use App\Http\Controllers\AdminController;

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

Route::get('/', [MenuController::class, 'menu'])
    ->name('home');

Route::get('/dashboard/{date?}', [MenuController::class, 'dashboard'])
    ->name('dashboard');

Route::get('/menu/{date?}', [MenuController::class, 'menu'])
    ->name('menu');

Route::get('/menus/{date?}', [MenuController::class, 'menu'])
    ->name('menus');

Route::get('/notifications/{date?}', [MenuController::class, 'notifications'])
    ->name('notifications');

Route::get('/notifications/webex/{day}', [MenuController::class, 'webexMenu'])
    ->name('notifications.webex.day');

Route::get('/legal', [MenuController::class, 'legal'])
    ->name('legal');

Route::get('/darkmode/{enable}', [DarkmodeController::class, 'json'])
    ->name('darkmode');

Auth::routes(['verify' => true]);

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/account', [AccountController::class, 'index'])
        ->name('account');
});

# admin route group with prefix
Route::prefix('/admin')->group(function () {
    Route::middleware(['auth', 'verified', 'role:Super Admin'])->group(function () {
        Route::get('/', [AdminController::class, 'index'])
            ->name('admin');

        Route::get('/menus/{date?}', [AdminController::class, 'menu'])
            ->name('admin.menu');

        Route::post('/menus', [AdminController::class, 'updateMenu'])
            ->name('admin.menu.update');

        Route::get('/webex', [AdminController::class, 'webex'])
            ->name('admin.webex');

        Route::post('/webex/notify', [AdminController::class, 'webexNotify'])
            ->name('admin.webex.notify');
    });
});