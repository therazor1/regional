<?php namespace Controllers\admin;

use DOMDocument;
use Inc\Req;
use Inc\Rsp;

class legal extends _controller
{
    protected $no_edit = ['index', 'item', 'exportar', 'form'];

    public function form()
    {
        return Rsp::ok()
            ->set('item', [
                'legal' => $this->getLegalByName('/legal.html'),
            ]);
    }

    function getLegalByName($name)
    {
        $file_path = _PATH_ . $name;
        if (!file_exists($file_path)) {
            return '';

        } else {
            $domDoc = new DOMDocument;
            $mock = new DOMDocument;
            $domDoc->loadHTML(file_get_contents($file_path), LIBXML_NOWARNING | LIBXML_NOERROR);
            $body = $domDoc->getElementsByTagName('body')->item(0);
            foreach ($body->childNodes as $child) {
                $mock->appendChild($mock->importNode($child, true));
            }
            $body = $mock->saveHTML();
            $body = str_replace('{$brand}', stg('brand'), $body);
            return $body;
        }
    }

    function save(Req $req)
    {
        $new_legal = $req->any('legal');
        return $this->_save($new_legal);
    }

    function restore()
    {
        $new_legal = $this->getLegalByName('/legal.sample.html');
        return $this->_save($new_legal);
    }

    private function _save($new_legal)
    {
        $legal = '
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html lang="es">
<head>
    <title>Privacy Policy of ' . stg('brand') . '</title>
    <meta content="This Application collects some Personal Data from its Users." name="description"/>
    <meta content="privacy policy" name="keywords"/>
    <meta http-equiv="Content-Language" content="en"/>
    <meta name="robots" content="noindex, follow">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta http-equiv="date" content="2018-09-28">
    <meta http-equiv="last-modified" content="2018-09-28">
    <meta name="viewport" content="width=device-width">
</head>
<body>
' . $new_legal . '
</body>
</html>
';
        if (file_put_contents(_PATH_ . '/legal.html', $legal)) {
            return rsp(true);
        } else {
            return rsp('Se produjo un error');
        }
    }

}