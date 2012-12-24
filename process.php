<?php
 @header('Content-type: text/html;charset=UTF-8');
require dirname(__FILE__) . '/PhpParser/lib/bootstrap.php';
class MyNodeVisitor extends PHPParser_NodeVisitorAbstract
{
    public function leaveNode(PHPParser_Node $node) {
        if ($node instanceof PHPParser_Node_Scalar_String) {
            $node->value = 'foo';
        }
    }
}
class FileLexer extends PHPParser_Lexer {
    public function startLexing($fileName) {
        if (!file_exists($fileName)) {
            throw new InvalidArgumentException(sprintf('File "%s" does not exist', $fileName));
        }

        parent::startLexing(file_get_contents($fileName));
    }
}

$parser        = new PHPParser_Parser(new FileLexer);
$traverser     = new PHPParser_NodeTraverser;
$prettyPrinter = new PHPParser_PrettyPrinter_Zend;

// add your visitor
$traverser->addVisitor(new MyNodeVisitor);

try {
    // parse
    $stmts = $parser->parse('./TestData/simple.socket.class.php');

    // traverse
    $stmts = $traverser->traverse($stmts);

    // pretty print
    $code = '<?php ' . $prettyPrinter->prettyPrint($stmts);

    echo $code;
} catch (PHPParser_Error $e) {
    echo 'Parse Error: ', $e->getMessage();
}