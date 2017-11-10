<?php
/**
 * Created by PhpStorm.
 * User: Frank
 * Date: 10/19/2017
 * Time: 12:36 PM
 */

function rand_string( $length ) {
    $chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz@#$&*";
    $size = strlen( $chars );
    for( $i = 0; $i < $length; $i++ ) {
        $str= $chars[ rand( 0, $size - 1 ) ];

    }

    return $str;
}