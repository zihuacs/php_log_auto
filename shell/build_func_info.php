<?
 @header('Content-type: text/html;charset=UTF-8');
require '../PhpParser/lib/bootstrap.php';

class FileLexer extends PHPParser_Lexer {
    public function startLexing($fileName) {
        if (!file_exists($fileName)) {
            throw new InvalidArgumentException(sprintf('File "%s" does not exist', $fileName));
        }

        parent::startLexing(file_get_contents($fileName));
    }
}
class BuildFuncInfo {
	private $parser;
    private $php_src_file;
    private $stmts;

    private $get_func_info;

    function __construct($php_src_file) {
        # code...
        $this->parser       = new PHPParser_Parser(new FileLexer);
        $this->php_src_file = $php_src_file;

        try {
            $this->stmts = $this->parser->parse($this->php_src_file);
        } catch  (PHPParser_Error $e) {
            #echo 'Parse Error: ', $e->getMessage();
            $_SESSION['parser_error']=$e->getMessage();
            echo '<script>location.href="index.php"</script>';
        }

        $this->get_func_info = new PHPParser_GetFuncInfo($this->stmts);
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
        print '<h1 align="center">Php Src FuncInfo</h1>';

    }
    public function show_begin() {

        print '<table width="750" border="10" align="center" cellpading="10">';
        print '<tbody>';
        print '<tr><td  align="center">';
        // print '<input name="keywords" type="text" id="keywords" size="32" maxlength="200">';
        // print '<input name="submit" type="submit" value="Search">';
        $arr = explode("/", $this->php_src_file);
        print $arr[count($arr)-1];
        print '</td></tr>';
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
        $this->show_func_names();
        $this->show_end();
    }

    public function show_func_names() {
        print '<tr><td>';
        echo '<pre>';
    	echo htmlspecialchars('     |+---$func_name <$func_line> $func_complexity' . "\n");
        echo htmlspecialchars('     |----$calss_name (calss)' . "\n");
        echo htmlspecialchars('     |@---$orign_func_call <$orign_func_call_line>' . "\n");
        echo htmlspecialchars('     |@@--$class_func_call <$class_func_call_line>' . "\n");
        echo htmlspecialchars("     数据格式含义如上\n");
        echo ">begin";
        echo  $this->get_func_info->get_func_info($this->stmts,1) ;
        #echo htmlspecialchars($this->get_func_info->get_func_info($this->stmts,1) . "\n");
        echo "<br>>end<br>";
        echo '</pre>';
        print '</td></tr>';
    }
}
session_start();

if(isset($_SESSION['php_src'])) {

    $php_src_file = "../data/log_sug_temp/" . $_SESSION['php_src'];
    $build_syntax_tree = new BuildFuncInfo($php_src_file);
    $build_syntax_tree->start_show();
} else {
    $_SESSION["no_file_err"] = "No File Upload, Can't Show FuncInfo!";
    echo '<script>location.href="index.php"</script>';
}


