<?php

namespace App\Mcp\Tools\Concerns;

use App\Models\Tenant;

trait InteractsWithCanteen
{
    /**
     * The canteen resolved from the MCP endpoint URL by the tenant middleware.
     */
    protected function canteen(): Tenant
    {
        return request()->route('tenant');
    }
}
