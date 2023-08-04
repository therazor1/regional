<?php namespace Models;

use Inc\Rsp;
use Inc\Util;

class Reniec extends Rsp
{
    const URL_BASE = "https://dniruc.apisperu.com/api/v1/";
    const TOKEN = "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJlbWFpbCI6ImZyYW5jb0Bmb2N1c2l0LnBlIn0.V8VTyP9-u1YdKR2Kmxmuwaw0tuulIaNyunnz4qn3SDM";
    const TIPO_DNI = '1';
    const TIPO_RUC = '2';

    public $tipo = '';
    public $documento = '';
    public $nombre = '';

    public $direccion = '';
    public $departamento = '';
    public $provincia = '';
    public $distrito = '';
    public $ubigeo = '';

    public $apellido_paterno = '';
    public $apellido_materno = '';
    public $codigo_verificacion = '';

    public function __construct($documento)
    {
        $this->documento = $documento;
    }

    /**
     * @return State[]
     */
    static function tipos()
    {
        return [
            self::TIPO_DNI => new State('#0abb87', 'DNI'),
            self::TIPO_RUC => new State('#ffb822', 'RUC'),
        ];
    }

    /**
     * @return array
     */
    static function tiposArr()
    {
        return Util::osArr(self::tipos());
    }

    /**
     * @param $tipo
     * @return State
     */
    static function tipoObj($tipo)
    {
        $map = self::tipos();
        return isset($map[$tipo]) ? $map[$tipo] : new State();
    }

    /**
     * @return string|null
     */
    public function makeUrl()
    {
        $url = Reniec::URL_BASE;

        switch (strlen($this->documento)) {
            case 8:
                $this->tipo = Reniec::TIPO_DNI;
                $tipoObj = Reniec::tipoObj($this->tipo);
                break;
            case 11:
                $this->tipo = Reniec::TIPO_RUC;
                $tipoObj = Reniec::tipoObj($this->tipo);
                break;
            default:
                return null;
        }

        if (!is_numeric($this->documento)) return null;

        $url .= strtolower($tipoObj->name);
        $url .= '/' . $this->documento;

        return $url;
    }

    /**
     * @return $this|Rsp
     */
    public function datos()
    {
        $url = $this->makeUrl();

        if (!$url) {
            $this->msg = 'Datos incorrectos, recuerda que un RUC cuenta con 11 dígitos y un DNI con 8';
            return $this;
        }

        $response = Util::callAPI($url, ['token' => Reniec::TOKEN]);

        $succes = isset($response->success) ? $response->success : true;

        if ($succes) {
            $this->ok = true;
            if ($this->tipo == Reniec::TIPO_DNI) {
                $this->documento = $response->dni;
                $this->nombre = $response->nombres;
                $this->apellido_paterno = $response->apellidoPaterno;
                $this->apellido_materno = $response->apellidoMaterno;
                $this->codigo_verificacion = $response->codVerifica;
            } else {
                $this->documento = $response->ruc;
                $this->nombre = $response->razonSocial;
                $this->direccion = $response->direccion;
                $this->departamento = $response->departamento;
                $this->provincia = $response->provincia;
                $this->distrito = $response->distrito;
                $this->ubigeo = $response->ubigeo;
            }
        } else {
            $this->msg = $response->message;
        }

        return $this;
    }
}