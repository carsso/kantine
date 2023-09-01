<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class File extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'hash',
        'name',
        'file_path',
        'state',
        'message'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<int, string>
     */
    protected $casts = [
        'datetime_carbon' => 'date',
    ];

    public function getFilePathCsvAttribute()
    {
        return $this->file_path.'.csv';
    }

    public function getDatetimeCarbonAttribute()
    {
        return $this->datetime ? Carbon::parse($this->datetime) : null;
    }
    
    public function menus(): HasMany
    {
        return $this->hasMany(Menu::class);
    }
}
