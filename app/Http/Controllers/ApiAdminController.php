<?php

namespace App\Http\Controllers;
use App\Events\MenuUpdatedEvent;
use App\Http\Requests\UpdateMenuFormRequest;
use App\Http\Requests\UpdateMenuApiRequest;
use App\Libraries\WebexApi;
use App\Models\Dish;
use App\Models\DishCategory;
use App\Models\Information;
use App\Services\DayService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\HttpException;
use App\Jobs\ProcessWebexMenuNotification;
use App\Models\Menu;
use Illuminate\Http\Request;
use App\Models\Tenant;

class ApiAdminController extends Controller
{
    public function __construct()
    {
        $this->middleware(['permission:admin', 'tenant-admin']);
    }

    public function menu(Request $request, DayService $dayService)
    {
        $tenant = $request->route('tenant');
        $dateString = $request->route('date');
        return $dayService->getDay($tenant, $dateString);
    }

    public function updateMenuApi(UpdateMenuApiRequest $request, DayService $dayService)
    {
        $user = $request->user();
        $tenant = $request->route('tenant');
        $date = $request->route('date');

        // Validate date format
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return response()->json([
                'error' => 'Format de date invalide',
                'message' => 'La date doit être au format YYYY-MM-DD'
            ], 422);
        }

        $menuData = $request->validated();

        // Validate style if provided
        if (isset($menuData['information']['style']) && $menuData['information']['style'] !== '') {
            $allowedStyles = array_keys(config('tsparticles.config', []));
            if (!in_array($menuData['information']['style'], $allowedStyles)) {
                return response()->json([
                    'error' => 'Style invalide',
                    'invalid_style' => $menuData['information']['style'],
                    'allowed_styles' => $allowedStyles
                ], 422);
            }
        }

        // Load all categories and create hierarchical slug to id mapping
        $categories = DishCategory::where('tenant_id', $tenant->id)
            ->with('parent')
            ->get();
        $categorySlugToId = [];
        foreach ($categories as $category) {
            if ($category->parent_id) {
                $categorySlugToId[$category->parent->type . '.' . $category->parent->name_slug . '.' . $category->name_slug] = $category->id;
            }
        }

        // Validate all categories first
        $invalidCategories = [];
        foreach ($menuData['dishes'] as $dishType => $rootCategories) {
            foreach ($rootCategories as $rootCategorySlug => $subCategories) {
                foreach ($subCategories as $subCategorySlug => $dishes) {
                    $fullSlug = $dishType . '.' . $rootCategorySlug . '.' . $subCategorySlug;
                    if (!isset($categorySlugToId[$fullSlug])) {
                        $invalidCategories[] = $fullSlug;
                    }
                }
            }
        }

        if (!empty($invalidCategories)) {
            return response()->json([
                'error' => 'Catégories invalides',
                'invalid_categories' => $invalidCategories,
                'allowed_categories' => array_keys($categorySlugToId)
            ], 422);
        }

        // Validate all tags
        $allowedTags = array_keys(Dish::getTagsDefinitions());
        $invalidTags = [];
        foreach ($menuData['dishes'] as $dishType => $rootCategories) {
            foreach ($rootCategories as $rootCategorySlug => $subCategories) {
                foreach ($subCategories as $subCategorySlug => $dishes) {
                    foreach ($dishes as $dish) {
                        if (isset($dish['tags']) && is_array($dish['tags'])) {
                            foreach ($dish['tags'] as $tag) {
                                if (!in_array($tag, $allowedTags)) {
                                    $invalidTags[] = $tag;
                                }
                            }
                        }
                    }
                }
            }
        }

        if (!empty($invalidTags)) {
            return response()->json([
                'error' => 'Tags invalides',
                'invalid_tags' => array_unique($invalidTags),
                'allowed_tags' => $allowedTags
            ], 422);
        }

        // If we get here, all categories and tags are valid
        $dishIds = [];
        foreach ($menuData['dishes'] as $dishType => $rootCategories) {
            foreach ($rootCategories as $rootCategorySlug => $subCategories) {
                foreach ($subCategories as $subCategorySlug => $dishes) {
                    $fullSlug = $dishType . '.' . $rootCategorySlug . '.' . $subCategorySlug;
                    foreach ($dishes as $dish) {
                        $createdDish = Dish::firstOrCreate([
                            'date' => $date,
                            'dishes_category_id' => $categorySlugToId[$fullSlug],
                            'name' => $dish['name'],
                            'tenant_id' => $tenant->id,
                            'tags' => $dish['tags'] ?? [],
                        ]);
                        $dishIds[] = $createdDish->id;
                    }
                }
            }
        }

        // Delete dishes that are no longer present
        Dish::where('tenant_id', $tenant->id)
            ->where('date', $date)
            ->whereNotIn('id', $dishIds)
            ->delete();

        // Process information
        if (isset($menuData['information'])) {
            $information = Information::firstOrCreate([
                'date' => $date,
                'tenant_id' => $tenant->id,
            ]);

            $information->event_name = $menuData['information']['event_name'] ?? '';
            $information->information = $menuData['information']['information'] ?? '';
            $information->style = $menuData['information']['style'] ?? '';
            $information->save();
        }

        // Trigger menu update event
        $menu = $dayService->getDay($tenant, $date);
        if ($menu) {
            try {
                MenuUpdatedEvent::dispatch($menu);
            } catch (\Exception $e) {
                Log::error($e);
            }
        }

        return response()->json([
            'message' => 'Menu mis à jour avec succès',
            'menu' => $menu
        ]);
    }
}
