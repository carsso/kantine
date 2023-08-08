<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\DarkmodeController;

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
    ->middleware('guest')
    ->name('home');

Route::get('/menu', [MenuController::class, 'index'])
    ->middleware('guest')
    ->name('menu');

Route::get('/menu/{date}', [MenuController::class, 'menu'])
    ->middleware('guest')
    ->name('menu.date');

Route::post('/upload', [MenuController::class, 'upload'])
    ->middleware('guest')
    ->name('upload');

Route::get('/file', [MenuController::class, 'files'])
    ->middleware('guest')
    ->name('files');

Route::get('/file/{hash}', [MenuController::class, 'file'])
    ->middleware('guest')
    ->name('file');

Route::get('/file/{hash}/relaunch', [MenuController::class, 'fileRelaunch'])
    ->middleware('guest')
    ->name('file.relaunch');

Route::get('/file/{hash}/delete', [MenuController::class, 'fileDelete'])
    ->middleware('guest')
    ->name('file.delete');

Route::get('/darkmode/{enable}', [DarkmodeController::class, 'json'])
    ->name('darkmode');