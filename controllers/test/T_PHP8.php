<?php namespace Controllers\test;

use Closure;
use Inc\Rsp;

class T_PHP8 extends _controller
{

    public function argumentosNombrados()
    {
        return $this->_funcDemo('demo', estado: 5);
    }

    public function expresionesMatch()
    {
        $parametro = 'v11';
        $valor = match ($parametro) {
            'v10' => 'el parametro es V10',
            'v11' => 'esto es el 11',
        };
        return Rsp::ok()->set('valor', $valor);
    }

    public function operadorNullsafe()
    {
        $items = $this->_funcDemo('xx');

        foreach ($items as $item) {
            $item['n'];
        }

        $obj = null;
        return Rsp::ok()->set('atributo', $obj?->hijo?->atributo);
    }

    public function funcionesFlecha()
    {
        $productos = [
            ['nombre' => 'prod 1', 'precio' => 10],
            ['nombre' => 'prod 2', 'precio' => 52],
        ];
        $precios = array_map(fn($pr) => $pr['precio'], $productos);
        return Rsp::ok()
            ->set('productos', $productos)
            ->set('precios', $precios);
    }

    public function nullCoalescingAssignmentOperator()
    {
        $valor = null;
        $valor ??= 'es por def';
        return $valor;
    }

    public function operatorSpreadArrays()
    {
        $foo = [1, 2, 3];
        $bar = ['a', 'b', 'c'];
        $result = [...$foo, 'algo en medio', ...$bar];

        return Rsp::ok()
            ->set('foo', $foo)
            ->set('bar', $bar)
            ->set('result', $result);
    }


    private function _funcDemo(string $nombre, int $id_log = 0, int $estado = 0, Closure $callback = null)
    {
        return [
            'nombre' => $nombre,
            'id_log' => $id_log,
            'estado' => $estado,
        ];
    }

}