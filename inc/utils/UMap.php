<?php namespace Inc\utils;

use Inc\Util;

class UMap
{

    public static function directions($stops)
    {

        $rsp = [
            'distance'    => 0, // kilometros
            'duration'    => 0, // minutos
            'polyline'    => '',
            'bounds'      => null, // direccion de destino
            'org_address' => '', // direccion de origen
            'dst_address' => '', // direccion de destino
        ];

        $firstIndex = 0;
        $lastIndex = count($stops) - 1;
        $waypoints = [];

        $org = ['lat' => $stops[$firstIndex]['lat'] + 0, 'lng' => $stops[$firstIndex]['lng'] + 0];
        $dst = ['lat' => $stops[$lastIndex]['lat'] + 0, 'lng' => $stops[$lastIndex]['lng'] + 0];

        foreach ($stops as $i => $stop) {
            if ($i != $firstIndex && $i != $lastIndex) {
                $waypoints[] = $stops[$i]['lat'] . ',' . $stops[$i]['lng'];
            }
        }

        $params = [
            'key'         => stg('key_maps'),
            'origin'      => $org['lat'] . ',' . $org['lng'],
            'waypoints'   => implode('|', $waypoints),
            'destination' => $dst['lat'] . ',' . $dst['lng'],
        ];

        $response = Util::callAPI('https://maps.googleapis.com/maps/api/directions/json', $params);

        if (@$response->status != 'OK') {
        } else if (empty($response->routes)) {
        } else {
            $route = $response->routes[0];
            $distance = 0;
            $duration = 0;

            foreach ($route->legs as $leg) {
                $distance += $leg->distance->value / 1000;
                $duration += $leg->duration->value / 60;
            }

            return [
                'distance'    => round($distance, 1),
                'duration'    => round($duration),
                'polyline'    => $route->overview_polyline->points,
                'bounds'      => $route->bounds,
                'org_address' => $route->legs[0]->start_address,
                'dst_address' => $route->legs[count($route->legs) - 1]->end_address,
            ];
        }

        return $rsp;
    }

    /**
     * Saber si un Punto se encuentra dentro de un Poligono
     * @param $point
     * @param $p
     * @return bool
     */
    public static function pointInPolygon($point, $p)
    {
        if ($p[0] != $p[count($p) - 1]) {
            $p[count($p)] = $p[0];
        }

        $j = 0;
        $oddNodes = false;
        $x = $point[1];
        $y = $point[0];
        $n = count($p);
        for ($i = 0; $i < $n; $i++) {
            $j++;
            if ($j == $n) {
                $j = 0;
            }
            if ((($p[$i][0] < $y) && ($p[$j][0] >= $y)) || (($p[$j][0] < $y) && ($p[$i][0] >= $y))) {
                if ($p[$i][1] + ($y - $p[$i][0]) / ($p[$j][0] - $p[$i][0]) * ($p[$j][1] - $p[$i][1]) < $x) {
                    $oddNodes = !$oddNodes;
                }
            }
        }

        return $oddNodes;
    }

    public static function polygonFromMySQL($str)
    {
        $str = str_replace('POLYGON((', '', $str);
        $str = str_replace('))', '', $str);
        $arr = explod($str);
        $items = [];
        foreach ($arr as $st) {
            $ar = explod($st, ' ');
            $items[] = [$ar[0] + 0, $ar[0] + 0];
        }
        array_pop($items); # eliminamos el ultimo
        return $items;
    }

    static function address($lat, $lng)
    {
        $result = Util::callAPI('https://maps.googleapis.com/maps/api/geocode/json', [
            'key'    => stg('key_maps'),
            'latlng' => $lat . ',' . $lng,
        ]);

        if (@$result->status == 'OK') {
            $results = $result->results;
            if (count($results) > 0) {
                return $results[0]->formatted_address;
            }
        }

        return '';
    }
}