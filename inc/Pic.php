<?php namespace Inc;

use Exception;
use Gumlet\ImageResize;
use Libs\Pixie\QB;

class Pic
{
    private $is_img = false;
    private $name = null;
    private $folder = null;
    private $prefix = null; # dato opcional que se agregara al lado izquierdo del nombre al autogenerar el nombre

    private $thumb_width = 200;
    private $thumb_height = 200;

    private $file = null;

    private function __construct($file_key = null, $is_img = false)
    {
        $this->is_img = $is_img;
        $this->file = @$_FILES[$file_key];
    }

    public static function file($file_key)
    {
        return new Pic($file_key, false);
    }

    public static function img($file_key)
    {
        return new Pic($file_key, true);
    }

    public function setIsImage($is_image = true)
    {
        $this->is_img = $is_image;
        return $this;
    }

    public static function fileArr($list_key, $file_key, $index)
    {
        $ins = new Pic();
        if (isset($_FILES[$list_key]['tmp_name'][$index][$file_key])) {
            $ins->file = [
                'tmp_name' => $_FILES[$list_key]['tmp_name'][$index][$file_key],
                'name'     => $_FILES[$list_key]['name'][$index][$file_key],
            ];
        }
        return $ins;
    }

    public function name($name)
    {
        $this->name = $name;
        return $this;
    }

    public function getFile()
    {
        return $this->file;
    }

    public function getFileName()
    {
        return $this->file ? $this->file['name'] : '';
    }

    public function folder($folder)
    {
        $this->folder = $folder;
        return $this;
    }

    public function prefix($prefix)
    {
        $this->prefix = $prefix;
        return $this;
    }

    public function thumbSize($width, $height = 0)
    {
        $this->thumb_width = $width;
        $this->thumb_height = $height;
        return $this;
    }

    # helpers folders
    public function global()
    {
        return $this->folder('global');
    }

    /**
     * @return string|null
     */
    public function go()
    {
        if ($this->is_img) {
            return $this->_saveImg();
        } else {
            return $this->_saveFile();
        }
    }

    /**
     * @param $table
     * @param $column
     * @param $id
     * @return null|string
     */
    public function db($table, $column, $id)
    {
        $this->folder($table);
        $this->name($column . '_' . $id);
        if ($pic = $this->go()) {
            if (QB::table($table)->where('id', $id)->update([$column => $pic . '?t=' . time()])) {
                return $pic;
            } else {
                return null;
            }
        }
        return null;
    }

    private function _existFile()
    {
        return isset($this->file['tmp_name']) && !empty($this->file['tmp_name']);
    }

    private function getFolder()
    {
        return $this->folder ?: 'pics';
    }

    private function getName()
    {
        return ($this->prefix ? $this->prefix . '_' : '') . ($this->name ?: date('Ymd_His') . '_' . uniqid());
    }

    /**
     * @return string
     */
    private function _generateName()
    {
        $name = $this->getName();
        $ext = pathinfo($this->file['name'], PATHINFO_EXTENSION) ?: 'pic';
        return '/' . $name . '.' . $ext;
    }

    /**
     * @return string|null
     */
    private function _saveImg()
    {
        if (!$this->_existFile()) return null;

        $pic = $this->_generateName();

        $folder = '/' . $this->getFolder();

        $path_thumbnail = upl('/_' . $folder);
        $path_default = upl($folder);

        try {
            $ir1 = new ImageResize($this->file['tmp_name']);
            $ir1->resizeToBestFit(900, 900);
            $ir1->save($path_default . $pic);

            $ir2 = new ImageResize($this->file['tmp_name']);
            if ($this->thumb_height == 0) {
                $ir2->resizeToBestFit($this->thumb_width, $this->thumb_width);
            } else {
                $ir2->crop($this->thumb_width, $this->thumb_height);
            }
            $ir2->save($path_thumbnail . $pic);
            return $folder . $pic;
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * @return string|null
     */
    private function _saveFile()
    {
        if ($this->_existFile()) {
            $folder = '/' . $this->getFolder();
            $path_default = upl($folder);

            $pic = $this->_generateName();

            if (@move_uploaded_file($this->file['tmp_name'], $path_default . $pic)) {
                return $folder . $pic;
            } else {
                return null;
            }
        } else {
            return null;
        }
    }

    public static function url($pic, $thumb = false, $placeholder = null)
    {
        if (empty($pic)) {
            if ($placeholder)
                return URL_API . $placeholder;
            else
                return '';
        } else {
            return url_uploads() . ($thumb ? '/_' : '') . $pic;
        }
    }

    public static function saveUrl($url)
    {
        $folder = '/cache';
        $path = upl($folder);
        $pic = '/' . md5($url) . '.jpg';
        $final_path = $path . $pic;

        if (file_exists($final_path)) {
            return $folder . $pic;
        } else if (@copy($url, $final_path)) {
            return $folder . $pic;
        } else {
            return '';
        }
    }

}