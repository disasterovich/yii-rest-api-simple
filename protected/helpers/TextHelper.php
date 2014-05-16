<?php
class TextHelper 
    {
    public static function generateCode()
        {
        $res = '';
        $len = 8;
        $useChars = '23456789ABCDEFGHKMNPQRSTUVWXYZabcdefghkmnpqrstuvwxyz';
        $useChars .= $useChars;

        for( $i = 0; $i < $len; $i++ )
            $res .= $useChars[mt_rand( 0, strlen( $useChars ) - 1 )];

        return $res;
        }
    }
?>