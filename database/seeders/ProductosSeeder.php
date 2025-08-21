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
                'precio_unitario' => 55000, // Precio de venta mayor al costo
                'costo_unitario' => 44900,
                'stock' => 12,
                'entidad_id' => 1
            ],
            [
                'nombre' => 'Bifasico 150Ml',
                'categoria_id' => $categoria->id,
                'precio_unitario' => 52000, // Precio de venta mayor al costo
                'costo_unitario' => 42900,
                'stock' => 12,
                'entidad_id' => 1
            ],
            [
                'nombre' => 'Mini Bifasico',
                'categoria_id' => $categoria->id,
                'precio_unitario' => 22000, // Precio de venta mayor al costo
                'costo_unitario' => 17500,
                'stock' => 12,
                'entidad_id' => 1
            ],
            [
                'nombre' => 'Repuestos',
                'categoria_id' => $categoria->id,
                'precio_unitario' => 15000, // Precio de venta mayor al costo
                'costo_unitario' => 11000,
                'stock' => 12,
                'entidad_id' => 1
            ],
            [
                'nombre' => 'Depilador',
                'categoria_id' => $categoria->id,
                'precio_unitario' => 25000, // Precio de venta mayor al costo
                'costo_unitario' => 18500,
                'stock' => 12,
                'entidad_id' => 1
            ],
            [
                'nombre' => 'Pomo',
                'categoria_id' => $categoria->id,
                'precio_unitario' => 25000, // Precio de venta mayor al costo
                'costo_unitario' => 18500,
                'stock' => 12,
                'entidad_id' => 1
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
