<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Dish extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'tenant_id',
        'date',
        'name',
        'tags',
        'type',
        'dishes_category_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<int, string>
     */
    protected $casts = [
        'date_carbon' => 'date:Y-m-d',
        'tags' => 'array',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = ['id', 'created_at', 'updated_at', 'dishes_category_id', 'tenant_id'];

    /**
     * The attributes that should be appended to arrays.
     *
     * @var array<int, string>
     */
    protected $appends = [];

    public static function getTagsDefinitions()
    {
        return [
            'halal' => [
                'name' => 'Halal',
                'name_short' => 'Halal',
                'icon' => 'fa-square-h',
                'emoji' => '🐐',
                'color' => '#007e45',
            ],
            'vegetarian' => [
                'name' => 'Végétarien',
                'name_short' => 'Végé.',
                'icon' => 'fa-seedling',
                'emoji' => '🌱',
                'color' => '#A6D64D',
            ],
            'fish' => [
                'name' => 'Poisson',
                'name_short' => 'Poisson',
                'icon' => 'fa-fish-fins',
                'emoji' => '🐟',
                'color' => '#4AB0F5',
            ],
            'sustainable_fishing' => [
                'name' => 'Pêche durable',
                'name_short' => 'Pêche durable',
                'icon' => 'fa-fish-fins',
                'emoji' => '🐟',
                'color' => '#4AB0F5',
            ],
            'equitable_trade' => [
                'name' => 'Commerce équitable',
                'name_short' => 'Commerce éq.',
                'icon' => 'fa-balance-scale',
                'emoji' => '🌍',
                'color' => '#147DE8',
            ],
            'france' => [
                'name' => 'Origine France',
                'name_short' => 'Orig. France',
                'icon' => 'fa-circle-f',
                'emoji' => '🐓',
                'color' => '#147DE8',
            ],
            'regional' => [
                'name' => 'Local & régional',
                'name_short' => 'Régional',
                'icon' => 'fa-tractor',
                'emoji' => '🚜',
                'color' => '#FFD124',
            ],
            'organic' => [
                'name' => 'Bio',
                'name_short' => 'Bio',
                'icon' => 'fa-leaf',
                'emoji' => '🌿',
                'color' => '#007e45',
            ],
            'seasonal' => [
                'name' => 'Saison',
                'name_short' => 'Saison',
                'icon' => 'fa-calendar',
                'emoji' => '🌼',
                'color' => '#FFD124',
            ],
        ];
    }

    public static function getTagName($tag)
    {
        return self::getTagsDefinitions()[$tag]['name'] ?? $tag;
    }

    public static function getTagShortName($tag)
    {
        return self::getTagsDefinitions()[$tag]['name_short'] ?? $tag;
    }

    public static function getTagIcon($tag)
    {
        return self::getTagsDefinitions()[$tag]['icon'] ?? 'fa-question';
    }

    public static function getTagEmoji($tag)
    {
        return self::getTagsDefinitions()[$tag]['emoji'] ?? '❓';
    }

    public static function getTagColor($tag)
    {
        return self::getTagsDefinitions()[$tag]['color'] ?? '#000000';
    }

    public function getDateCarbonAttribute()
    {
        return Carbon::parse($this->date);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(DishCategory::class, 'dishes_category_id');
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
