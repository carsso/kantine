<?php

use App\Http\Controllers\ApiDocsController;
use App\Mcp\Servers\KantineServer;
use Illuminate\Support\Facades\Route;
use Laravel\Mcp\Facades\Mcp;

Mcp::web('/{tenantSlug}/mcp', KantineServer::class)
    ->middleware(['tenant', 'throttle:mcp']);

// Replaces the 405 GET route registered by Mcp::web to send browsers to the docs.
Route::get('/{tenantSlug}/mcp', [ApiDocsController::class, 'mcp'])
    ->middleware(['web', 'tenant'])
    ->name('mcp');
