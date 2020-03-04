<?php

namespace App\Models;
use Firebase\JWT\JWT;

class TokenJWT
{
    private static $secretKey = 'fD$,KD.)e-D4a6%>';
    private static $encryption = ['HS256'];
    private static $aud = null;
    
    public static function NewToken($data) {
        $now = time();
        
        $payload = array(
        	'iat'=> $now,
            'exp' => $now + (10800),
            'aud' => self::Aud(),
            'data' => $data,
        );

        return JWT::encode($payload, self::$secretKey);
    }
    
    public static function VerifyToken($token) {
        if (empty($token))
            throw new Exception("El token no puede estar vacio.");
        
        try {
            $decoded = JWT::decode($token, self::$secretKey, self::$encryption);
        }
        catch (Exception $e) {
            throw $e;
        }
            
        if ($decode->aud !== self::Aud())
            throw new Exception("Usuario no valido.");
    }
    
    public static function GetPayLoad($token) {
        try {
            return JWT::decode($token, self::$secretKey, self::$encryption);
        }
        catch (Exception $e) {
            throw $e;
        }
    }

    public static function GetData($token) {
        try {
            return JWT::decode($token, self::$secretKey, self::$encryption)->data;
        }
        catch (Exception $e) {
            throw $e;
        }
    }
    
    private static function Aud() {
        $aud = '';
        
        if (!empty($_SERVER['HTTP_CLIENT_IP']))
            $aud = $_SERVER['HTTP_CLIENT_IP'];

        elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))
            $aud = $_SERVER['HTTP_X_FORWARDED_FOR'];

        else
            $aud = $_SERVER['REMOTE_ADDR'];
        
        $aud .= @$_SERVER['HTTP_USER_AGENT'];
        $aud .= gethostname();
        
        return sha1($aud);
    }
}