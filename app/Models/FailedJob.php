<?php

namespace App\Models;

use App\Models\Traits\HasJsonAttributes;
use Illuminate\Database\Eloquent\Model;

class FailedJob extends Model
{
    use HasJsonAttributes;

    protected $table = 'failed_jobs';

    protected $fillable = [
        'id',
        'uuid',
        'connection',
        'queue',
        'payload',
        'exception',
        'failed_at'
    ];

    protected $casts = [
        'failed_at' => 'datetime'
    ];

    public function getPayloadAttribute($value)
    {
        return $this->getJsonAttribute('payload', $value);
    }
} 