<?php

namespace App\Models\Traits;

trait HasJsonAttributes
{
    /**
     * Désérialise les données PHP sérialisées
     *
     * @param mixed $data
     * @return mixed
     */
    protected function deserializePhpData($data)
    {
        if (is_string($data) && preg_match('/^O:\d+:"[^"]+"/', $data)) {
            return unserialize($data, [
                'allowed_classes' => [
                    'Illuminate\Support\Collection',
                    'Illuminate\Database\Eloquent\Collection',
                    'Illuminate\Contracts\Database\ModelIdentifier',
                    'App\Models\Dish',
                    'App\Models\DishCategory',
                    'App\Models\Information',
                    'App\Models\Tenant',
                    'Carbon\Carbon',
                ]
            ]);
        }
        
        if (is_array($data)) {
            return array_map([$this, 'deserializePhpData'], $data);
        }
        
        return $data;
    }

    /**
     * Récupère un attribut JSON
     *
     * @param string $key
     * @param mixed $value
     * @return mixed
     */
    public function getJsonAttribute($key, $value)
    {
        if (empty($value)) {
            return null;
        }

        $decoded = json_decode($value, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return $value;
        }

        return $this->deserializePhpData($decoded);
    }

    /**
     * Définit un attribut JSON
     *
     * @param string $key
     * @param mixed $value
     * @return string
     */
    public function setJsonAttribute($key, $value)
    {
        if (empty($value)) {
            return null;
        }

        return json_encode($value);
    }
} 