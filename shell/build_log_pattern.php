<?php

 @header('Content-type: text/html;charset=UTF-8');
#require dirname(__FILE__) . '/PhpParser/lib/bootstrap.php';
require '../PhpParser/lib/bootstrap.php';

class FileLexer extends PHPParser_Lexer {
    public function startLexing($fileName) {
        if (!file_exists($fileName)) {
            throw new InvalidArgumentException(sprintf('File "%s" does not exist', $fileName));
        }

        parent::startLexing(file_get_contents($fileName));
    }
}

class BuildLogPattern{
    private $log_dir;
    private $log_pattern_file;
    private $src_file_list=array();

    private $parser;#= new PHPParser_Parser(new FileLexer);
    private $logExtract;# =

    function __construct($dirname,$pattern_file) {
        # code...
        $this->log_dir = $dirname;
        $this->log_pattern_file = $pattern_file;
        #$this->parser = $php_parser;
        #$this->logExtract =  $log_extract;
        $this->parser = new PHPParser_Parser(new FileLexer);
        $this->logExtract = new PHPParser_ExtractLogPattern($this->log_pattern_file);
    }
    private function file_extend($file_name) {
        $extend =explode("." , $file_name);
        $va=count($extend)-1;
        return $extend[$va];
    }

    # get the files in dir rec
    private function listFiles($dirname) {
        //打开目录
        $handle=opendir($dirname);
        //阅读目录
        while(false!=($file=readdir($handle))) {
             //列出所有文件并去掉'.'和'..'
           if($file!='.'&&$file!='..'){
                //所得到的文件名是否是一个目录
               if(is_dir("$dirname/$file")) {
                    //列出目录下的文件
                   listFiles("$dirname/$file");
               }
               else {
                    // //如果是文件则打开该文件
                    // $fp=fopen("$dirname/$file","r");
                    // //阅读文件内容
                    // $data=fread($fp,filesize("$dirname/$file"));
                    if($this->file_extend($file)=='php') {
                        $this->src_file_list[]="$dirname/$file";
                    }
                }
            }
        }
    }
    
    public function clear_log_pattern_file() {
        $fp=fopen($this->log_pattern_file,'w+');
        fclose($fp);
    }
    
    public function start_build(){
        # code...
        $this->listFiles($this->log_dir);
        $this->clear_log_pattern_file();
        
        foreach ($this->src_file_list as $key => $file) {
            # code...
            try {
                $stmts = $this->parser->parse($file);
                $this->logExtract->set_php_src_file($file);
                $this->logExtract->get_log_pattern($stmts,array());
            } catch (PHPParser_Error $e) {
                echo 'Parse Error: ', $e->getMessage();
            }
        }
        echo "build log success." . "<br />";
    }

    public function test()
    {
        # code...
        echo "hellow";
    }

    public function show_log_pattern() {
        #$fp = fopen($this->log_pattern_file,'r');
        echo file_get_contents($this->log_pattern_file);
    }

}

$build_log_pattern = new BuildLogPattern('../data/log_pattern_src','../data/log_pattern/pattern');
$build_log_pattern->start_build();
$build_log_pattern->show_log_pattern();
?>
