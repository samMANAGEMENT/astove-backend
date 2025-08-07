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

        // Crear el nuevo permiso si no existe
        $permiso = Permiso::firstOrCreate(
            ['nombre' => 'eliminar_servicios_realizados'],
            [
                'nombre' => 'eliminar_servicios_realizados',
                'descripcion' => 'Eliminar servicios realizados',
                'modulo' => 'servicios'
            ]
        );

        $this->info("Permiso 'eliminar_servicios_realizados' creado/actualizado.");

        // Asignar el permiso a admin y supervisor
        $adminRole = Role::where('nombre', 'admin')->first();
        $supervisorRole = Role::where('nombre', 'supervisor')->first();

        if ($adminRole) {
            $adminRole->permisos()->syncWithoutDetaching([$permiso->id]);
            $this->info("Permiso asignado al rol 'admin'.");
        }

        if ($supervisorRole) {
            $supervisorRole->permisos()->syncWithoutDetaching([$permiso->id]);
            $this->info("Permiso asignado al rol 'supervisor'.");
        }

        $this->info('Permisos actualizados correctamente.');
    }
} 