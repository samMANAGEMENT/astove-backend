<?php
 
namespace App\modules\empleados\service;

use App\modules\empleados\models\empleados;

class empleadosService
{
    public function crearEmpleado($data)
    {
        return empleados::create($data);
    }

    public function listarEmpleados()
    {
        return empleados::with('entidad')->get();
    }
}

