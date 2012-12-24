<?php
require_once __DIR__.'/../lib/Ladybug/Autoloader.php';
Ladybug\Autoloader::register();

// user class
class Foo3 {public $a; public function __construct() {$this->a = new DateTime();}}
class Foo2 {public $a; public function __construct() {$this->a = new Foo3();}}
class Foo {
    public $bar = 1;
    public $bar2 = 2;
    public $a;
    
    public function __construct() {$this->a = new Foo2();}
    public function getBar() { return $this->bar; }
    public function setBar($bar = 1, $bar2 = TRUE, $bar3 = NULL) { $this->bar = $bar; }
    public function __toString() {return $this->bar . ' - ' . $this->bar2; }
}

$foo = new Foo();
ladybug_dump($foo);

// DateTime object

$date = new DateTime();
ladybug_dump($date);


$sXml = <<<XML
<books>
    <book id="1">
        <title>PHP 5 Power Programming</title>
        <author>Andi Gutmans, Stig Bakken, Derick Rethans</author>
    </book>
    <book id="2">
        <title>Clean Code: A Handbook of Agile Software Craftsmanship</title>
        <author>Robert C. Martin</author>
    </book>
</books>
XML;
$dom = new DOMDocument();
$dom->loadXml($sXml);

ladybug_dump($dom);

$reflected = new ReflectionClass('Foo');

ladybug_dump($reflected);