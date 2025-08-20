<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        // Crear cargo Desarrollador
        \App\Http\Modules\Cargos\models\Cargos::firstOrCreate([
            'nombre' => 'Desarrollador',
        ], [
            'sueldo_base' => 0
        ]);

        // Crear Entidad de Prueba
        \App\Http\Modules\Entidades\models\Entidades::firstOrCreate([
            'nombre' => 'suitpress IAO',
        ], [
            'direccion' => 'null',
            'estado' => 'true'
        ]);

        // Ejecutar los seeders
        $this->call([
            EntidadesSeeder::class,
            ServiciosSeeder::class,
            ProductosSeeder::class,
            RolesAndPermissionsSeeder::class,
        ]);
    }
}
