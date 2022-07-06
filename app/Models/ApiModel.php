<?php

namespace App\Models;

use CodeIgniter\Model;

class ApiModel extends Model
{
    protected $table = 'indicators';
    protected $primaryKey = 'id_indicator';
    protected $allowedFields = ['codigo', 'nombre', 'unidad_medida', 'fecha', 'valor'];


    // public function get($id_indicator = null)
    // {
    //     if ($id_indicator === null) {
    //         return $this->findAll();
    //     }
    //     return $this->asArray()->where(['id_indicator' => $id_indicator])->first();
    // }


}