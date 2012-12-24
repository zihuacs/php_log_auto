<?php
 @header('Content-type: text/html;charset=UTF-8');
require '../PhpParser/lib/bootstrap.php';
require_once __DIR__.'/../Ladybug-master/lib/Ladybug/Autoloader.php';
Ladybug\Autoloader::register();


class FileLexer extends PHPParser_Lexer {
    public function startLexing($fileName) {
        if (!file_exists($fileName)) {
            throw new InvalidArgumentException(sprintf('File "%s" does not exist', $fileName));
        }

        parent::startLexing(file_get_contents($fileName));
    }
}

class BuildSyntaxTree {
    private $parser;
    private $show_tree;
    private $php_src_file;
    function __construct($php_src_file) {
        # code...
        $this->parser       = new PHPParser_Parser(new FileLexer);
        $this->show_tree    = new PHPParser_ShowSyntaxTree;
        $this->php_src_file = $php_src_file;
    }

    function get_tree() {
        try {
            $stmts = $this->parser->parse($this->php_src_file);
        } catch  (PHPParser_Error $e) {
            #echo 'Parse Error: ', $e->getMessage();
            $_SESSION['parser_error']=$e->getMessage();
            return NULL;
        }
        return $stmts;
    }


    public function show_header() {
        print '<html>';
        print '<head>
               <meta http-equiv="Content-Type" content="text/html; charset=utf8">
               <title>Problems</title>
               <link href="log_sug.css" rel="stylesheet" type="text/css">
               </head>';

        print '<body>';
        print '<p align="left">';
        print '<a href="index.php">Home</a>';
        print ' | ';
        print '<a href="build_log_sug.php">LogSug</a>';
        print ' | ';
        print '<a href="build_syntax_tree.php">SyntaxTree</a>';
        print ' | ';
        print '<a href="build_func_info.php">FuncInfo</a>';
        print ' | ';
        print '<a href="ShowSrcFile.php">SrcFile</a>';
        print '</p>';
        print '<div style="position:absolute; top:0; left:0;"><a style="text-decoration: none;" href="admin">&nbsp;&nbsp;</a></div>';
        print '<h1 align="center">Php Src SyntaxTree</h1>';

    }
    public function show_begin() {

        print '<table width="750" border="0" align="center" cellpading="3">';
        print '<tbody>';
        print '<tr><td  align="center">';
        // print '<input name="keywords" type="text" id="keywords" size="32" maxlength="200">';
        // print '<input name="submit" type="submit" value="Search">';
        $arr = explode("/", $this->php_src_file);
        print $arr[count($arr)-1];
        print '</td></tr>';
        print '<tr><td  height="40" align="center" valign="top"> <hr> </td></tr>';
    }
    function show_tree() {
        print '<tr><td  align="left">';
        $stmts = $this->get_tree($this->php_src_file);
        if($stmts == NULL) {
            print 'no result';
        }
        #ladybug_set("array.max_nesting_level", 10);
        #ladybug_set("object.max_nesting_level",10);
        #echo "详细语法树:<br>";
        #ladybug_dump($stmts);
        echo "简洁语法树:<br>";
        ladybug_dump($this->show_tree->get_stmts2array($stmts));
        echo "抽象语法树:<br>";
        $this->show_tree->show_tree($stmts);
        print '</td></tr>';
        print '<tr><td  height="40" align="center" valign="bottom"> <hr> </td></tr>';
    }
    public function show_end()
    {
        # code...
        print '</tbody>';
        print "</table>";
        print "</body>";
        print '</html>';
    }
    public function start_show() {
        $this->show_header();
        $this->show_begin();
        $this->show_tree($this->php_src_file);
        $this->show_end();
    }
}
session_start();

if(isset($_SESSION['php_src'])) {

    $php_src_file = "../data/log_sug_temp/" . $_SESSION['php_src'];
    $build_syntax_tree = new BuildSyntaxTree($php_src_file);
    #$build_syntax_tree->show_tree('../data/log_sug_temp/DB.class.php');
    #$build_syntax_tree->show_tree('../data/log_pattern_src/DB.class.php');
    $build_syntax_tree->start_show();
    #$build_syntax_tree->test2(array());
    if(isset($_SESSION['parser_error'])) {
        echo '<script>location.href="index.php"</script>';
    }
} else {
    $_SESSION["no_file_err"] = "No File Upload, Can't Show SyntaxTree!";
    echo '<script>location.href="index.php"</script>';
}
?>
