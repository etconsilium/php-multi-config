<?php include './vendor/autoload.php';

use MultiConfig\Config;

$config = new MultiConfig\Config([[2,3,4],5,6,]);

var_dump($config->getArrayCopy());
var_dump($config->get('0.1'));


var_dump( MultiConfig\Config::loadFromFile('./composer.json', MultiConfig\Config::JSON )->getArrayCopy() );
var_dump( MultiConfig\Config::loadFromFile('./data/phpunit.xml.dist', MultiConfig\Config::XML )->getArrayCopy() );
var_dump( MultiConfig\Config::loadFromFile('./data/config.yaml', MultiConfig\Config::YAML )->getArrayCopy() );
var_dump( MultiConfig\Config::loadFromFile('./data/GitHub.tmTheme.xml', MultiConfig\Config::PLST )->getArrayCopy() );