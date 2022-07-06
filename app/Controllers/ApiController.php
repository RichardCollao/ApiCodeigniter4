<?php

namespace App\Controllers;

use Exception;

use ApiResponseNormalizedController;
use CodeIgniter\RESTful\ResourceController;

class ApiController extends ResourceController
{
    protected $modelName = 'App\Models\ApiModel';
    protected $format    = 'json';

    public function index()
    {
        $data = array();
        $data['route'] = "index";
        $data['errors'] = array();
        $data['data'] = $this->model->findAll();
        return $this->respond($data, 200, "ok");
    }

    public function filter()
    {
        $data = array();
        $data['route'] = __FUNCTION__;
        $data['errors'] = array();
        $codigo = $this->request->getPost('codigo');
        $fecha_inicial = $this->request->getPost('fecha_inicial');
        $fecha_final = $this->request->getPost('fecha_final');

        $filters = [];
        $values = [];
        $where = '';

        if ($codigo != 'all') {
            $filters[] = "codigo = ?";
            $values[] = $codigo;
        }
        if (!empty($fecha_inicial)) {
            $filters[] = "fecha >= ?";
            $values[] = $fecha_inicial;
        }
        if (!empty($fecha_final)) {
            $filters[] = " fecha <= ?";
            $values[] = $fecha_final;
        }
        if (!empty($filters)) {
            $where = ' WHERE ';
        }
        $sql = "SELECT * FROM indicators" . $where . implode(' AND ', $filters) . ';';
        $query = $this->model->query($sql, $values);
        $data['data'] = $query->getResult('array');
        return $this->respond($data, 200, "ok");
    }

    public function show($id = null)
    {
        $data = array();
        $data['route'] = __FUNCTION__;
        $data['errors'] = array();
        $data['data'] = $this->model->find($id);
        return $this->respond($data, 200, "ok");
    }

    public function create()
    {
        $data = array();
        $data['route'] = __FUNCTION__;
        $values = $this->request->getPost();
        $data['errors'] = $this->validateForm($values);
        if (empty($data['errors'])) {
            $data['id_indicator'] = $this->model->insert([
                'codigo' => $values['codigo'],
                'nombre' => $values['nombre'],
                'unidad_medida' => $values['unidad_medida'],
                'fecha' => $values['fecha'],
                'valor' => $values['valor']
            ]);
            return $this->respond($data, 200, "ok");
        }
        return $this->respond($data, 500, "error");
    }

    public function update($id = null)
    {
        $data = array();
        $data['route'] = __FUNCTION__;
        $values =  $this->request->getRawInput();
        $data['errors'] = $this->validateForm($values);

        $indicator = $this->model->find($id);
        if (empty($indicator)) {
            $errors[] = 'No se encontro ningun recurso con este Id';
        }

        if (empty($data['errors'])) {
            $this->model->update($id, [
                'codigo' => $values['codigo'],
                'nombre' => $values['nombre'],
                'unidad_medida' => $values['unidad_medida'],
                'fecha' => $values['fecha'],
                'valor' => $values['valor']
            ]);
            return $this->respond($data, 200, "ok");
        }
        return $this->respond($data, 500, "error");
    }

    public function delete($id = null)
    {
        $data = array();
        $data['route'] = __FUNCTION__;
        $data['errors'] = array();
        $this->model->delete($id);
        return $this->respond($data, 200, "ok");
    }

    public function modify()
    {
        $data = array();
        $data['route'] = __FUNCTION__;
        $data['errors'] = array();
        // $data['post'] = $this->request->getPost();
        $id_indicator = $this->request->getPost('id_indicator');
        // $codigo = $this->request->getPost('codigo');
        // $nombre = $this->request->getPost('nombre');
        // $unidad_medida = $this->request->getPost('unidad_medida');
        $valor = $this->request->getPost('valor');

        $sql = "UPDATE indicators SET valor = ? WHERE id_indicator = ?;";
        $query = $this->model->query($sql, [$valor, $id_indicator]);
        // $data['data'] = $this->model->find($id_indicator);
        return $this->respond($data, 200, "ok");
    }

