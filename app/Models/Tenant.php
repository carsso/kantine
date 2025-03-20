<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Tenant extends Model
{
    use HasSlug;
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'is_active',
        'webex_bearer_token',
        'webex_bot_name',
    ];

    protected $hidden = [
        'webex_bearer_token',
        'id',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug');
    }

    public function dishes(): HasMany
    {
        return $this->hasMany(Dish::class);
    }

    public function categories(): HasMany
    {
        return $this->hasMany(DishCategory::class);
    }

    public function information(): HasMany
    {
        return $this->hasMany(Information::class);
    }
}
