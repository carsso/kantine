<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DishCategory;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CategoryLinkController extends Controller
{
    public function __construct()
    {
        $this->middleware('restrict.ip');
    }

    public function redirect(Request $request)
    {
        $type = $request->route('type');
        $parentSlug = $request->route('parentSlug');
        $childSlug = $request->route('childSlug');
        $tenant = $request->route('tenant');

        $category = DishCategory::where('name_slug', $childSlug)
            ->whereHas('parent', function ($query) use ($parentSlug, $type) {
                $query->where('name_slug', $parentSlug);
                $query->where('type', $type);
            })
            ->where('tenant_id', $tenant->id)
            ->first();
        if (!$category) {
            abort(404, 'Catégorie non trouvée');
        }

        $meta = $category->meta;
        if (isset($meta['link_url'])) {
            return redirect($meta['link_url']);
        }

        abort(404, 'Aucun lien trouvé');
    }
} 