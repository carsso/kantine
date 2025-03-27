<?php

namespace App\Models;

use App\Models\Traits\HasJsonAttributes;
use Illuminate\Database\Eloquent\Model;

class Job extends Model
{
    use HasJsonAttributes;

    protected $table = 'jobs';

    protected $fillable = [
        'id',
        'queue',
        'payload',
        'attempts',
        'reserved_at',
        'available_at',
        'created_at'
    ];

    protected $casts = [
        'reserved_at' => 'integer',
        'available_at' => 'integer',
        'created_at' => 'integer'
    ];

    public function getPayloadAttribute($value)
    {
        return $this->getJsonAttribute('payload', $value);
    }
} 