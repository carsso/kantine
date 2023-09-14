<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Menu extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'date',
        'event_name',
        'starters',
        'mains',
        'sides',
        'cheeses',
        'desserts',
        'file_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<int, string>
     */
    protected $casts = [
        'date_carbon' => 'date:Y-m-d',
        'starters' => 'array',
        'mains' => 'array',
        'sides' => 'array',
        'cheeses' => 'array',
        'desserts' => 'array',
        'is_fries_day' => 'boolean',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = ['id', 'file_id'];

    public function getIsFriesDayAttribute()
    {
        return str_contains(join(', ', $this->sides), 'Frites');
    }

    public function getDateCarbonAttribute()
    {
        return Carbon::parse($this->date);
    }

    public function file(): BelongsTo
    {
        return $this->belongsTo(File::class);
    }
}
