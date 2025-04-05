<?php

namespace App\Lib;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;
use App\Models\Tenant;
use App\Models\DishCategory;
use App\Models\Dish;
use App\Models\Information;
use App\Services\DayService;
use App\Events\MenuUpdatedEvent;

class ApiRestaurationClient
{
    private Tenant $tenant;
    private string $baseUrl;
    private array $headers;
    private $logCallback;

    public function __construct(Tenant $tenant, $logCallback = null)
    {
        if(!$tenant->meta['api_url']) {
            throw new \Exception('Tenant '.$tenant->slug.' has no API URL');
        }
        if(!$tenant->meta['api_type']) {
            throw new \Exception('Tenant '.$tenant->slug.' has no API Type');
        }
        if($tenant->meta['api_type'] !== 'api-restauration') {
            throw new \Exception('Tenant '.$tenant->slug.' has an invalid API Type');
        }
        $this->tenant = $tenant;
        $this->baseUrl = rtrim($tenant->meta['api_url'], '/');
        $this->headers = [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'Referer' => Config::get('app.name', 'Kantine'),
        ];
        $this->logCallback = $logCallback;
    }

    private function log($message, $level = 'info', $data = [])
    {
        if ($this->logCallback && is_callable($this->logCallback)) {
            call_user_func($this->logCallback, $message, $level, $data);
        } else {
            Log::info($message, $data);
        }
    }

    public function compareMenus(array $apiMenus = null)
    {
        if(!$apiMenus) {
            throw new \Exception('No menus provided');
        }
        $currentMenus = [];
        foreach($apiMenus as $date => $apiMenu) {
            $currentMenu = app(DayService::class)->getDay($this->tenant, $date);
            if(!$currentMenu) { 
                $currentMenus[$date] = $apiMenu;
            } else {
                foreach($apiMenu['dishes'] as $dishType => $rootCategories) {
                    foreach($rootCategories as $rootCategorySlug => $subCategories) {
                        foreach($subCategories as $subCategorySlug => $dishes) {
                            $apiDishes = $dishes;
                            $currentDishes = isset($currentMenu['dishes'][$dishType][$rootCategorySlug][$subCategorySlug]) ? $currentMenu['dishes'][$dishType][$rootCategorySlug][$subCategorySlug]->toArray() : [];
                            usort($apiDishes, function($a, $b) {
                                if ($a['name'] !== $b['name']) {
                                    return strcmp($a['name'], $b['name']);
                                }
                                return strcmp(implode(',', $a['tags']), implode(',', $b['tags']));
                            });
                            usort($currentDishes, function($a, $b) {
                                if ($a['name'] !== $b['name']) {
                                    return strcmp($a['name'], $b['name']);
                                }
                                return strcmp(implode(',', $a['tags']), implode(',', $b['tags']));
                            });
                            foreach($apiDishes as $dishIdx => $apiDish) {
                                $currentDish = $currentDishes[$dishIdx] ?? null;
                                if(!$currentDish || $currentDish['name'] !== $apiDish['name'] || $currentDish['tags'] !== $apiDish['tags']) {
                                    $apiDish['_inconsistency_from'] = 'API';
                                    $apiDish['_inconsistency_other'] = $currentDish;
                                    $currentMenus[$date]['dishes'][$dishType][$rootCategorySlug][$subCategorySlug][$dishIdx] = $apiDish;
                                    $this->log('[' . $date . '] Inconsistance API trouvée pour ' . $dishType . ' ' . $rootCategorySlug . ' ' . $subCategorySlug . ' : ' . $apiDish['name'], 'info', $apiDish);
                                }
                            }
                        }
                    }
                }
                foreach($currentMenu['dishes'] as $dishType => $rootCategories) {
                    foreach($rootCategories as $rootCategorySlug => $subCategories) {
                        foreach($subCategories as $subCategorySlug => $dishes) {
                            $currentDishes = $dishes->toArray() ?? [];
                            $apiDishes = $apiMenu['dishes'][$dishType][$rootCategorySlug][$subCategorySlug] ?? [];
                            usort($currentDishes, function($a, $b) {
                                if ($a['name'] !== $b['name']) {
                                    return strcmp($a['name'], $b['name']);
                                }
                                return strcmp(implode(',', $a['tags']), implode(',', $b['tags']));
                            });
                            usort($apiDishes, function($a, $b) {
                                if ($a['name'] !== $b['name']) {
                                    return strcmp($a['name'], $b['name']);
                                }
                                return strcmp(implode(',', $a['tags']), implode(',', $b['tags']));
                            });
                            foreach($currentDishes as $dishIdx => $currentDish) {
                                $apiDish = $apiDishes[$dishIdx] ?? null;
                                if(!$apiDish || $apiDish['name'] !== $currentDish['name'] || $apiDish['tags'] !== $currentDish['tags']) {
                                    $currentDish['_inconsistency_from'] = 'DB';
                                    $currentDish['_inconsistency_other'] = $apiDish;
                                    $currentMenus[$date]['dishes'][$dishType][$rootCategorySlug][$subCategorySlug][$dishIdx] = $currentDish;
                                    $this->log('[' . $date . '] Inconsistance DB trouvée pour ' . $dishType . ' ' . $rootCategorySlug . ' ' . $subCategorySlug . ' : ' . $currentDish['name'], 'info', $currentDish);
                                }
                            }
                        }
                    }
                }
            }
        }

        return $currentMenus;
    }

