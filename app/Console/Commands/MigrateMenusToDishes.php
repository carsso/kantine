<?php

namespace App\Console\Commands;

use App\Models\Menu;
use App\Models\Dish;
use App\Models\Information;
use App\Models\DishCategory;
use Illuminate\Console\Command;

class MigrateMenusToDishes extends Command
{
    protected $signature = 'kantine:migrate-menus-to-dishes';
    protected $description = 'Migrate data from menus table to dishes and dishes_categories tables';

    // Mapping menu fields to category types
    private $menuFieldsMapping = [
        'starters' => [
            'Entrées' => [
                'starters' => [
                    'name' => 'Entrées',
                    'color' => '#A6D64D',
                    'icon' => 'fa-salad',
                    'emoji' => '🥗',
                    'sort_order' => 100,
                ]
            ]
        ],
        'mains' => [
            'Libéro' => [
                'liberos' => [
                    'name' => 'Libéro',
                    'color' => '#4AB0F5',
                    'icon' => 'fa-pan-frying',
                    'emoji' => '🍳',
                    'sort_order' => 200,
                ],
            ],
            'Plats' => [
                'mains' => [
                    'name' => 'Plats',
                    'color' => '#ED733D',
                    'icon' => 'fa-turkey',
                    'emoji' => '🍗',
                    'sort_order' => 300,
                ],
                'sides' => [
                    'name' => 'Garnitures',
                    'color' => '#FFD124',
                    'icon' => 'fa-carrot',
                    'emoji' => '🥕',
                    'sort_order' => 400,
                ],
            ],
        ],
        'desserts' => [
            'Fromages/Laitages' => [
                'cheeses' => [
                    'name' => 'Fromages/Laitages',
                    'color' => '#73E3FF',
                    'icon' => 'fa-cheese-swiss',
                    'emoji' => '🧀',
                    'sort_order' => 500,
                ],
            ],
            'Desserts' => [
                'desserts' => [
                    'name' => 'Desserts',
                    'color' => '#147DE8',
                    'icon' => 'fa-cupcake',
                    'emoji' => '🍰',
                    'sort_order' => 600,
                ],
            ]
        ]
    ];

    public function handle()
    {

        $dishes = Dish::count();
        if($dishes > 0) {
            $this->info('Dishes already migrated');
            return;
        }

        $this->info('Starting migration of menus to dishes...');

        $categories = [];
        foreach ($this->menuFieldsMapping as $type => $subCategories) {
            foreach ($subCategories as $typeName => $subsubCategories) {
                foreach ($subsubCategories as $field => $subsubCategory) {
                    $name = $subsubCategory['name'];
                    $color = $subsubCategory['color'];
                    $icon = $subsubCategory['icon'];
                    $emoji = $subsubCategory['emoji'];
                    $sortOrder = $subsubCategory['sort_order'];

                    $categories[$typeName] = DishCategory::firstOrCreate([
                        'name' => $typeName,
                        'parent_id' => null,
                        'type' => $type,
                        'color' => $color,
                        'icon' => $icon,
                        'emoji' => $emoji,
                        'sort_order' => $sortOrder,
                    ]);
                    $categories[$typeName.'-'.$name] = DishCategory::firstOrCreate([
                        'name' => $name,
                        'parent_id' => $categories[$typeName]->id,
                        'type' => $type,
                        'color' => $color,
                        'icon' => $icon,
                        'emoji' => $emoji,
                        'sort_order' => $sortOrder,
                    ]);
                }
            }
        }

        // Get all menus
        $menus = Menu::all();
        $this->info(sprintf('Migrating %d menus...', $menus->count()));

        $bar = $this->output->createProgressBar($menus->count());
        $bar->start();

        foreach ($menus as $menu) {
            // Create information record
            if ($menu->event_name || $menu->information || $menu->style) {
                Information::firstOrCreate([
                    'date' => $menu->date,
                ], [
                    'event_name' => $menu->event_name,
                    'information' => $menu->information,
                    'style' => $menu->style,
                ]);
            }

            foreach ($this->menuFieldsMapping as $type => $subCategories) {
                foreach ($subCategories as $typeName => $subsubCategories) {
                    foreach ($subsubCategories as $field => $subsubCategory) {
                        $name = $subsubCategory['name'];

                        $dishes = $menu->{$field} ?? [];
                        if (!is_array($dishes)) {
                            continue;
                        }

                        foreach ($dishes as $dishIndex => $dishName) {
                            if (empty($dishName)) {
                                continue;
                            }

                            $tags = [];
                            if ($field === 'mains') {
                                foreach ($menu->mains_special_indexes as $tag => $index) {
                                    if ($index === $dishIndex) {
                                        $tags[] = $tag;
                                    }
                                }
                            }

                            Dish::firstOrCreate([
                                'date' => $menu->date,
                                'name' => $dishName,
                                'dishes_category_id' => $categories[$typeName.'-'.$name]->id,
                                'tags' => $tags,
                            ]);
                        }
                    }
                }
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('Migration completed successfully!');
    }
}