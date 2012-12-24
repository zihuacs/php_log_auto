<?php

class ShowSrcFile {
	private $php_src_file;
    private $gorigin_log_line;
    private $gsug_log_line;
    private $label_arr; # 锚标记数组
	function __construct($php_src_file,$gorigin_log_line=array(),$gsug_log_line=array()) {
        $this->php_src_file     = $php_src_file;
        $this->gorigin_log_line = $gorigin_log_line;
        $this->gsug_log_line    = $gsug_log_line;
        
        $this->get_label_arr();

	}

    public function get_label_arr() {
        unset($this->label_arr);
        $this->label_arr = array();

        foreach ($this->gorigin_log_line as $key => $value) {
            # code...
            $flag=true;
            foreach ($this->label_arr as $l_key => $l_value) {
                # code...
                if($l_value == $value)
                {
                    $flag=false;
                    break;
                }
            }
            if($flag) {
                $this->label_arr[]=$value;
            }
        }

        foreach ($this->gsug_log_line as $key => $value) {
            # code...
            $flag=true;
            foreach ($this->label_arr as $l_key => $l_value) {
                # code...
                if($l_value == $value)
                {
                    $flag=false;
                    break;
                }
            }
            if($flag) {
                $this->label_arr[]=$value;
            }
        }
        sort($this->label_arr);
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
        print '<h1 align="center">Php Src File</h1>';

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
    public function show_end()
    {
        # code...
        print '</tbody>';
        print "</table>";
        print "</body>";
        print '</html>';
    }
    private function is_maybe_log_line($log_line) {
        $res=1000000;
        foreach ($this->gorigin_log_line as $index => $line) {
            # code...
            $res = min($res,abs($log_line - $line)) ;
            
        }
        foreach ($this->gsug_log_line as $index => $line) {
            # code...
            $res= min($res,abs($log_line - $line));
        }
        return $res;
    }

    public function highlight_file_with_line_numbers($file) { 
          //Strip code and first span
        $code      = substr(highlight_file($file, true), 36, -15);
        //Split lines
        $lines     = explode('<br />', $code);
        //Count
        $lineCount = count($lines);
        //Calc pad length
        $padLength = strlen($lineCount);
        
        //Re-Print the code and span again
        echo "<code ><span style=\"color: #000000\">";
        
        //Loop lines
        foreach($lines as $i => $line) {
            $line_no    = $i+1;
            //Create line number
            $lineNumber = str_pad($i + 1,  $padLength, '0', STR_PAD_LEFT);

            echo "<a name=" . $line_no . "></a>";
            if($this->is_maybe_log_line($line_no) <= 5)  {
                //Print line
                if($this->is_maybe_log_line($line_no) == 0) {
                    $this->gen_label_arr2($line_no);
                    echo '<p class="sug_line">';
                    echo sprintf('<br><span style="color: red">%s |->> </span>%s', $lineNumber, $line);
                    echo '</p>';
                } else {
                    echo sprintf('<br><span style="color: blue">%s |->> </span>%s', $lineNumber, $line);
                }
            } else {
                echo sprintf('<br><span style="color: #999999">%s | </span>%s', $lineNumber, $line);
            }

        }
        
        //Close span
        echo "</span></code>";
    }
    public function gen_label_arr2($line) {
        echo '<font size=5px>';
        $tkey=-1;
        echo '<br>';
        if(is_int($line)) {

            foreach ($this->label_arr as $key => $value) {
                # code...
                if($value == $line) {
                    $tkey = $key;
                    break;
                }
            } 
            
            if($tkey != -1) {
                if(isset($this->label_arr[$tkey+1])) {
                    echo '<a href="#' . ($this->label_arr[$tkey+1]) . '">Done </a>';
                } else {
                    echo 'Done ';
                }

                if(isset($this->label_arr[$tkey-1])) {
                    echo '<a href="#' . ($this->label_arr[$tkey-1]) . '">Up </a>';
                } else {
                    echo 'Up ';
                }
            }
        }

        foreach ($this->label_arr as $key => $value) {
            # code...
            if($tkey != -1 && $key == $tkey) { 
                echo $key . " ";
                continue; 
            }
            echo '<a href="#' . ($value) . '">';
            echo $key . " ";
            echo '</a>';
        }
        echo '<a href="#start">First </a>';
        
        echo '</font>';
    }
    public function gen_label_arr()
    {
        # code...
        echo '<tr><td>';
        echo '<font size=5px>';

        foreach ($this->label_arr as $key => $value) {
            # code...
            echo '<a href="#' . ($value) . '">';
            echo $key . " ";
            echo '</a>';
        }
        echo '</font>';
        echo '</td></tr>';
    }

    public function show_content() {
        echo '<a name="start">';
        $this->gen_label_arr();
        echo '</a>';

        echo '<tr><td>';
        echo "<pre>";
        #highlight_file($this->php_src_file);
 
        $this->highlight_file_with_line_numbers($this->php_src_file);
        // $fp = fopen($this->php_src_file, 'r');
        // $line_no=0;
        // while (($src_line = fgets($fp))!=false) {
        //     # code...
        //     $line_no++;
        //     if ($this->is_maybe_log_line($line_no) <= 5) {
        //         # code...
        //         if($this->is_maybe_log_line($line_no)==0) { echo '<font color="red">';}
        //         else {  echo '<font color="blue">';}
        //         echo $line_no . ": " . htmlspecialchars($src_line) ;
        //         echo '</font>';
        //     } else {
        //         echo $line_no . ": " . htmlspecialchars($src_line) ;
        //     }

        // }
        // fclose($fp);
        echo "</pre>";
    	#echo '<pre>' . htmlspecialchars( file_get_contents($this->php_src_file) ) . '</pre>';
    	echo  '</td></tr>';
        print '<tr><td  height="40" align="center" valign="bottom"> <hr> </td></tr>';
    }
    public function start_show() {
        $this->show_header();
        $this->show_begin();
        $this->show_content();
        $this->show_end();
    }
}
session_start();

if(isset($_SESSION['php_src'])) {
    $gorigin_log_line = array();
    if(isset($_SESSION['gorigin_log_line'])) {
        $gorigin_log_line = $_SESSION['gorigin_log_line'];
        #print_r($gorigin_log_line);
    }
    $gsug_log_line = array();
    if(isset($_SESSION['gsug_log_line'])) {
        $gsug_log_line = $_SESSION['gsug_log_line'];
        #print_r($gsug_log_line);
    }
    $php_src_file = "../data/log_sug_temp/" . $_SESSION['php_src'];
    $show_src_file = new ShowSrcFile($php_src_file,$gorigin_log_line,$gsug_log_line);
    $show_src_file->start_show();
} else {
	$_SESSION["no_file_err"] = "No File Upload, Can't Show Src File!";
	echo '<script>location.href="index.php"</script>';
}