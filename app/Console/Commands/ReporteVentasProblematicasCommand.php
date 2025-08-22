<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Modules\Ventas\Models\Ventas;
use App\Http\Modules\Productos\Models\Productos;
use Illuminate\Support\Facades\DB;

class ReporteVentasProblematicasCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ventas:reporte-problematicas {--export-csv : Exportar a archivo CSV}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generar reporte de ventas que contienen productos con precios incorrectos';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('📋 Generando reporte de ventas problemáticas...');
        $this->info('');

        // Obtener todas las ventas con sus productos
        $ventas = Ventas::with(['productos', 'empleado'])->get();
        
        $ventasProblematicas = [];
        $totalPerdidaEstimada = 0;
        $totalGananciaPerdida = 0;

        $progressBar = $this->output->createProgressBar($ventas->count());
        $progressBar->start();

        foreach ($ventas as $venta) {
            $productosProblematicos = [];
            $perdidaVenta = 0;
            $gananciaPerdida = 0;

            foreach ($venta->productos as $producto) {
                $cantidad = $producto->pivot->cantidad;
                $subtotalActual = $producto->pivot->subtotal;
                $subtotalCorrecto = $producto->precio_unitario * $cantidad;
                
                // Verificar si el precio actual es menor al costo
                if ($producto->precio_unitario < $producto->costo_unitario) {
                    $gananciaUnitaria = $producto->precio_unitario - $producto->costo_unitario;
                    $gananciaCorrecta = $gananciaUnitaria * $cantidad;
                    
                    $productosProblematicos[] = [
                        'producto_id' => $producto->id,
                        'nombre' => $producto->nombre,
                        'cantidad' => $cantidad,
                        'precio_actual' => $producto->precio_unitario,
                        'costo_unitario' => $producto->costo_unitario,
                        'subtotal_actual' => $subtotalActual,
                        'subtotal_correcto' => $subtotalCorrecto,
                        'diferencia' => $subtotalCorrecto - $subtotalActual,
                        'ganancia_perdida' => $gananciaCorrecta - ($subtotalActual - ($producto->costo_unitario * $cantidad))
                    ];
                    
                    $perdidaVenta += $subtotalCorrecto - $subtotalActual;
                    $gananciaPerdida += $gananciaCorrecta - ($subtotalActual - ($producto->costo_unitario * $cantidad));
                }
            }

            if (!empty($productosProblematicos)) {
                $ventasProblematicas[] = [
                    'venta_id' => $venta->id,
                    'fecha' => $venta->fecha,
                    'empleado' => $venta->empleado->nombre . ' ' . $venta->empleado->apellido,
                    'total_actual' => $venta->total,
                    'ganancia_actual' => $venta->ganancia_total,
                    'productos_problematicos' => $productosProblematicos,
                    'perdida_total' => $perdidaVenta,
                    'ganancia_perdida' => $gananciaPerdida
                ];
                
                $totalPerdidaEstimada += $perdidaVenta;
                $totalGananciaPerdida += $gananciaPerdida;
            }
            
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->info('');

        // Mostrar resumen
        $this->mostrarResumen($ventasProblematicas, $totalPerdidaEstimada, $totalGananciaPerdida);

        // Mostrar detalles de cada venta problemática
        if (!empty($ventasProblematicas)) {
            $this->mostrarDetallesVentas($ventasProblematicas);
        }

        // Exportar a CSV si se solicita
        if ($this->option('export-csv')) {
            $this->exportarCSV($ventasProblematicas);
        }

        return 0;
    }

    private function mostrarResumen($ventasProblematicas, $totalPerdidaEstimada, $totalGananciaPerdida)
    {
        $this->info('📊 RESUMEN DEL REPORTE');
        $this->info('=====================');
        $this->info("🔍 Ventas analizadas: " . count($ventasProblematicas));
        $this->info("💰 Pérdida estimada total: $" . number_format($totalPerdidaEstimada, 2));
        $this->info("💵 Ganancia perdida total: $" . number_format($totalGananciaPerdida, 2));
        $this->info("");
        
        if (count($ventasProblematicas) > 0) {
            $this->warn("⚠️  Se encontraron ventas con productos de precios incorrectos");
            $this->info("💡 Ejecuta 'php artisan ventas:corregir-historicas --dry-run' para simular la corrección");
            $this->info("💡 Ejecuta 'php artisan ventas:corregir-historicas' para aplicar las correcciones");
        } else {
            $this->info("✅ No se encontraron ventas problemáticas");
        }
        $this->info("");
    }

    private function mostrarDetallesVentas($ventasProblematicas)
    {
        $this->info('📋 DETALLES DE VENTAS PROBLEMÁTICAS');
        $this->info('===================================');
        
        foreach ($ventasProblematicas as $venta) {
            $this->info("🛒 Venta #{$venta['venta_id']} - {$venta['empleado']}");
            $this->info("   📅 Fecha: " . $venta['fecha']->format('d/m/Y H:i'));
            $this->info("   💰 Total actual: $" . number_format($venta['total_actual'], 2));
            $this->info("   📈 Ganancia actual: $" . number_format($venta['ganancia_actual'], 2));
            $this->info("   ❌ Pérdida estimada: $" . number_format($venta['perdida_total'], 2));
            $this->info("   💸 Ganancia perdida: $" . number_format($venta['ganancia_perdida'], 2));
            
            foreach ($venta['productos_problematicos'] as $producto) {
                $this->info("   📦 {$producto['nombre']} (x{$producto['cantidad']})");
                $this->info("      Precio actual: $" . number_format($producto['precio_actual'], 2));
                $this->info("      Costo: $" . number_format($producto['costo_unitario'], 2));
                $this->info("      Diferencia: $" . number_format($producto['diferencia'], 2));
            }
            $this->info("");
        }
    }

    private function exportarCSV($ventasProblematicas)
    {
        $filename = 'ventas_problematicas_' . date('Y-m-d_H-i-s') . '.csv';
        $filepath = storage_path('app/' . $filename);
        
        $file = fopen($filepath, 'w');
        
        // Encabezados
        fputcsv($file, [
            'Venta ID',
            'Fecha',
            'Empleado',
            'Total Actual',
            'Ganancia Actual',
            'Producto ID',
            'Producto Nombre',
            'Cantidad',
            'Precio Actual',
            'Costo Unitario',
            'Subtotal Actual',
            'Subtotal Correcto',
            'Diferencia',
            'Ganancia Perdida'
        ]);

        // Datos
        foreach ($ventasProblematicas as $venta) {
            foreach ($venta['productos_problematicos'] as $producto) {
                fputcsv($file, [
                    $venta['venta_id'],
                    $venta['fecha']->format('Y-m-d H:i:s'),
                    $venta['empleado'],
                    $venta['total_actual'],
                    $venta['ganancia_actual'],
                    $producto['producto_id'],
                    $producto['nombre'],
                    $producto['cantidad'],
                    $producto['precio_actual'],
                    $producto['costo_unitario'],
                    $producto['subtotal_actual'],
                    $producto['subtotal_correcto'],
                    $producto['diferencia'],
                    $producto['ganancia_perdida']
                ]);
            }
        }
        
        fclose($file);
        
        $this->info("📄 Reporte exportado a: {$filepath}");
    }
}
