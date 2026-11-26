<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class MenuLinkController extends Controller
{
    /**
     * Redirect to the tenant's private link shown under the menus, so its URL stays out of the page.
     */
    public function redirect(Request $request): RedirectResponse
    {
        $url = $request->route('tenant')->meta['link_url'] ?? null;
        if (! $url) {
            abort(404, 'Aucun lien trouvé');
        }

        return redirect($url);
    }
}
