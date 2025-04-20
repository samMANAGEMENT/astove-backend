<?php

namespace App\http\modules\pagos\service;

use App\Http\Modules\pagos\models\pagos;

class pagosService
{
    public function crearPago($data){
        return pagos::create($data);
    }

    public function listarPago(){
        return pagos::get();
    }
}
