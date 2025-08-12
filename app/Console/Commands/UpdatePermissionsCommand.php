<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Permiso;
use App\Models\Role;

class UpdatePermissionsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permissions:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Actualizar permisos existentes agregando nuevos permisos';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Actualizando permisos...');

        // Lista de permisos a crear/actualizar
        $permisos = [
            [
                'nombre' => 'eliminar_servicios_realizados',
                'descripcion' => 'Eliminar servicios realizados',
                'modulo' => 'servicios'
            ],
            [
                'nombre' => 'eliminar_pagos',
                'descripcion' => 'Eliminar pagos',
                'modulo' => 'pagos'
            ]
        ];

        foreach ($permisos as $permisoData) {
            $permiso = Permiso::firstOrCreate(
                ['nombre' => $permisoData['nombre']],
                $permisoData
            );

            $this->info("Permiso '{$permisoData['nombre']}' creado/actualizado.");
        }

        // Asignar permisos a roles
        $adminRole = Role::where('nombre', 'admin')->first();
        $supervisorRole = Role::where('nombre', 'supervisor')->first();

        // Obtener todos los permisos de la lista
        $permisosIds = Permiso::whereIn('nombre', array_column($permisos, 'nombre'))->pluck('id')->toArray();

        if ($adminRole) {
            $adminRole->permisos()->syncWithoutDetaching($permisosIds);
            $this->info("Permisos asignados al rol 'admin'.");
        }

        if ($supervisorRole) {
            $supervisorRole->permisos()->syncWithoutDetaching($permisosIds);
            $this->info("Permisos asignados al rol 'supervisor'.");
        }

        $this->info('Permisos actualizados correctamente.');
    }
} 