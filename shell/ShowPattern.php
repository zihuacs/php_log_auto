<?
 @header('Content-type: text/html;charset=UTF-8');
 require '../PHP-Error-master/src/php_error.php';
\php_error\reportErrors();
/**
* log_index != -1 then show all pattern
* other just show the log_index pattern
*/
class ShowPattern {
	private $pattern_src;
	private $g_log_arr;

	function __construct($pattern_src="../data/log_pattern/pattern") {
		$this->pattern_src = $pattern_src;
		$this->g_log_arr   = array();
		$this->load_log_pattern();

	}

    public function load_log_pattern() {
    	$fp=fopen($this->pattern_src,'r');
    	if($fp) {

    		$temp_log_arr=array();
    		$log_pattern_index=0; 
            while(($line=fgets($fp)) !== false) {
    			unset($temp_log_arr);
    			$temp=explode("$$",$line);
    			foreach($temp as $t) {
    				list($k,$v) = explode(':',$t);
    				$temp_log_arr[$k]=$v;
    			}
    			$this->g_log_arr[]=$temp_log_arr;

                $this->g_log_arr[$log_pattern_index]["log_index"] = (int) $this->g_log_arr[$log_pattern_index]["log_index"];
                $this->g_log_arr[$log_pattern_index]["log_score"] = (float) $this->g_log_arr[$log_pattern_index]["log_score"];
    		    $log_pattern_index++;
            }
    		fclose($fp);
    	}
    	#print_r(explode("##",$this->g_log_arr[0]["current"]));
    	#print_r($this->g_log_arr);
    }
    public function get_one_pattern($log_index) {
    	$r="";
		if(isset($this->g_log_arr[$log_index])) {
			$one_log = $this->g_log_arr[$log_index];

			$arr = explode("/", $one_log["src_file"]);
			$src_file = $arr[count($arr)-1];

			#$r .= $log_index . ' :' . $src_file . "(" .  $one_log["log_line"] . ") (" . $one_log["grand_parent"] . "," .
			# $one_log["grand_parent_len"] .") (" . $one_log["parent"] . "," . $one_log["parent_len"] . ") " . 
             #$one_log["current"] . " " . $one_log["log_index"] . " " . $one_log["log_score"] . "\n";

             $r .= "<br>|----" . $log_index . "----" . $src_file . " <" . $one_log["log_line"] . " >";
             $r .= "<br>.....|----" . $one_log["grand_parent"] . "  " . $one_log["grand_parent_len"];
             $r .= "<br>..........|----" . $one_log["parent"] . "  " . $one_log["parent_len"];
             #$r .= "\n...............|----" . $one_log["current"] . "  " . $one_log["log_index"] . "  " . $one_log["log_score"];

             $current_log_arr = explode("##", $one_log["current"]);
             array_pop($current_log_arr);

             for ($i=0; $i < count($current_log_arr); $i++) { 
             	# code...
             	if ($i == $one_log["log_index"]+1) {
             		# log position
             		$r .= '<font color="red">';
             		$r .= "<br>...............|----" . "here is the log " . $one_log["log_index"] . " " . $one_log["log_count"] . " " . $one_log["log_pos"];
             		$r .= '</font>';
             	}
             	$r .= "<br>...............|----" . $current_log_arr[$i];
             }
             if ($i == $one_log["log_index"]+1) {
         		# log position
         		$r .= '<font color="red">';
         		$r .= "<br>...............|----" . "here is the log " . $one_log["log_index"] . " " . $one_log["log_count"] . " " . $one_log["log_pos"];
         		$r .= '</font>';
             }

             $r .= "<br>...............|----" . $one_log["log_score"];




		}
		return $r . "<br>";
    }
	public function get_pattern($log_index_arr=array()) {
		$r="";
		if(count($log_index_arr)==0) {
			# show all
			for ($i=0; $i < count($this->g_log_arr); $i++) { 
				# code...
				$r .= $this->get_one_pattern($i);
			}
		} else {
			# just show this log_pattern
			foreach ($log_index_arr as $key => $value) {
				# code...
				$r .= $this->get_one_pattern($value);
			}
		}
#		print_r($this->g_log_arr);
		return $r;
	}
}

session_start();
if(isset($_SESSION['php_src'])) {
    $line=-1;
    if (isset($_GET['line'])) {
    # code...
        $line  = $_GET['line'];
    }
    if(isset($_GET['usize'])) {
        $usize = $_GET['usize'];
    }
    if(isset($_GET['dsize'])) {
        $dsize = $_GET['dsize'];
    }

    if($line!=-1) {
        $php_src_file = "../data/log_sug_temp/" . $_SESSION['php_src'];
        $fp = fopen($php_src_file, 'r');
        if($fp) {
            $line_no=0;
            echo "<pre>";
            echo 'code:<br><br>';
            while ( ($src_line = fgets($fp)) != false) {
                # code...
                $line_no++;

                if($line_no >= $line-$usize && $line_no <= $line+$dsize ) {
                    if(abs($line_no-$line)==2) { echo '<font color="blue">'; }
                    if(abs($line_no-$line)==1) { echo '<font color="green">';  }
                    if ($line_no==$line) { echo '<font color="red">'; }
                    echo $line_no . ": " . htmlspecialchars($src_line);
                    if(abs($line_no-$line)<=2) {
                        echo '</font>';
                    }
                }
                if($line_no > $line+$dsize) {
                    break;
                }
            }
            echo "</pre>";
            fclose($fp);
        }
    }
}

$pattern_arr=array();

if(isset($_GET['pattern'])) {
	$pattern_arr = explode(",", $_GET['pattern']);
	$len = count($pattern_arr);
	for ($i=0; $i < $len; $i++) { 
		# code...

		if($pattern_arr[$i] == '' || !is_int($pattern_arr[$i]+0) || $pattern_arr[$i] + 0 <0) {
			unset($pattern_arr[$i]);
		}
	}
}
$show_pattern = new ShowPattern();
echo 'pattern:<br>';
echo '<pre>' . $show_pattern->get_pattern($pattern_arr) . '</pre>';