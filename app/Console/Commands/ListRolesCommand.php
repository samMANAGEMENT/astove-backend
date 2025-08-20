<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Role;
use App\Models\Permiso;

class ListRolesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'roles:list';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Listar todos los roles y sus permisos';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('=== ROLES Y PERMISOS DEL SISTEMA ===');
        
        $roles = Role::with('permisos')->get();
        
        foreach ($roles as $role) {
            $this->info("\n📋 Rol: {$role->nombre}");
            $this->info("   Descripción: {$role->descripcion}");
            $this->info("   Estado: " . ($role->estado ? '✅ Activo' : '❌ Inactivo'));
            
            if ($role->permisos->count() > 0) {
                $this->info("   Permisos ({$role->permisos->count()}):");
                
                // Agrupar permisos por módulo
                $permisosPorModulo = $role->permisos->groupBy('modulo');
                
                foreach ($permisosPorModulo as $modulo => $permisos) {
                    $this->info("     📁 {$modulo}:");
                    foreach ($permisos as $permiso) {
                        $this->info("       • {$permiso->nombre} - {$permiso->descripcion}");
                    }
                }
            } else {
                $this->warn("   ⚠️  No tiene permisos asignados");
            }
        }
        
        $this->info("\n=== PERMISOS DISPONIBLES ===");
        $permisos = Permiso::all()->groupBy('modulo');
        
        foreach ($permisos as $modulo => $permisosModulo) {
            $this->info("\n📁 Módulo: {$modulo}");
            foreach ($permisosModulo as $permiso) {
                $this->info("   • {$permiso->nombre} - {$permiso->descripcion}");
            }
        }
    }
}
