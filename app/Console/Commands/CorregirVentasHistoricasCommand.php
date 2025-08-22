<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Modules\Ventas\Models\Ventas;
use App\Http\Modules\Productos\Models\Productos;
use Illuminate\Support\Facades\DB;

class CorregirVentasHistoricasCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ventas:corregir-historicas {--dry-run : Ejecutar en modo simulación sin hacer cambios}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Corregir ventas históricas que se realizaron con precios incorrectos de productos';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $isDryRun = $this->option('dry-run');
        
        if ($isDryRun) {
            $this->info('🔍 Ejecutando en modo simulación (no se harán cambios)...');
        } else {
            $this->info('🔧 Corrigiendo ventas históricas con precios incorrectos...');
        }

        $this->info('');

        // Obtener todas las ventas con sus productos
        $ventas = Ventas::with(['productos', 'empleado'])->get();
        
        $ventasCorregidas = 0;
        $totalRecuperado = 0;
        $totalGananciaRecuperada = 0;
        $errores = [];

        $progressBar = $this->output->createProgressBar($ventas->count());
        $progressBar->start();

        foreach ($ventas as $venta) {
            try {
                $correccionVenta = $this->analizarYCorregirVenta($venta, $isDryRun);
                
                if ($correccionVenta['necesitaCorreccion']) {
                    $ventasCorregidas++;
                    $totalRecuperado += $correccionVenta['diferenciaTotal'];
                    $totalGananciaRecuperada += $correccionVenta['diferenciaGanancia'];
                    
                    if (!$isDryRun) {
                        $this->mostrarDetalleCorreccion($venta, $correccionVenta);
                    }
                }
            } catch (\Exception $e) {
                $errores[] = [
                    'venta_id' => $venta->id,
                    'error' => $e->getMessage()
                ];
            }
            
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->info('');

        // Mostrar resumen
        $this->mostrarResumen($ventasCorregidas, $totalRecuperado, $totalGananciaRecuperada, $errores, $isDryRun);

        return 0;
    }

    private function analizarYCorregirVenta($venta, $isDryRun)
    {
        $necesitaCorreccion = false;
        $diferenciaTotal = 0;
        $diferenciaGanancia = 0;
        $correccionesProductos = [];

        // Analizar cada producto en la venta
        foreach ($venta->productos as $producto) {
            $cantidad = $producto->pivot->cantidad;
            $subtotalActual = $producto->pivot->subtotal;
            
            // Calcular subtotal correcto con el precio actual del producto
            $subtotalCorrecto = $producto->precio_unitario * $cantidad;
            $gananciaUnitaria = $producto->precio_unitario - $producto->costo_unitario;
            $gananciaCorrecta = $gananciaUnitaria * $cantidad;
            
            // Verificar si necesita corrección
            if (abs($subtotalActual - $subtotalCorrecto) > 0.01) {
                $necesitaCorreccion = true;
                $diferenciaSubtotal = $subtotalCorrecto - $subtotalActual;
                $diferenciaTotal += $diferenciaSubtotal;
                $diferenciaGanancia += $gananciaCorrecta - ($producto->pivot->subtotal - ($producto->costo_unitario * $cantidad));
                
                $correccionesProductos[] = [
                    'producto_id' => $producto->id,
                    'producto_nombre' => $producto->nombre,
                    'cantidad' => $cantidad,
                    'subtotal_actual' => $subtotalActual,
                    'subtotal_correcto' => $subtotalCorrecto,
                    'diferencia' => $diferenciaSubtotal,
                    'ganancia_correcta' => $gananciaCorrecta
                ];
            }
        }

        // Si necesita corrección y no es dry-run, aplicar cambios
        if ($necesitaCorreccion && !$isDryRun) {
            $this->aplicarCorrecciones($venta, $correccionesProductos, $diferenciaTotal, $diferenciaGanancia);
        }

        return [
            'necesitaCorreccion' => $necesitaCorreccion,
            'diferenciaTotal' => $diferenciaTotal,
            'diferenciaGanancia' => $diferenciaGanancia,
            'correccionesProductos' => $correccionesProductos
        ];
    }

    private function aplicarCorrecciones($venta, $correccionesProductos, $diferenciaTotal, $diferenciaGanancia)
    {
        DB::transaction(function () use ($venta, $correccionesProductos, $diferenciaTotal, $diferenciaGanancia) {
            // Actualizar subtotales en ventas_productos
            foreach ($correccionesProductos as $correccion) {
                DB::table('ventas_productos')
                    ->where('venta_id', $venta->id)
                    ->where('producto_id', $correccion['producto_id'])
                    ->update([
                        'subtotal' => $correccion['subtotal_correcto']
                    ]);
            }

            // Actualizar total y ganancia_total en ventas
            $nuevoTotal = $venta->total + $diferenciaTotal;
            $nuevaGanancia = $venta->ganancia_total + $diferenciaGanancia;
            
            $venta->update([
                'total' => $nuevoTotal,
                'ganancia_total' => $nuevaGanancia
            ]);
        });
    }

    private function mostrarDetalleCorreccion($venta, $correccion)
    {
        $this->info("📊 Venta #{$venta->id} - Empleado: {$venta->empleado->nombre} {$venta->empleado->apellido}");
        $this->info("   Fecha: " . $venta->fecha->format('d/m/Y H:i'));
        $this->info("   Total anterior: $" . number_format($venta->total, 2));
        $this->info("   Total corregido: $" . number_format($venta->total + $correccion['diferenciaTotal'], 2));
        $this->info("   Diferencia: $" . number_format($correccion['diferenciaTotal'], 2));
        $this->info("   Ganancia recuperada: $" . number_format($correccion['diferenciaGanancia'], 2));
        
        foreach ($correccion['correccionesProductos'] as $prod) {
            $this->info("   - {$prod['producto_nombre']}: $" . number_format($prod['diferencia'], 2));
        }
        $this->info("");
    }

    private function mostrarResumen($ventasCorregidas, $totalRecuperado, $totalGananciaRecuperada, $errores, $isDryRun)
    {
        $this->info('📈 RESUMEN DE CORRECCIÓN');
        $this->info('========================');
        
        if ($isDryRun) {
            $this->info("🔍 MODO SIMULACIÓN - No se realizaron cambios");
        }
        
        $this->info("✅ Ventas corregidas: {$ventasCorregidas}");
        $this->info("💰 Total recuperado: $" . number_format($totalRecuperado, 2));
        $this->info("💵 Ganancia recuperada: $" . number_format($totalGananciaRecuperada, 2));
        
        if (count($errores) > 0) {
            $this->error("❌ Errores encontrados: " . count($errores));
            foreach ($errores as $error) {
                $this->error("   Venta #{$error['venta_id']}: {$error['error']}");
            }
        }
        
        if ($ventasCorregidas > 0) {
            $this->info("");
            $this->info("🎯 RECOMENDACIONES:");
            $this->info("1. Verifica que los totales en los reportes coincidan");
            $this->info("2. Revisa las estadísticas de ganancias");
            $this->info("3. Considera ejecutar 'php artisan productos:corregir-precios' si no lo has hecho");
        } else {
            $this->info("");
            $this->info("✅ No se encontraron ventas que necesiten corrección");
        }
    }
}
