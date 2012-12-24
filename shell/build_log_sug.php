<?php
 @header('Content-type: text/html;charset=UTF-8');

require '../PhpParser/lib/bootstrap.php';
require './show_result.php';
session_start();

require '../PHP-Error-master/src/php_error.php';
\php_error\reportErrors();

class FileLexer extends PHPParser_Lexer {
    public function startLexing($fileName) {
        if (!file_exists($fileName)) {
            throw new InvalidArgumentException(sprintf('File "%s" does not exist', $fileName));
        }

        parent::startLexing(file_get_contents($fileName));
    }
}
class BuildLogSug {
    # class
    private $parser;
    private $log_match;

    # array
    private $gfinal_line_score;  # is array
    private $gsug_line2log_index; # is array
    private $gorigin_log_line; 
    
    function __construct($pattern_file) {
        # code...
        $this->parser            = new PHPParser_Parser(new FileLexer);
        $this->log_match         = new PHPParser_MatchLogPattern($pattern_file);

        $this->gfinal_line_score = array();
        $this->gorigin_log_line  = array();
    }

    function start_build($file) {
        try {
                $stmts                     = $this->parser->parse($file);
                $this->gfinal_line_score   = $this->log_match->get_log_sug_line($stmts);
                $this->gsug_line2log_index = $this->log_match->get_sug_line2log_index_arr();

                # class
                $get_log_line  = new PHPParser_GetLogLine($stmts);
                $this->gorigin_log_line = $get_log_line->get_log_line();
                #print_r($this->gorigin_log_line);

        } catch (PHPParser_Error $e) {
            #echo 'Parse Error: ', $e->getMessage();
            $_SESSION['parser_error']  =$e->getMessage();
        }

        return $this->gfinal_line_score;
    }
    function get_sug_line2log_index_arr() {
        return $this->gsug_line2log_index;
    }
    function get_origin_log_line() {
        return $this->gorigin_log_line;
    }
}

if(isset($_SESSION['php_src'])) {
    
    $php_src_file        = "../data/log_sug_temp/" . $_SESSION['php_src'];
    $build_log_sug       = new BuildLogSug('../data/log_pattern/pattern');
    $gfinal_line_score   = $build_log_sug->start_build($php_src_file);
    $gsug_line2log_index = $build_log_sug->get_sug_line2log_index_arr();
    $gorigin_log_line    = $build_log_sug->get_origin_log_line();
    
    $gsug_log_line           = array();
    foreach ($gfinal_line_score as $key => $value) {
        # code...
        $gsug_log_line[]=$value[0];
    }
    $_SESSION['gsug_log_line'] = $gsug_log_line;
    $_SESSION['gorigin_log_line'] = $gorigin_log_line;

    if(isset($_SESSION['parser_error'])) {
        echo '<script>location.href="index.php"</script>';
    }
    if(isset($_GET['volume'])){
        $show_page=$_GET['volume'];
        $show_page+=0;
        if(!is_int($show_page) || $show_page < 0) {
            $show_page=0;
        }
    } else {
        $show_page=0;
    }
    $show_result = new ShowResult($gfinal_line_score,$gsug_line2log_index,$gorigin_log_line,$show_page,$_SESSION['php_src']);
    $show_result->start_show();

} else {
    $_SESSION["no_file_err"] = "No File Upload, Can't Show LogSug!";
    echo '<script>location.href="index.php"</script>';
}

?>