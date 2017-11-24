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