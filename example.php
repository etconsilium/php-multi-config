<?php include './vendor/autoload.php';

use MultiConfig\Config;

$config = new MultiConfig\Config([[2,3,4],5,6,]);

var_dump((array)$config);
var_dump($config->get('0.1'));
