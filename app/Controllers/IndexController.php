<?php

namespace App\Controllers;

use App\Models\ApiModel;

class IndexController extends BaseController
{
    public function index()
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        $data = array();
        $codigos = ['uf', 'ivp', 'dolar', 'dolar_intercambio', 'euro', 'ipc', 'utm', 'imacec', 'tpm', 'libra_cobre', 'tasa_desempleo', 'bitcoin'];

        $data['title'] = 'Indicadores';
        $data['data'] = ['codigos' => $codigos];
        $data['token'] = ApiController::generateToken();

        return view('layout', $data);
    }
}
