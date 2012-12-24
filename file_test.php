<?php
 @header('Content-type: text/html;charset=UTF-8');
 
require dirname(__FILE__) . '/PhpParser/lib/bootstrap.php';
class FileLexer extends PHPParser_Lexer {
    public function startLexing($fileName) {
        if (!file_exists($fileName)) {
            throw new InvalidArgumentException(sprintf('File "%s" does not exist', $fileName));
        }

        parent::startLexing(file_get_contents($fileName));
    }
}
$parser = new PHPParser_Parser(new FileLexer);
$nodeDumper = new PHPParser_NodeDumper;
$serializer = new PHPParser_Serializer_XML;
$logExtract = new PHPParser_ExtractLogPattern; 
$logMatch = new PHPParser_MatchLogPattern('./data/log_pattern/pattern');
try {
    $stmts = $parser->parse('./TestData/DB.class.php.txt');
    
    #$stmts = $parser->parse('./TestData/simple.test.php');
    #$nodeDumper->set_root($stmts);
    #echo '<pre>' . htmlspecialchars($nodeDumper->dump($stmts)) . '</pre>';   
    #$nodeDumper->get_log_pattern($stmts,NULL);
    #echo '<pre>' . htmlspecialchars($nodeDumper->get_log_pattern($stmts,NULL)) . '</pre>';
    #$nodeDumper->sort_pos2line();
    #echo '<pre>' . htmlspecialchars($serializer->serialize($stmts)) . '</pre>';
    $logExtract->clear_log_pattern_2_file();
    
    $logExtract->set_php_src_file("DB.class.php.txt");
    $logExtract->get_log_pattern($stmts,NULL);
    
    $stmts = $parser->parse('./TestData/Socket.class.php.txt');
    $logExtract->set_php_src_file("Socket.class.php.txt");
    $logExtract->get_log_pattern($stmts,NULL);

    print "log 打印提示!!!!!!!!!!<br><br>";
    $logMatch->get_log_sug_line($stmts);
    print count($stmts,1) . "<br>";
    echo '<pre>' . htmlspecialchars($nodeDumper->dump($stmts)) . '</pre>';
    
    
} catch (PHPParser_Error $e) {
    echo 'Parse Error: ', $e->getMessage();
}
?>
