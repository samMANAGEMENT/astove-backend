<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Support\Facades\DB;

class PostgresServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Solo aplicar en PostgreSQL
        if (config('database.default') === 'pgsql') {
            $this->configurePostgresBooleans();
        }
    }

    /**
     * Configurar el manejo de booleanos para PostgreSQL
     */
    protected function configurePostgresBooleans()
    {
        // Interceptar las consultas de actualización
        DB::listen(function ($query) {
            if (str_contains(strtolower($query->sql), 'update') && str_contains($query->sql, 'pagado')) {
                // Reemplazar valores booleanos en las consultas
                $sql = $query->sql;
                $bindings = $query->bindings;
                
                foreach ($bindings as $key => $binding) {
                    if (is_bool($binding)) {
                        $bindings[$key] = $binding ? 'TRUE' : 'FALSE';
                    }
                }
                
                $query->bindings = $bindings;
            }
        });
    }
}
