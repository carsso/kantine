<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\DarkmodeController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\JobMonitorController;

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

Route::get('/', [MenuController::class, 'home'])
    ->name('home');

Route::get('/legal', [MenuController::class, 'legal'])
    ->name('legal');

Route::get('/darkmode/{enable}', [DarkmodeController::class, 'json'])
    ->name('darkmode');

Auth::routes(['verify' => true]);

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/account', [AccountController::class, 'index'])
        ->name('account');

    Route::post('/account/tokens', [AccountController::class, 'storeToken'])->name('account.tokens.store');
    Route::delete('/account/tokens/{token}', [AccountController::class, 'destroyToken'])->name('account.tokens.destroy');
});

Route::prefix('/admin')->middleware(['auth', 'verified', 'permission:admin'])->group(function () {
    Route::get('/', [AdminController::class, 'index'])
        ->name('admin');
    Route::get('/jobs', [JobMonitorController::class, 'index'])
        ->name('admin.jobs');
    Route::get('/api/jobs', [JobMonitorController::class, 'getJobs']);
});

Route::redirect('/dashboard/{date?}', '/roubaix/dashboard/{date?}');
Route::redirect('/menu/{date?}', '/roubaix/menus/{date?}');
Route::redirect('/menus/{date?}', '/roubaix/menus/{date?}');
Route::redirect('/notifications/{date?}', '/roubaix/notifications/{date?}');
Route::redirect('/notifications/webex/{date?}', '/roubaix/notifications/webex/{date?}');


Route::prefix('{tenantSlug}')->middleware('tenant')->group(function () {
    Route::get('/', [MenuController::class, 'menu'])
        ->name('tenant.home');

    Route::get('/dashboard/{date?}', [MenuController::class, 'dashboard'])
        ->name('dashboard');

    Route::get('/menus/{date?}', [MenuController::class, 'menu'])
        ->name('menus');

    Route::get('/notifications/{date?}', [MenuController::class, 'notifications'])
        ->name('notifications');

    Route::get('/notifications/webex/{date}', [MenuController::class, 'webexMenu'])
        ->name('notifications.webex');

    # admin route group with prefix
    Route::prefix('/admin')->middleware(['auth', 'verified', 'tenant-admin'])->group(function () {
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