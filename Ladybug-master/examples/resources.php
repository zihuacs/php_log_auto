<?php
require_once __DIR__.'/../lib/Ladybug/Autoloader.php';
Ladybug\Autoloader::register();

$file = fopen(__DIR__ . '/../LICENSE', 'r');
ladybug_dump($file);