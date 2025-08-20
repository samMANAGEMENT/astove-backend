<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Http\Modules\Entidades\models\Entidades;

class EntidadesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $entidades = [
            [
                'nombre' => 'Sucursal Principal',
                'direccion' => 'Calle Principal #123',
                'estado' => true,
            ],
            [
                'nombre' => 'Sucursal Norte',
                'direccion' => 'Avenida Norte #456',
                'estado' => true,
            ],
            [
                'nombre' => 'Sucursal Sur',
                'direccion' => 'Calle Sur #789',
                'estado' => true,
            ],
        ];

        foreach ($entidades as $entidad) {
            Entidades::firstOrCreate(
                ['nombre' => $entidad['nombre']],
                $entidad
            );
        }

        $this->command->info('Entidades sembradas exitosamente.');
    }
}
