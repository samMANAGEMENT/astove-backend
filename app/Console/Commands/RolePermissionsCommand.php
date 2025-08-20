<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Role;
use App\Models\Permiso;

class RolePermissionsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'roles:permissions {--role=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Ver permisos de un rol específico';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $roleName = $this->option('role');

        if (!$roleName) {
            $this->error('Debe especificar --role');
            $this->info('Roles disponibles:');
            Role::all()->each(function ($r) {
                $this->info("  • {$r->nombre}");
            });
            return 1;
        }

        $role = Role::where('nombre', $roleName)->first();
        if (!$role) {
            $this->error("Rol '{$roleName}' no encontrado");
            return 1;
        }

        $this->info("=== PERMISOS DEL ROL: {$role->nombre} ===");
        $this->info("Descripción: {$role->descripcion}");
        $this->info("Estado: " . ($role->estado ? '✅ Activo' : '❌ Inactivo'));

        if ($role->permisos->count() > 0) {
            $this->info("\n📋 Permisos asignados ({$role->permisos->count()}):");
            
            // Agrupar permisos por módulo
            $permisosPorModulo = $role->permisos->groupBy('modulo');
            
            foreach ($permisosPorModulo as $modulo => $permisos) {
                $this->info("\n📁 Módulo: {$modulo}");
                foreach ($permisos as $permiso) {
                    $this->info("   ✅ {$permiso->nombre} - {$permiso->descripcion}");
                }
            }

            // Mostrar permisos que NO tiene
            $todosLosPermisos = Permiso::all();
            $permisosNoAsignados = $todosLosPermisos->diff($role->permisos);
            
            if ($permisosNoAsignados->count() > 0) {
                $this->info("\n❌ Permisos NO asignados ({$permisosNoAsignados->count()}):");
                
                $permisosNoAsignadosPorModulo = $permisosNoAsignados->groupBy('modulo');
                
                foreach ($permisosNoAsignadosPorModulo as $modulo => $permisos) {
                    $this->info("\n📁 Módulo: {$modulo}");
                    foreach ($permisos as $permiso) {
                        $this->info("   ❌ {$permiso->nombre} - {$permiso->descripcion}");
                    }
                }
            }
        } else {
            $this->warn("⚠️  Este rol no tiene permisos asignados");
        }

        return 0;
    }
}
