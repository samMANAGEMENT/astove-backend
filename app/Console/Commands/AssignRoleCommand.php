<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Role;

class AssignRoleCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:assign-role {--email=} {--role=} {--user-id=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Asignar rol a un usuario específico';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->option('email');
        $roleName = $this->option('role');
        $userId = $this->option('user-id');

        if (!$email && !$userId) {
            $this->error('Debe especificar --email o --user-id');
            return 1;
        }

        if (!$roleName) {
            $this->error('Debe especificar --role');
            return 1;
        }

        // Buscar usuario
        $user = null;
        if ($email) {
            $user = User::where('email', $email)->first();
        } elseif ($userId) {
            $user = User::find($userId);
        }

        if (!$user) {
            $this->error('Usuario no encontrado');
            return 1;
        }

        // Buscar rol
        $role = Role::where('nombre', $roleName)->first();
        if (!$role) {
            $this->error("Rol '{$roleName}' no encontrado");
            $this->info('Roles disponibles:');
            Role::all()->each(function ($r) {
                $this->info("  • {$r->nombre}");
            });
            return 1;
        }

        // Asignar rol
        $user->role_id = $role->id;
        $user->save();

        $this->info("✅ Rol '{$roleName}' asignado exitosamente a {$user->email}");
        $this->info("   Usuario: {$user->name}");
        $this->info("   Rol: {$role->nombre}");
        $this->info("   Descripción del rol: {$role->descripcion}");

        return 0;
    }
}
