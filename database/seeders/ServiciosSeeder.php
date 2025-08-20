<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Http\Modules\servicios\models\Servicios;
use App\Http\Modules\Entidades\models\Entidades;

class ServiciosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener todas las entidades disponibles
        $entidades = Entidades::all();
        
        if ($entidades->isEmpty()) {
            $this->command->warn('No hay entidades disponibles. Ejecute EntidadesSeeder primero.');
            return;
        }
        
        $servicios = [
            [
                'nombre' => 'Depilacion con Cera',
                'precio' => 16000,
                'estado' => true,
                'porcentaje_pago_empleado' => 40.00,
            ],
            [
                'nombre' => 'Diseño de Cejas',
                'precio' => 18000,
                'estado' => true,
                'porcentaje_pago_empleado' => 40.00,
            ],
            [
                'nombre' => 'Depilacion y Sombreado',
                'precio' => 30000,
                'estado' => true,
                'porcentaje_pago_empleado' => 40.00,
            ],
            [
                'nombre' => 'Sombreado de Cejas',
                'precio' => 20000,
                'estado' => true,
                'porcentaje_pago_empleado' => 40.00,
            ],
            [
                'nombre' => 'Laminado de Cejas',
                'precio' => 80000,
                'estado' => true,
                'porcentaje_pago_empleado' => 40.00,
            ],
            [
                'nombre' => 'Laminado con Sombreado',
                'precio' => 90000,
                'estado' => true,
                'porcentaje_pago_empleado' => 40.00,
            ],
            [
                'nombre' => 'Pestañas por Punto',
                'precio' => 32000,
                'estado' => true,
                'porcentaje_pago_empleado' => 40.00,
            ],
            [
                'nombre' => 'Lifting Tradicional',
                'precio' => 90000,
                'estado' => true,
                'porcentaje_pago_empleado' => 40.00,
            ],
            [
                'nombre' => 'Lifting Coreano',
                'precio' => 120000,
                'estado' => true,
                'porcentaje_pago_empleado' => 60.00,
            ],
            [
                'nombre' => 'Pestañas Pelo a Pelo Clasicas',
                'precio' => 80000,
                'estado' => true,
                'porcentaje_pago_empleado' => 50.00,
            ],
            [
                'nombre' => 'Pestañas Pelo a Pelo Pestañina',
                'precio' => 90000,
                'estado' => true,
                'porcentaje_pago_empleado' => 50.00,
            ],
            [
                'nombre' => 'Pestañas Pelo a Pelo Tecnologicas',
                'precio' => 100000,
                'estado' => true,
                'porcentaje_pago_empleado' => 50.00,
            ],
            [
                'nombre' => 'Bozo',
                'precio' => 7000,
                'estado' => true,
                'porcentaje_pago_empleado' => 40.00,
            ],
            [
                'nombre' => 'Patillas',
                'precio' => 10000,
                'estado' => true,
                'porcentaje_pago_empleado' => 40.00,
            ],
            [
                'nombre' => 'Menton',
                'precio' => 6000,
                'estado' => true,
                'porcentaje_pago_empleado' => 40.00,
            ],
            [
                'nombre' => 'Nariz Completa',
                'precio' => 12000,
                'estado' => true,
                'porcentaje_pago_empleado' => 40.00,
            ],
            [
                'nombre' => 'Borde de Nariz',
                'precio' => 9000,
                'estado' => true,
                'porcentaje_pago_empleado' => 40.00,
            ],
            [
                'nombre' => 'Rostro Completo Sin Cejas',
                'precio' => 35000,
                'estado' => true,
                'porcentaje_pago_empleado' => 40.00,
            ],
            [
                'nombre' => 'Cachetes',
                'precio' => 7000,
                'estado' => true,
                'porcentaje_pago_empleado' => 40.00,
            ],
            [
                'nombre' => 'Frente',
                'precio' => 6000,
                'estado' => true,
                'porcentaje_pago_empleado' => 40.00,
            ],
            [
                'nombre' => 'Bikini Completo',
                'precio' => 60000,
                'estado' => true,
                'porcentaje_pago_empleado' => 40.00,
            ],
            [
                'nombre' => 'Borde de Bikini',
                'precio' => 35000,
                'estado' => true,
                'porcentaje_pago_empleado' => 40.00,
            ],
            [
                'nombre' => 'Pierna Completa',
                'precio' => 60000,
                'estado' => true,
                'porcentaje_pago_empleado' => 40.00,
            ],
            [
                'nombre' => 'Media Pierna',
                'precio' => 40000,
                'estado' => true,
                'porcentaje_pago_empleado' => 40.00,
            ],
            [
                'nombre' => 'Axilas',
                'precio' => 18000,
                'estado' => true,
                'porcentaje_pago_empleado' => 40.00,
            ],
            [
                'nombre' => 'Abdomen',
                'precio' => 10000,
                'estado' => true,
                'porcentaje_pago_empleado' => 40.00,
            ],
            [
                'nombre' => 'Gluteos',
                'precio' => 15000,
                'estado' => true,
                'porcentaje_pago_empleado' => 40.00,
            ],
            [
                'nombre' => 'Oidos',
                'precio' => 10000,
                'estado' => true,
                'porcentaje_pago_empleado' => 40.00,
            ],
            [
                'nombre' => 'Limpieza Facial',
                'precio' => 100000,
                'estado' => true,
                'porcentaje_pago_empleado' => 60.00,
            ],
            [
                'nombre' => 'Limpieza con Laser',
                'precio' => 180000,
                'estado' => true,
                'porcentaje_pago_empleado' => 65.00,
            ],
            [
                'nombre' => 'Hidratacion Facial',
                'precio' => 80000,
                'estado' => true,
                'porcentaje_pago_empleado' => 60.00,
            ],
        ];

        // Asignar servicios a entidades de forma rotativa
        $entidadIndex = 0;
        foreach ($servicios as $servicio) {
            $entidadId = $entidades[$entidadIndex]->id;
            $servicio['entidad_id'] = $entidadId;
            
            Servicios::firstOrCreate(
                ['nombre' => $servicio['nombre'], 'entidad_id' => $entidadId],
                $servicio
            );
            
            // Rotar entre entidades
            $entidadIndex = ($entidadIndex + 1) % $entidades->count();
        }

        $this->command->info('Servicios sembrados exitosamente en ' . $entidades->count() . ' entidades.');
    }
} 