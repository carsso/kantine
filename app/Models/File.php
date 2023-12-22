<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
     * The attributes that should be appended to arrays.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'file_path_csv',
        'datetime_carbon',
        'filename_year',
        'filename_week',
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

    public function getFilenameYearAttribute()
    {
        if(!preg_match('/^S[0-9]+-([0-9]{4})\.pdf$/', $this->name, $matches)) {
            return null;
        }
        return $matches[1];
    }

    public function getFilenameWeekAttribute()
    {
        if(!preg_match('/^S[0-9]+-([0-9]{4})\.pdf$/', $this->name, $matches)) {
            return null;
        }
        return $matches[0];
    }

    public function menus(): HasMany
    {
        return $this->hasMany(Menu::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
