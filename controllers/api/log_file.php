<?php namespace Controllers\api;

use Inc\Pic;

class log_file extends _controller
{
    public static function create($rows = [], $file_name = 'log', $json = true)
    {
        $path = _PATH_ . '/uploads/logs/' . $file_name . '.txt';

        $file = fopen($path, "w");

        if ($json) {
            fwrite($file, json_encode($rows) . PHP_EOL);
        } else if (is_array($rows)) {
            foreach ($rows as $key => $value) {
                $nv = ($key . ') ' . $value);
                fwrite($file, $nv);
            }
        } else {
            fwrite($file, $rows . PHP_EOL);
        }

        fclose($file);
    }

    public static function read($file_name = 'log')
    {
        header('Content-Type: application/json; charset=utf-8');
        $path = '/logs/' . $file_name . '.txt';
        return file_get_contents(Pic::url($path));
    }

    public static function createFolder()
    {
        $folder_name = 'logs';
        $path = _PATH_ . '/uploads/' . $folder_name;

        (!file_exists($path)) && mkdir($path, 0777, true);

        return $path;
    }

}



