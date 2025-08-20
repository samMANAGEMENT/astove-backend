<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Http\Modules\servicios\models\Servicios;
use App\Http\Modules\Entidades\models\Entidades;

class ServiciosEntidad6Seeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Verificar que existe la entidad 6
        $entidad = Entidades::find(6);
        
        if (!$entidad) {
            $this->command->error('La entidad 6 no existe. Cree la entidad primero.');
            return;
        }
        
        $servicios = [
            // Servicios Caballero
            [
                'nombre' => 'Manicura tradicional caballero',
                'precio' => 17000,
                'estado' => true,
                'porcentaje_pago_empleado' => 50.00,
            ],
            [
                'nombre' => 'Pedicura tradicional caballero',
                'precio' => 18000,
                'estado' => true,
                'porcentaje_pago_empleado' => 50.00,
            ],
            [
                'nombre' => 'Completo tradicional',
                'precio' => 31000,
                'estado' => true,
                'porcentaje_pago_empleado' => 50.00,
            ],
            [
                'nombre' => 'Manicura semi',
                'precio' => 27000,
                'estado' => true,
                'porcentaje_pago_empleado' => 50.00,
            ],
            [
                'nombre' => 'Pedicura semi',
                'precio' => 30000,
                'estado' => true,
                'porcentaje_pago_empleado' => 50.00,
            ],
            [
                'nombre' => 'Completo',
                'precio' => 42000,
                'estado' => true,
                'porcentaje_pago_empleado' => 50.00,
            ],
            [
                'nombre' => 'Manos semi pies tradicional',
                'precio' => 37000,
                'estado' => true,
                'porcentaje_pago_empleado' => 50.00,
            ],
            
            // Servicios Dama
            [
                'nombre' => 'Tradicional manos',
                'precio' => 19000,
                'estado' => true,
                'porcentaje_pago_empleado' => 50.00,
            ],
            [
                'nombre' => 'Pies tradicional',
                'precio' => 22000,
                'estado' => true,
                'porcentaje_pago_empleado' => 50.00,
            ],
            [
                'nombre' => 'Manos solo semi',
                'precio' => 47000,
                'estado' => true,
                'porcentaje_pago_empleado' => 50.00,
            ],
            [
                'nombre' => 'Pies solo semi',
                'precio' => 42000,
                'estado' => true,
                'porcentaje_pago_empleado' => 50.00,
            ],
            [
                'nombre' => 'Dama completa tradicional',
                'precio' => 38000,
                'estado' => true,
                'porcentaje_pago_empleado' => 50.00,
            ],
            [
                'nombre' => 'Dama manos semi pies tradicional',
                'precio' => 69000,
                'estado' => true,
                'porcentaje_pago_empleado' => 50.00,
            ],
            [
                'nombre' => 'Pies semi manos tradicional',
                'precio' => 61000,
                'estado' => true,
                'porcentaje_pago_empleado' => 50.00,
            ],
            [
                'nombre' => 'Dipling',
                'precio' => 57000,
                'estado' => true,
                'porcentaje_pago_empleado' => 50.00,
            ],
            [
                'nombre' => 'Diplin y tradicional',
                'precio' => 79000,
                'estado' => true,
                'porcentaje_pago_empleado' => 50.00,
            ],
            [
                'nombre' => 'Completo semi',
                'precio' => 82000,
                'estado' => true,
                'porcentaje_pago_empleado' => 50.00,
            ],
            [
                'nombre' => 'Nivelación con base ruber',
                'precio' => 67000,
                'estado' => true,
                'porcentaje_pago_empleado' => 50.00,
            ],
            [
                'nombre' => 'Forrado polygel',
                'precio' => 70000,
                'estado' => true,
                'porcentaje_pago_empleado' => 50.00,
            ],
            [
                'nombre' => 'Forrado acrílico',
                'precio' => 87000,
                'estado' => true,
                'porcentaje_pago_empleado' => 50.00,
            ],
            [
                'nombre' => 'Alargamiento polygel',
                'precio' => 90000,
                'estado' => true,
                'porcentaje_pago_empleado' => 50.00,
            ],
            [
                'nombre' => 'Acrílico con tips',
                'precio' => 105000,
                'estado' => true,
                'porcentaje_pago_empleado' => 50.00,
            ],
            [
                'nombre' => 'Acrílico esculpidas',
                'precio' => 120000,
                'estado' => true,
                'porcentaje_pago_empleado' => 50.00,
            ],
            [
                'nombre' => 'Sof gel',
                'precio' => 85000,
                'estado' => true,
                'porcentaje_pago_empleado' => 50.00,
            ],
            
            // Servicios individuales
            [
                'nombre' => 'Una sola uña acrílico',
                'precio' => 10500,
                'estado' => true,
                'porcentaje_pago_empleado' => 50.00,
            ],
            [
                'nombre' => 'Una sola uña poli',
                'precio' => 9000,
                'estado' => true,
                'porcentaje_pago_empleado' => 50.00,
            ],
            [
                'nombre' => 'Una sola sofgel',
                'precio' => 8500,
                'estado' => true,
                'porcentaje_pago_empleado' => 50.00,
            ],
            [
                'nombre' => 'Una sola esculpidas',
                'precio' => 12000,
                'estado' => true,
                'porcentaje_pago_empleado' => 50.00,
            ],
            [
                'nombre' => 'Uñas grandes del pie',
                'precio' => 10000,
                'estado' => true,
                'porcentaje_pago_empleado' => 50.00,
            ],
            
            // Servicios de retiro
            [
                'nombre' => 'Retiro de cualquier sistema - Complejo',
                'precio' => 150000,
                'estado' => true,
                'porcentaje_pago_empleado' => 50.00,
            ],
            [
                'nombre' => 'Retiro de cualquier sistema - Simple',
                'precio' => 20000,
                'estado' => true,
                'porcentaje_pago_empleado' => 50.00,
            ],
            [
                'nombre' => 'Retiro semi',
                'precio' => 10000,
                'estado' => true,
                'porcentaje_pago_empleado' => 50.00,
            ],
        ];

        // Crear servicios para la entidad 6
        foreach ($servicios as $servicio) {
            $servicio['entidad_id'] = $entidad->id;
            
            Servicios::firstOrCreate(
                ['nombre' => $servicio['nombre'], 'entidad_id' => $entidad->id],
                $servicio
            );
        }

        $this->command->info('Servicios de manicura y pedicura sembrados exitosamente para la entidad: ' . $entidad->nombre);
        $this->command->info('Total de servicios creados: ' . count($servicios));
    }
}
