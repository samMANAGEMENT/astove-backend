<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Role;

class AssignDefaultRolesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:assign-default-roles {--admin-email=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Asignar roles por defecto a usuarios existentes';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Asignando roles por defecto a usuarios existentes...');

        // Obtener todos los usuarios sin rol
        $users = User::whereNull('role_id')->get();

        if ($users->isEmpty()) {
            $this->info('No hay usuarios sin rol asignado.');
            return;
        }

        $this->info("Encontrados {$users->count()} usuarios sin rol.");

        // Obtener roles disponibles
        $roles = Role::all();
        
        if ($roles->isEmpty()) {
            $this->error('No hay roles disponibles. Ejecuta primero el seeder de roles.');
            return;
        }

        // Obtener el email del admin si se proporciona
        $adminEmail = $this->option('admin-email');

        foreach ($users as $user) {
            $roleToAssign = null;
            
            // Si se proporciona un email de admin, asignar rol admin
            if ($adminEmail && $user->email === $adminEmail) {
                $roleToAssign = $roles->where('nombre', 'admin')->first();
                $this->info("Asignando rol ADMIN a {$user->email} (email especificado)");
            } else {
                // Por defecto, asignar rol operador
                $roleToAssign = $roles->where('nombre', 'operador')->first();
                $this->info("Asignando rol OPERADOR a {$user->email} (por defecto)");
            }

            if ($roleToAssign) {
                $user->update(['role_id' => $roleToAssign->id]);
                $this->info("✅ Rol '{$roleToAssign->nombre}' asignado a {$user->email}");
            } else {
                $this->error("❌ No se pudo asignar rol a {$user->email}");
            }
        }

        $this->info("\n🎉 Proceso completado exitosamente!");
        
        if (!$adminEmail) {
            $this->info("\n💡 Para asignar rol admin a un usuario específico, usa:");
            $this->info("   php artisan users:assign-default-roles --admin-email=tu-email@admin.com");
        }
    }
} 