<?php

namespace App\Traits;

use Illuminate\Support\Facades\DB;

trait PostgresBooleanTrait
{
    /**
     * Convertir valores booleanos para PostgreSQL
     */
    protected function convertBooleanForPostgres($value)
    {
        if (is_bool($value)) {
            return $value ? DB::raw('TRUE') : DB::raw('FALSE');
        }
        return $value;
    }

    /**
     * Preparar datos para inserción en PostgreSQL
     */
    protected function prepareDataForPostgres(array $data)
    {
        $prepared = [];
        foreach ($data as $key => $value) {
            if (is_bool($value)) {
                $prepared[$key] = $this->convertBooleanForPostgres($value);
            } else {
                $prepared[$key] = $value;
            }
        }
        return $prepared;
    }
}
