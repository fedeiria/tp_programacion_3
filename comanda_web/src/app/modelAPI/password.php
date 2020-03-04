<?php

namespace App\Models;

class Password
{
    public static function Hash($password) {
        return password_hash($password, PASSWORD_DEFAULT, ['cost' => 15]);
    }

    public static function Verify($password, $hash) {
        return password_verify($password, $hash);
    }
}

?>