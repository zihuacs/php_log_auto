<?php
require_once __DIR__.'/../lib/Ladybug/Autoloader.php';
Ladybug\Autoloader::register();

$var1 = NULL;
$var2 = 15;
$var3 = 15.5;
$var4 = 'hello world!';
$var5 = false;
$v=new \Symfony\Component\Serializer\Serializer();
ladybug_dump($var1, $var2, $var3, $var4, $var5,$v);
