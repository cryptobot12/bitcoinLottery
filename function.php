<?php
/**
 * Created by PhpStorm.
 * User: luckiestguyever
 * Date: 11/23/17
 * Time: 11:48 PM
 */

function filterOnlyNumber(&$number, $default, $max, $min)
{
    if (is_numeric($number)) {
        if (($number <= $max) && ($number >= $min))
            $number = floor($number);
        else
            $number = $default;
    } else
        $number = $default;
}

function filterArray(&$array, $size)
{
    $array = str_split($array);
    $array = array_unique($array);


    if (count($array) != $size) {
        $array = array();
        for ($i = 0; $i < $size; $i++) {
            array_push($array, $i + 1);
        }

    } else {
        for ($i = 0; $i < $size; $i++) {
            if ($array[$i] < 1 || $array[$i] > $size) {
                $array = array();
                for ($i = 0; $i < $size; $i++) {
                    array_push($array, $i + 1);
                }
                break;
            }
        }
    }

}


function hide_mail($email) {

    $pos = strpos($email, "@");
    $email = "******" . substr($email, $pos - 5);

    return $email;
}

function rand_string( $length ) {
    $chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
    $size = strlen( $chars );
    $str = "";
    for( $i = 0; $i < $length; $i++ ) {
        $str .= $chars[ rand( 0, $size - 1 ) ];

    }

    return $str;
}