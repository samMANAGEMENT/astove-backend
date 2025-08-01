<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Role;

class AssignRolesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:assign-roles';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Asignar roles a usuarios existentes';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Asignando roles a usuarios existentes...');

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

        $this->info("\nRoles disponibles:");
        foreach ($roles as $role) {
            $this->info("  {$role->id}. {$role->nombre} - {$role->descripcion}");
        }

        foreach ($users as $user) {
            $this->info("\nUsuario: {$user->email}");
            
            // Mostrar opciones de roles
            $roleId = $this->ask("Selecciona el ID del rol para este usuario (1-{$roles->count()}):");
            
            // Validar que el ID sea válido
            $selectedRole = $roles->find($roleId);
            
            if (!$selectedRole) {
                $this->error("❌ ID de rol '{$roleId}' no válido. Saltando usuario.");
                continue;
            }

            // Asignar rol
            $user->update(['role_id' => $selectedRole->id]);
            
            $this->info("✅ Rol '{$selectedRole->nombre}' asignado a {$user->email}");
        }

        $this->info("\n🎉 Proceso completado exitosamente!");
    }
} 