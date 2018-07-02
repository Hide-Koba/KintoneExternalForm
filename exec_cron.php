#!/usr/bin/php
<?php
$url =  'http://www.study-au.com/ktform_is/index.php/Mail1?key=Wyqx7BVtdcwiV4zDXGIijVxVvgNqk6ShJRdsQULT';
$contents = file_get_contents( $url, FALSE );
var_dump($contents);