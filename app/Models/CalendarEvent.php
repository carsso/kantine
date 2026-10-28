<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A dated event added to a tenant's themes calendar (World Cup, Roland-Garros…).
 */
class CalendarEvent extends Model
{
    use HasFactory;

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'tenant_id',
        'label',
        'style',
        'starts_on',
        'ends_on',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'starts_on' => 'immutable_date',
        'ends_on' => 'immutable_date',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
