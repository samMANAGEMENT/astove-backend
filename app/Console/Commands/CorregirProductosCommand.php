<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Modules\Productos\Models\Productos;

class CorregirProductosCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'productos:corregir-precios';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Corregir precios de productos que tienen precio menor al costo';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Corrigiendo precios de productos...');

        $productos = Productos::all();
        $corregidos = 0;

        foreach ($productos as $producto) {
            $gananciaActual = $producto->precio_unitario - $producto->costo_unitario;
            
            if ($gananciaActual < 0) {
                $this->warn("Producto: {$producto->nombre}");
                $this->warn("  Precio actual: $" . number_format($producto->precio_unitario));
                $this->warn("  Costo actual: $" . number_format($producto->costo_unitario));
                $this->warn("  Ganancia actual: $" . number_format($gananciaActual));
                
                // Calcular nuevo precio con 35% de margen sobre el costo
                $nuevoPrecio = $producto->costo_unitario * 1.35;
                
                $producto->precio_unitario = round($nuevoPrecio);
                $producto->save();
                
                $nuevaGanancia = $producto->precio_unitario - $producto->costo_unitario;
                
                $this->info("  Nuevo precio: $" . number_format($producto->precio_unitario));
                $this->info("  Nueva ganancia: $" . number_format($nuevaGanancia));
                $this->info("  Margen: " . round(($nuevaGanancia / $producto->costo_unitario) * 100, 1) . "%");
                $this->info("");
                
                $corregidos++;
            }
        }

        if ($corregidos > 0) {
            $this->info("✅ Se corrigieron {$corregidos} productos.");
        } else {
            $this->info("✅ Todos los productos ya tienen precios correctos.");
        }

        return 0;
    }
}
