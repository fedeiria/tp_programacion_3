<?php

namespace App\Models\ORM;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

define("PATH_PICTURES", "/storage/ssd1/602/11777602/img/orders/");

class Foto
{
    private static $allowedLimit = 2097152;
    private static $allowedExtensions = array("image/jpg", "image/jpeg", "image/png");

    public function GetPhoto() {
        $typePhoto = $_FILES["foto"]["type"];
        $sizePhoto = $_FILES["foto"]["size"];
        $namePhoto = $_FILES["foto"]["name"];
        $pathPhoto = $_FILES["foto"]["tmp_name"];
        
        if (in_array($typePhoto, self::$allowedExtensions) && ($sizePhoto <= self::$allowedLimit)) {
            return self::saveTemp($pathPhoto, PATH_PICTURES, $namePhoto);
        }

        return null;
    }

    private function saveTemp($source, $destination, $filename) {
        $extension = self::getExtensionFile($filename);
        $path = $destination . $filename;
        move_uploaded_file($source, $path);
        return $path;
    }

    private function getExtensionFile($filename) {
        $extension = substr($filename, strrpos($filename, '.'));
        return $extension;
    }
}

?>