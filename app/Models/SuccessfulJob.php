<?php

namespace App\Models;

use App\Models\Traits\HasJsonAttributes;
use Illuminate\Database\Eloquent\Model;

class SuccessfulJob extends Model
{
    use HasJsonAttributes;

    protected $table = 'successful_jobs';

    protected $fillable = [
        'id',
        'uuid',
        'connection',
        'queue',
        'payload',
        'result',
        'finished_at',
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'finished_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    public function getPayloadAttribute($value)
    {
        return $this->getJsonAttribute('payload', $value);
    }

    public function getResultAttribute($value)
    {
        return $this->getJsonAttribute('result', $value);
    }
} 