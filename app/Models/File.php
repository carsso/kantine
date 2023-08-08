<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    public function getFilePathCsvAttribute()
    {
        return $this->file_path.'.csv';
    }
    
    public function menus(): HasMany
    {
        return $this->hasMany(Menu::class);
    }
}