    public function updateMenus(array $menus = null)
    {
        if(!$menus) {
            throw new \Exception('No menus provided');
        }

        $success = true;
        foreach ($menus as $date => $menuData) {
            // Validate date format
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
                throw new \Exception('Format de date invalide : ' . $date);
            }

            // Load all categories and create hierarchical slug to id mapping
            $categories = DishCategory::where('tenant_id', $this->tenant->id)
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
                throw new \Exception('Catégories invalides : ' . implode(', ', $invalidCategories) . '- Catégories valides : ' . implode(', ', array_keys($categorySlugToId)));
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
                throw new \Exception('Tags invalides : ' . implode(', ', $invalidTags) . '- Tags valides : ' . implode(', ', $allowedTags));
            }

            $dishIds = [];
            foreach ($menuData['dishes'] as $dishType => $rootCategories) {
                foreach ($rootCategories as $rootCategorySlug => $subCategories) {
                    foreach ($subCategories as $subCategorySlug => $dishes) {
                        $fullSlug = $dishType . '.' . $rootCategorySlug . '.' . $subCategorySlug;
                        foreach ($dishes as $dish) {
                            if(!isset($categorySlugToId[$fullSlug])) {
                                throw new \Exception('Catégorie invalide : ' . $fullSlug);
                            }
                            $createdDish = Dish::firstOrCreate([
                                'date' => $date,
                                'dishes_category_id' => $categorySlugToId[$fullSlug],
                                'name' => $dish['name'],
                                'tenant_id' => $this->tenant->id,
                                'tags' => $dish['tags'] ?? [],
                            ]);
                            $createdDish->tags = $dish['tags'] ?? [];
                            $createdDish->save();
                            $dishIds[] = $createdDish->id;
                        }
                    }
                }
            }

            // Delete dishes that are no longer present
            Dish::where('tenant_id', $this->tenant->id)
                ->where('date', $date)
                ->whereNotIn('id', $dishIds)
                ->delete();

