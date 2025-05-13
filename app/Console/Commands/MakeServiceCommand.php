<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class MakeServiceCommand extends Command
{
    protected $signature = 'make:service {name : Full PSR-4 class path starting from App\\}';

    protected $description = 'Create a new service class at the exact location specified by the path';

    public function handle()
    {
        $name = $this->argument('name');

        // Eliminar la raíz "App" del path para convertirlo a ruta real
        $cleanPath = preg_replace('/^App[\/\\\\]?/', '', $name);

        // Ruta del archivo real dentro de app/
        $filePath = app_path(str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $cleanPath) . '.php');

        if (file_exists($filePath)) {
            $this->error("💥 El archivo ya existe en: {$filePath}");
            return Command::FAILURE;
        }

        $this->makeDirectory($filePath);
        file_put_contents($filePath, $this->buildClass($name));

        $this->info("✅ Servicio creado: {$filePath}");
        return Command::SUCCESS;
    }

    protected function makeDirectory(string $path): void
    {
        $dir = dirname($path);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
    }

    protected function buildClass(string $name): string
    {
        $class = class_basename($name);
        $namespace = str_replace('/', '\\', dirname($name));

        return <<<PHP
<?php

namespace {$namespace};

class {$class}
{
    // Desde Aquí se inicia la logica @samM
}

PHP;
    }
} 