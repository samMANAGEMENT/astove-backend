<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permiso;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear permisos
        $permisos = [
            // Dashboard
            ['nombre' => 'ver_dashboard_admin', 'descripcion' => 'Ver dashboard de administrador', 'modulo' => 'dashboard'],
            ['nombre' => 'ver_dashboard_operador', 'descripcion' => 'Ver dashboard de operador', 'modulo' => 'dashboard'],
            
            // Pagos
            ['nombre' => 'ver_pagos', 'descripcion' => 'Ver pagos', 'modulo' => 'pagos'],
            ['nombre' => 'crear_pagos', 'descripcion' => 'Crear pagos', 'modulo' => 'pagos'],
            ['nombre' => 'editar_pagos', 'descripcion' => 'Editar pagos', 'modulo' => 'pagos'],
            ['nombre' => 'eliminar_pagos', 'descripcion' => 'Eliminar pagos', 'modulo' => 'pagos'],
            ['nombre' => 'ver_servicios_empleado', 'descripcion' => 'Ver servicios del empleado', 'modulo' => 'pagos'],
            
            // Servicios
            ['nombre' => 'ver_servicios', 'descripcion' => 'Ver servicios', 'modulo' => 'servicios'],
            ['nombre' => 'crear_servicios', 'descripcion' => 'Crear servicios', 'modulo' => 'servicios'],
            ['nombre' => 'editar_servicios', 'descripcion' => 'Editar servicios', 'modulo' => 'servicios'],
            ['nombre' => 'eliminar_servicios', 'descripcion' => 'Eliminar servicios', 'modulo' => 'servicios'],
            
            // Operadores
            ['nombre' => 'ver_operadores', 'descripcion' => 'Ver operadores', 'modulo' => 'operadores'],
            ['nombre' => 'crear_operadores', 'descripcion' => 'Crear operadores', 'modulo' => 'operadores'],
            ['nombre' => 'editar_operadores', 'descripcion' => 'Editar operadores', 'modulo' => 'operadores'],
            ['nombre' => 'eliminar_operadores', 'descripcion' => 'Eliminar operadores', 'modulo' => 'operadores'],
            
            // Productos
            ['nombre' => 'ver_productos', 'descripcion' => 'Ver productos', 'modulo' => 'productos'],
            ['nombre' => 'crear_productos', 'descripcion' => 'Crear productos', 'modulo' => 'productos'],
            ['nombre' => 'editar_productos', 'descripcion' => 'Editar productos', 'modulo' => 'productos'],
            ['nombre' => 'eliminar_productos', 'descripcion' => 'Eliminar productos', 'modulo' => 'productos'],
            
            // Ventas
            ['nombre' => 'ver_ventas', 'descripcion' => 'Ver ventas', 'modulo' => 'ventas'],
            ['nombre' => 'crear_ventas', 'descripcion' => 'Crear ventas', 'modulo' => 'ventas'],
            ['nombre' => 'editar_ventas', 'descripcion' => 'Editar ventas', 'modulo' => 'ventas'],
            ['nombre' => 'eliminar_ventas', 'descripcion' => 'Eliminar ventas', 'modulo' => 'ventas'],
            
            // Entidades
            ['nombre' => 'ver_entidades', 'descripcion' => 'Ver entidades', 'modulo' => 'entidades'],
            ['nombre' => 'crear_entidades', 'descripcion' => 'Crear entidades', 'modulo' => 'entidades'],
            ['nombre' => 'editar_entidades', 'descripcion' => 'Editar entidades', 'modulo' => 'entidades'],
            ['nombre' => 'eliminar_entidades', 'descripcion' => 'Eliminar entidades', 'modulo' => 'entidades'],
            
            // Roles y Permisos (Solo Admin)
            ['nombre' => 'ver_roles', 'descripcion' => 'Ver roles y permisos', 'modulo' => 'roles'],
            ['nombre' => 'crear_roles', 'descripcion' => 'Crear roles', 'modulo' => 'roles'],
            ['nombre' => 'editar_roles', 'descripcion' => 'Editar roles', 'modulo' => 'roles'],
            ['nombre' => 'eliminar_roles', 'descripcion' => 'Eliminar roles', 'modulo' => 'roles'],
        ];

        foreach ($permisos as $permiso) {
            Permiso::firstOrCreate(
                ['nombre' => $permiso['nombre']],
                $permiso
            );
        }

        // Crear roles
        $roles = [
            [
                'nombre' => 'admin',
                'descripcion' => 'Administrador del sistema con acceso completo',
                'permisos' => Permiso::all()->pluck('id')->toArray()
            ],
            [
                'nombre' => 'supervisor',
                'descripcion' => 'Supervisor con acceso a gestión de entidad',
                'permisos' => [
                    'ver_dashboard_operador',
                    'ver_pagos', 'crear_pagos', 'editar_pagos',
                    'ver_servicios', 'crear_servicios', 'editar_servicios',
                    'ver_operadores', 'crear_operadores', 'editar_operadores',
                    'ver_productos', 'crear_productos', 'editar_productos',
                    'ver_ventas', 'crear_ventas', 'editar_ventas',
                    'ver_entidades'
                ]
            ],
            [
                'nombre' => 'operador',
                'descripcion' => 'Operador con acceso limitado a su información',
                'permisos' => [
                    'ver_dashboard_operador',
                    'ver_pagos',
                    'ver_servicios_empleado',
                    'ver_servicios'
                ]
            ]
        ];

        foreach ($roles as $roleData) {
            $permisosIds = $roleData['permisos'];
            unset($roleData['permisos']);
            
            $role = Role::firstOrCreate(
                ['nombre' => $roleData['nombre']],
                $roleData
            );
            
            if (is_array($permisosIds)) {
                // Si son nombres de permisos, buscar por nombre
                $permisos = Permiso::whereIn('nombre', $permisosIds)->get();
                $role->permisos()->sync($permisos);
            } else {
                // Si son IDs, usar directamente
                $role->permisos()->sync($permisosIds);
            }
        }
    }
} 