    public function scraping()
    {
        $code = $this->request->getPost('code');
        $year = $this->request->getPost('year');
        $codigos = ['uf', 'ivp', 'dolar', 'dolar_intercambio', 'euro', 'ipc', 'utm', 'imacec', 'tpm', 'libra_cobre', 'tasa_desempleo', 'bitcoin'];

        // https://mindicador.cl/api - Entrega los últimos valores registrados de los principales indicadores
        // https://mindicador.cl/api/{tipo_indicador} - Entrega los valores del último mes del indicador consultado
        // https://mindicador.cl/api/{tipo_indicador}/{dd-mm-yyyy} - Entrega el valor del indicador consultado según la fecha especificada 
        // https://mindicador.cl/api/{tipo_indicador}/{yyyy} - Entrega los valores del indicador consultado según el año especificado
        $apiUrl = 'https://mindicador.cl/api/' . $code . '/' . $year;

        //Es necesario tener habilitada la directiva allow_url_fopen para usar file_get_contents
        if (ini_get('allow_url_fopen')) {
            $json = file_get_contents($apiUrl);
        } else {
            //De otra forma utilizamos cURL
            $curl = curl_init($apiUrl);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            $json = curl_exec($curl);
            curl_close($curl);
        }

        // $json = $this->j;
        $obj = json_decode($json);

        foreach ($obj->serie as $v) {
            $data = [
                'codigo' => $obj->codigo,
                'nombre' => $obj->nombre,
                'unidad_medida' => $obj->unidad_medida,
                'fecha' => substr($v->fecha, 0, 10),
                'valor' => $v->valor
            ];
            // TODO: se podria mejorar el rendimiento con REPLACE INTO, en vez de consultar si existe el recurso 
            $sql = "SELECT * FROM indicators WHERE codigo = ? AND fecha = ?";
            $query = $this->model->query($sql, [$data['codigo'], $data['fecha']]);
            if (empty($query->getResult('array'))) {
                $this->model->insert($data);
            }
        }

        $data = array();
        $data['route'] = "scraping";
        $data['errors'] = array();
        return $this->respond($data, 200, "ok");
    }



    private function validateForm($values)
    {
        // TODO: mejorar validaciones usando Services/validation
        $regexFecha = "/^[1-2]{1}[0-9]{3}\\-(0[1-9]{1}|1[0-2]{1})\\-([0-2]{1}[1-9]{1}|3[0-1]{1})$/";
        $errors = array();
        if (empty($values['codigo'])) {
            $errors[] = "El campo 'codigo' esta vacio";
        }
        if (empty($values['nombre'])) {
            $errors[] = "El campo 'nombre' esta vacio";
        }
        if (empty($values['unidad_medida'])) {
            $errors[] = "El campo 'unidad_medida' esta vacio";
        }
        if (!preg_match($regexFecha, $values['fecha'], $matchFecha)) {
            $errors[] = "El campo 'fecha' no es valido";
        }
        if (!is_numeric($values['valor'])) {
            $errors[] = "El campo 'valor' no es valido";
        }
        return $errors;
    }

    public static function generateToken()
    {
        $_SESSION['filesexplorer']['token'] = bin2hex(openssl_random_pseudo_bytes(32));
        return $_SESSION['filesexplorer']['token'];
    }

    public static function getToken()
    {
        return $_SESSION['filesexplorer']['token'];
    }

    private function checkToken($token)
    {
        if (empty($token) || $_SESSION['filesexplorer']['token'] !== $token) {
            return false;
        }
    }

    private function out($data)
    {
        header('Content-Type: application/json');
        echo json_encode($data);
    }


