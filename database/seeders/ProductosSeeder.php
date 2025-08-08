<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Http\Modules\Productos\Models\Productos;
use App\Http\Modules\Categorias\Models\Categorias;

class ProductosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener o crear categoría por defecto
        $categoria = Categorias::firstOrCreate(
            ['nombre' => 'Productos de Belleza']
        );

        $productos = [
            [
                'nombre' => 'Espectañas',
                'categoria_id' => $categoria->id,
                'precio_unitario' => 33300,
                'costo_unitario' => 44900,
                'stock' => 12
            ],
            [
                'nombre' => 'Bifasico 150Ml',
                'categoria_id' => $categoria->id,
                'precio_unitario' => 31800,
                'costo_unitario' => 42900,
                'stock' => 12
            ],
            [
                'nombre' => 'Mini Bifasico',
                'categoria_id' => $categoria->id,
                'precio_unitario' => 12980,
                'costo_unitario' => 17500,
                'stock' => 12
            ],
            [
                'nombre' => 'Repuestos',
                'categoria_id' => $categoria->id,
                'precio_unitario' => 8150,
                'costo_unitario' => 11000,
                'stock' => 12
            ],
            [
                'nombre' => 'Depilaro',
                'categoria_id' => $categoria->id,
                'precio_unitario' => 13705,
                'costo_unitario' => 18500,
                'stock' => 12
            ],
            [
                'nombre' => 'Pomo',
                'categoria_id' => $categoria->id,
                'precio_unitario' => 13705,
                'costo_unitario' => 18500,
                'stock' => 12
            ]
        ];

        foreach ($productos as $producto) {
            Productos::firstOrCreate(
                ['nombre' => $producto['nombre']],
                $producto
            );
        }
    }
}
