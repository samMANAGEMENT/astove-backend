<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Role;
use App\Models\Permiso;

class AddPermissionCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'roles:add-permission {--role=} {--permission=} {--module=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Agregar permiso a un rol específico';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $roleName = $this->option('role');
        $permissionName = $this->option('permission');
        $module = $this->option('module');

        if (!$roleName) {
            $this->error('Debe especificar --role');
            $this->info('Roles disponibles:');
            Role::all()->each(function ($r) {
                $this->info("  • {$r->nombre}");
            });
            return 1;
        }

        if (!$permissionName) {
            $this->error('Debe especificar --permission');
            return 1;
        }

        // Buscar rol
        $role = Role::where('nombre', $roleName)->first();
        if (!$role) {
            $this->error("Rol '{$roleName}' no encontrado");
            return 1;
        }

        // Buscar permiso
        $permission = Permiso::where('nombre', $permissionName)->first();
        if (!$permission) {
            $this->error("Permiso '{$permissionName}' no encontrado");
            
            if ($module) {
                $this->info("Permisos disponibles para el módulo '{$module}':");
                Permiso::where('modulo', $module)->get()->each(function ($p) {
                    $this->info("  • {$p->nombre} - {$p->descripcion}");
                });
            } else {
                $this->info('Permisos disponibles:');
                Permiso::all()->groupBy('modulo')->each(function ($permisos, $modulo) {
                    $this->info("  📁 {$modulo}:");
                    foreach ($permisos as $permiso) {
                        $this->info("    • {$permiso->nombre} - {$permiso->descripcion}");
                    }
                });
            }
            return 1;
        }

        // Verificar si ya tiene el permiso
        if ($role->permisos()->where('permiso_id', $permission->id)->exists()) {
            $this->warn("⚠️  El rol '{$roleName}' ya tiene el permiso '{$permissionName}'");
            return 0;
        }

        // Asignar permiso
        $role->permisos()->attach($permission->id);

        $this->info("✅ Permiso '{$permissionName}' agregado exitosamente al rol '{$roleName}'");
        $this->info("   Módulo: {$permission->modulo}");
        $this->info("   Descripción: {$permission->descripcion}");

        return 0;
    }
}
