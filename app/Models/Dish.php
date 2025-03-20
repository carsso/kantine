<?php

namespace App\Models;

use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

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

    public static function getTagsTranslations($short = true)
    {
        return [
            'halal' => 'Halal',
            'vegetarian' => $short ? 'Végé.' : 'Végétarien',
            'fish' => 'Poisson',
            'france' => $short ? 'Orig. France' : 'Origine France',
            'regional' => 'Régional'
        ];
    }

    public static function getTagTranslation($tag, $short = true)
    {
        return self::getTagsTranslations($short)[$tag] ?? $tag;
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