    // protected $j = '{"version":"1.7.0","autor":"mindicador.cl","codigo":"dolar","nombre":"Dólar observado","unidad_medida":"Pesos","serie":[{"fecha":"2022-07-04T04:00:00.000Z","valor":934.54},{"fecha":"2022-07-01T04:00:00.000Z","valor":932.08},{"fecha":"2022-06-30T04:00:00.000Z","valor":919.97},{"fecha":"2022-06-29T04:00:00.000Z","valor":905.32},{"fecha":"2022-06-28T04:00:00.000Z","valor":911.49},{"fecha":"2022-06-24T04:00:00.000Z","valor":898.8},{"fecha":"2022-06-23T04:00:00.000Z","valor":886.64},{"fecha":"2022-06-22T04:00:00.000Z","valor":880.82},{"fecha":"2022-06-20T04:00:00.000Z","valor":875.65},{"fecha":"2022-06-17T04:00:00.000Z","valor":869.62},{"fecha":"2022-06-16T04:00:00.000Z","valor":863.95},{"fecha":"2022-06-15T04:00:00.000Z","valor":866.79},{"fecha":"2022-06-14T04:00:00.000Z","valor":863.33},{"fecha":"2022-06-13T04:00:00.000Z","valor":838.61},{"fecha":"2022-06-10T04:00:00.000Z","valor":827.19},{"fecha":"2022-06-09T04:00:00.000Z","valor":821.66},{"fecha":"2022-06-08T04:00:00.000Z","valor":828.99},{"fecha":"2022-06-07T04:00:00.000Z","valor":817.99},{"fecha":"2022-06-06T04:00:00.000Z","valor":813.38},{"fecha":"2022-06-03T04:00:00.000Z","valor":815.56},{"fecha":"2022-06-02T04:00:00.000Z","valor":825.28},{"fecha":"2022-06-01T04:00:00.000Z","valor":824.35},{"fecha":"2022-05-31T04:00:00.000Z","valor":826.26},{"fecha":"2022-05-30T04:00:00.000Z","valor":822.25},{"fecha":"2022-05-27T04:00:00.000Z","valor":829.61},{"fecha":"2022-05-26T04:00:00.000Z","valor":835.43},{"fecha":"2022-05-25T04:00:00.000Z","valor":833.99},{"fecha":"2022-05-24T04:00:00.000Z","valor":829.59},{"fecha":"2022-05-23T04:00:00.000Z","valor":833.58},{"fecha":"2022-05-20T04:00:00.000Z","valor":842.38},{"fecha":"2022-05-19T04:00:00.000Z","valor":849.9},{"fecha":"2022-05-18T04:00:00.000Z","valor":850.78},{"fecha":"2022-05-17T04:00:00.000Z","valor":857.98},{"fecha":"2022-05-16T04:00:00.000Z","valor":861.04},{"fecha":"2022-05-13T04:00:00.000Z","valor":867.93},{"fecha":"2022-05-12T04:00:00.000Z","valor":861.76},{"fecha":"2022-05-11T04:00:00.000Z","valor":868.06},{"fecha":"2022-05-10T04:00:00.000Z","valor":867.87},{"fecha":"2022-05-09T04:00:00.000Z","valor":857.58},{"fecha":"2022-05-06T04:00:00.000Z","valor":863.1},{"fecha":"2022-05-05T04:00:00.000Z","valor":859.33},{"fecha":"2022-05-04T04:00:00.000Z","valor":856.74},{"fecha":"2022-05-03T04:00:00.000Z","valor":860.64},{"fecha":"2022-05-02T04:00:00.000Z","valor":850.78},{"fecha":"2022-04-29T04:00:00.000Z","valor":856.58},{"fecha":"2022-04-28T04:00:00.000Z","valor":847.44},{"fecha":"2022-04-27T04:00:00.000Z","valor":847.57},{"fecha":"2022-04-26T04:00:00.000Z","valor":850.85},{"fecha":"2022-04-25T04:00:00.000Z","valor":834.45},{"fecha":"2022-04-22T04:00:00.000Z","valor":817.85},{"fecha":"2022-04-21T04:00:00.000Z","valor":816.29},{"fecha":"2022-04-20T04:00:00.000Z","valor":818.18},{"fecha":"2022-04-19T04:00:00.000Z","valor":817.7},{"fecha":"2022-04-19T04:00:00.000Z","valor":817.7},{"fecha":"2022-04-18T04:00:00.000Z","valor":814.73},{"fecha":"2022-04-14T04:00:00.000Z","valor":804.78},{"fecha":"2022-04-13T04:00:00.000Z","valor":806.73},{"fecha":"2022-04-12T04:00:00.000Z","valor":818.53},{"fecha":"2022-04-11T04:00:00.000Z","valor":814.28},{"fecha":"2022-04-08T04:00:00.000Z","valor":807.88},{"fecha":"2022-04-07T04:00:00.000Z","valor":794.58},{"fecha":"2022-04-06T04:00:00.000Z","valor":783.28},{"fecha":"2022-04-05T04:00:00.000Z","valor":779.33},{"fecha":"2022-04-04T04:00:00.000Z","valor":783.45},{"fecha":"2022-04-01T03:00:00.000Z","valor":787.98},{"fecha":"2022-03-31T03:00:00.000Z","valor":787.16},{"fecha":"2022-03-30T03:00:00.000Z","valor":777.1},{"fecha":"2022-03-29T03:00:00.000Z","valor":778.62},{"fecha":"2022-03-28T03:00:00.000Z","valor":785.89},{"fecha":"2022-03-25T03:00:00.000Z","valor":789.87},{"fecha":"2022-03-24T03:00:00.000Z","valor":794.44},{"fecha":"2022-03-23T03:00:00.000Z","valor":793.22},{"fecha":"2022-03-22T03:00:00.000Z","valor":798.5},{"fecha":"2022-03-21T03:00:00.000Z","valor":802.23},{"fecha":"2022-03-18T03:00:00.000Z","valor":798.13},{"fecha":"2022-03-17T03:00:00.000Z","valor":803.55},{"fecha":"2022-03-16T03:00:00.000Z","valor":815.03},{"fecha":"2022-03-15T03:00:00.000Z","valor":808.41},{"fecha":"2022-03-14T03:00:00.000Z","valor":802.48},{"fecha":"2022-03-11T03:00:00.000Z","valor":806.03},{"fecha":"2022-03-10T03:00:00.000Z","valor":803.57},{"fecha":"2022-03-09T03:00:00.000Z","valor":812.03},{"fecha":"2022-03-08T03:00:00.000Z","valor":808.6},{"fecha":"2022-03-07T03:00:00.000Z","valor":806.89},{"fecha":"2022-03-04T03:00:00.000Z","valor":800.97},{"fecha":"2022-03-03T03:00:00.000Z","valor":807.31},{"fecha":"2022-03-02T03:00:00.000Z","valor":803.27},{"fecha":"2022-03-01T03:00:00.000Z","valor":798.01},{"fecha":"2022-02-28T03:00:00.000Z","valor":805.25},{"fecha":"2022-02-25T03:00:00.000Z","valor":807.26},{"fecha":"2022-02-24T03:00:00.000Z","valor":787.05},{"fecha":"2022-02-23T03:00:00.000Z","valor":796.25},{"fecha":"2022-02-22T03:00:00.000Z","valor":799.99},{"fecha":"2022-02-21T03:00:00.000Z","valor":793.91},{"fecha":"2022-02-18T03:00:00.000Z","valor":798.98},{"fecha":"2022-02-17T03:00:00.000Z","valor":799.55},{"fecha":"2022-02-16T03:00:00.000Z","valor":804.17},{"fecha":"2022-02-15T03:00:00.000Z","valor":812.24},{"fecha":"2022-02-14T03:00:00.000Z","valor":806.24},{"fecha":"2022-02-11T03:00:00.000Z","valor":804.2},{"fecha":"2022-02-10T03:00:00.000Z","valor":820.79},{"fecha":"2022-02-09T03:00:00.000Z","valor":824.33},{"fecha":"2022-02-08T03:00:00.000Z","valor":826.93},{"fecha":"2022-02-07T03:00:00.000Z","valor":825.58},{"fecha":"2022-02-04T03:00:00.000Z","valor":817.29},{"fecha":"2022-02-03T03:00:00.000Z","valor":805.95},{"fecha":"2022-02-02T03:00:00.000Z","valor":801.52},{"fecha":"2022-02-01T03:00:00.000Z","valor":803.88},{"fecha":"2022-01-31T03:00:00.000Z","valor":810.12},{"fecha":"2022-01-28T03:00:00.000Z","valor":799.15},{"fecha":"2022-01-27T03:00:00.000Z","valor":798.86},{"fecha":"2022-01-26T03:00:00.000Z","valor":806.06},{"fecha":"2022-01-25T03:00:00.000Z","valor":800.31},{"fecha":"2022-01-24T03:00:00.000Z","valor":799.23},{"fecha":"2022-01-21T03:00:00.000Z","valor":805.25},{"fecha":"2022-01-20T03:00:00.000Z","valor":813.46},{"fecha":"2022-01-19T03:00:00.000Z","valor":820.6},{"fecha":"2022-01-18T03:00:00.000Z","valor":822},{"fecha":"2022-01-17T03:00:00.000Z","valor":815.39},{"fecha":"2022-01-14T03:00:00.000Z","valor":821.87},{"fecha":"2022-01-13T03:00:00.000Z","valor":826.51},{"fecha":"2022-01-12T03:00:00.000Z","valor":832.6},{"fecha":"2022-01-11T03:00:00.000Z","valor":831.39},{"fecha":"2022-01-10T03:00:00.000Z","valor":831.59},{"fecha":"2022-01-07T03:00:00.000Z","valor":838.88},{"fecha":"2022-01-06T03:00:00.000Z","valor":841.63},{"fecha":"2022-01-05T03:00:00.000Z","valor":852.03},{"fecha":"2022-01-04T03:00:00.000Z","valor":851.43},{"fecha":"2022-01-03T03:00:00.000Z","valor":844.69}]}';
}