            // Trigger menu update event
            $menu = app(DayService::class)->getDay($this->tenant, $date);
            if ($menu) {
                try {
                    MenuUpdatedEvent::dispatch($menu);
                } catch (\Exception $e) {
                    $this->log('Erreur lors de la mise à jour du menu', 'error', $e);
                }
            }
        }
        return $success;
    }

    /**
     * Récupère le menu depuis l'API
     *
     * @return array|null
     */
    public function getMenus(): ?array
    {
        try {
            $response = Http::withHeaders($this->headers)
                ->get($this->baseUrl);

            if ($response->successful()) {
                $apiMenus = $response->json();
                $this->log('API Menus', 'info', ['menus' => $apiMenus]);
                return $this->mapMenus($apiMenus);
            }

            $this->log('Erreur lors de la récupération du menu', 'error', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            throw new \Exception('Erreur lors de la récupération du menu: status '.$response->status().' body '.$response->body());
        } catch (\Exception $e) {
            $this->log('Exception lors de la récupération du menu', 'error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Transforme les données du menu de l'API vers notre format
     *
     * @param array $apiMenu
     * @return array
     */
    private function mapMenus(array $apiMenu): array
    {
        $mappedMenu = [];
        $today = date('Ymd');
        
        // Récupérer le mapping des feuilles vers les catégories et les plats statiques
        $categoryMapping = $this->tenant->meta['api_category_mapping'] ?? [];
        $staticDishes = $this->tenant->meta['api_static_dishes'] ?? [];
        
        // Première passe : collecter toutes les dates uniques et les plats disponibles tous les jours
        $dates = [];
        $dailyDishes = [];
        $dailyAccompaniments = [];
        
        foreach ($apiMenu as $item) {
            if ($item['periode'] === 'midi' && $item['rupture'] !== 'TRUE') {
                $feuille = $item['feuille'] ?? null;
                if(!isset($categoryMapping[$feuille])) {
                    $this->log('Catégorie non mappée : ' . $feuille, 'error');
                    continue;
                }
                $categorySlug = $categoryMapping[$feuille] ?? $feuille;

                if ($item['date'] === 'TRUE') {
                    $name = implode(', ', array_filter([
                        $item['nom'] ?? '',
                        $item['info1'] ?? '',
                        $item['info2'] ?? ''
                    ]));
                    if ($item['accompagnement'] === 'TRUE') {
                        $this->log('Garniture récurrente de la catégorie ' . $categorySlug . ' : ' . $name, 'info');
                        if(!isset($dailyAccompaniments[$categorySlug])) {
                            $dailyAccompaniments[$categorySlug] = [];
                        }
                        $dailyAccompaniments[$categorySlug][] = [
                            'name' => $name,
                            'tags' => $this->mapNutritionalInfo($item)
                        ];
                    } else {
                        $this->log('Plat récurrent de la catégorie ' . $categorySlug . ' : ' . $name, 'info');
                        if(!isset($dailyDishes[$categorySlug])) {
                            $dailyDishes[$categorySlug] = [];
                        }
                        $dailyDishes[$categorySlug][] = [
                            'name' => $name,
                            'tags' => $this->mapNutritionalInfo($item)
                        ];
                    }
                } else {
                    $dateUs = $item['dateUs'] ?? date('Ymd');
                    if ($dateUs >= $today || true) {
                        $formattedDate = date('Y-m-d', strtotime($dateUs));
                        $dates[$formattedDate] = true;
                    }
                }
            }
        }

        // Deuxième passe : pour chaque date, construire le menu avec les plats spécifiques et les plats quotidiens
        foreach ($dates as $formattedDate => $_) {
            $this->log('Traitement de la date : ' . $formattedDate, 'info');
            $mappedMenu[$formattedDate] = [
                'dishes' => [
                    'mains' => []
                ]
            ];

            // Ajouter les plats statiques
            foreach ($staticDishes as $type => $items) {
                $this->log('Ajout des plats statiques type ' . $type . ' :', 'info', $items);
                $mappedMenu[$formattedDate]['dishes'][$type] = $items;
            }

            // Initialiser les pôles avec les plats quotidiens
            foreach ($dailyDishes as $categorySlug => $dishes) {
                foreach($dishes as $dish) {
                    if (!isset($mappedMenu[$formattedDate]['dishes']['mains'][$categorySlug])) {
                        $mappedMenu[$formattedDate]['dishes']['mains'][$categorySlug] = [
                            'plats' => [],
                            'garnitures' => []
                        ];
                    }
                    $this->log('Ajout de plat récurrent type mains pour la catégorie ' . $categorySlug . ' : ' . $dish['name'], 'info');
                    $mappedMenu[$formattedDate]['dishes']['mains'][$categorySlug]['plats'][] = $dish;
                }
            }

            // Ajouter les accompagnements quotidiens
            foreach ($dailyAccompaniments as $categorySlug => $accompaniments) {
                foreach($accompaniments as $accompaniment) {
                    if (!isset($mappedMenu[$formattedDate]['dishes']['mains'][$categorySlug])) {
                        $mappedMenu[$formattedDate]['dishes']['mains'][$categorySlug] = [
                            'plats' => [],
                            'garnitures' => []
                        ];
                    }
                    $this->log('Ajout de garniture récurrente type mains pour la catégorie ' . $categorySlug . ' : ' . $accompaniment['name'], 'info');
                    $mappedMenu[$formattedDate]['dishes']['mains'][$categorySlug]['garnitures'][] = $accompaniment;
                }
            }

            // Ajouter les plats spécifiques à cette date
            foreach ($apiMenu as $item) {
                if ($item['periode'] === 'midi' && 
                    $item['date'] !== 'TRUE' && 
                    $item['rupture'] !== 'TRUE' &&
                    date('Y-m-d', strtotime($item['dateUs'])) === $formattedDate) {
                    
                    $feuille = $item['feuille'] ?? null;
                    if(!isset($categoryMapping[$feuille])) {
                        $this->log('Catégorie non mappée : ' . $feuille, 'error');
                        continue;
                    }
                    $categorySlug = $categoryMapping[$feuille] ?? $feuille;
                    $subCategorySlug = $item['accompagnement'] === 'TRUE' ? 'garnitures' : 'plats';
                    
                    if (!isset($mappedMenu[$formattedDate]['dishes']['mains'][$categorySlug])) {
                        $mappedMenu[$formattedDate]['dishes']['mains'][$categorySlug] = [
                            'plats' => [],
                            'garnitures' => []
                        ];
                    }

                    $name = implode(', ', array_filter([
                        $item['nom'] ?? '',
                        $item['info1'] ?? '',
                        $item['info2'] ?? ''
                    ]));

                    $this->log('Ajout de plat standard type mains de '.$subCategorySlug.' pour la catégorie '.$categorySlug.' : '.$name, 'info');
                    
                    if (!empty($name)) {
                        $mappedMenu[$formattedDate]['dishes']['mains'][$categorySlug][$subCategorySlug][] = [
                            'name' => $name,
                            'tags' => $this->mapNutritionalInfo($item)
                        ];
                    }
                }
            }
        }

        // Trier les dates
        ksort($mappedMenu);

        return $mappedMenu;
    }

    /**
     * Mappe les informations nutritionnelles vers nos tags
     *
     * @param array $item
     * @return array
     */
    private function mapNutritionalInfo(array $item): array
    {
        $tags = [];
        
        if ($item['vegetarien'] === 'TRUE') {
            $tags[] = 'vegetarian';
        }
        if ($item['bio'] === 'TRUE') {
            $tags[] = 'organic';
        }
        if ($item['local'] === 'TRUE') {
            $tags[] = 'regional';
        }
        if ($item['saison'] === 'TRUE') {
            $tags[] = 'seasonal';
        }
        if ($item['equitable'] === 'TRUE') {
            $tags[] = 'equitable_trade';
        }
        if ($item['peche'] === 'TRUE') {
            $tags[] = 'sustainable_fishing';
        }
        if ($item['france'] === 'TRUE') {
            $tags[] = 'france';
        }

        // Vérification pour le tag halal
        $searchText = strtolower(implode(' ', array_filter([
            $item['nom'] ?? '',
            $item['info1'] ?? '',
            $item['info2'] ?? ''
        ])));
        
        if (strpos($searchText, 'halal') !== false || strpos($searchText, 'hallal') !== false) {
            $tags[] = 'halal';
        }

        return $tags;
    }
} 