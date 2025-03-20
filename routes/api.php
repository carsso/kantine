<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiController;
use App\Http\Controllers\ApiAdminController;
use App\Http\Controllers\Api\AuthController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
});

Route::redirect('/', '/api/roubaix');
Route::redirect('/today', '/api/roubaix/today');
Route::redirect('/day/{date}', '/api/roubaix/day/{date}');

Route::prefix('{tenantSlug}')->middleware('tenant')->group(function () {
    Route::get('/', [ApiController::class, 'home'])
        ->name('api.home');
    Route::get('/day/{date}', [ApiController::class, 'day'])
        ->name('api.day');

    Route::get('/today', [ApiController::class, 'today'])
        ->name('api.today');
        
    Route::prefix('/admin')->middleware(['auth:sanctum', 'tenant-admin'])->group(function () {
        Route::get('/menus/{date}', [ApiAdminController::class, 'menu'])
            ->name('api.admin.menus.get');
        Route::post('/menus/{date}', [ApiAdminController::class, 'updateMenuApi'])
            ->name('api.admin.menus.update');
    });
});

