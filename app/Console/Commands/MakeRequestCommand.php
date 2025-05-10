<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class MakeRequestCommand extends Command
{
    protected $signature = 'make:request {name}';
    protected $description = 'Create a new form request class';

    public function handle()
    {
        try {
            $name = $this->argument('name');
            $this->info("Intentando crear request: {$name}");

            // Construir la ruta completa
            $path = app_path(str_replace('\\', '/', $name) . '.php');
            $this->info("Ruta del archivo: {$path}");

            // Verificar si el archivo ya existe
            if (File::exists($path)) {
                $this->error("El archivo ya existe en: {$path}");
                return 1;
            }

            // Crear el directorio si no existe
            $directory = dirname($path);
            if (!File::exists($directory)) {
                $this->info("Creando directorio: {$directory}");
                File::makeDirectory($directory, 0755, true);
            }

            // Generar el contenido del archivo
            $content = $this->buildClass($name);
            
            // Guardar el archivo
            File::put($path, $content);
            
            $this->info("✅ Request creado exitosamente en: {$path}");
            return 0;
        } catch (\Exception $e) {
            $this->error("Error al crear el request: " . $e->getMessage());
            $this->error("Stack trace: " . $e->getTraceAsString());
            return 1;
        }
    }

    protected function buildClass($name)
    {
        $class = class_basename($name);
        $namespace = str_replace('/', '\\', dirname($name));

        return <<<PHP
<?php

namespace {$namespace};

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class {$class} extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            //
        ];
    }

    protected function failedValidation(Validator \$validator)
    {
        throw new HttpResponseException(response()->json(\$validator->errors(), 422));
    }
}

PHP;
    }
} 