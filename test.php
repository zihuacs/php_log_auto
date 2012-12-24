<?php
require dirname(__FILE__) . '/PhpParser/lib/bootstrap.php';
$code = <<<'CODE'
<?php
    function printLine($msg) {
        echo $msg, "\n";
    }

    printLine('Hallo World!!!');
CODE;


$parser = new PHPParser_Parser(new PHPParser_Lexer);
$nodeDumper = new PHPParser_NodeDumper;
$serializer = new PHPParser_Serializer_XML;
echo "zzzzzzzzzzzzzzzzzz";
try {
    $stmts = $parser->parse($code);
    echo '<pre>' . htmlspecialchars($nodeDumper->dump($stmts)) . '</pre>';   
    echo '<pre>' . htmlspecialchars($serializer->serialize($stmts)) . '</pre>';
} catch (PHPParser_Error $e) {
    echo 'Parse Error: ', $e->getMessage();
}

?>