#!/usr/bin/php
<?php
$url =  'http://www.study-au.com/ktform_is/index.php/Mail2';
$contents = file_get_contents( $url, FALSE );
var_dump($contents);