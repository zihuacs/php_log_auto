<?php
require 'ShowSyntaxTree.php';

function adjust_cmp($a,$b) {
	if($a[1]==$b[1]) {
		if($a[0]==$b[0]) {
			return 0;
		}
		return $a[0] < $b[0] ? -1:1;
	}
	return $a[1] < $b[1] ? 1:-1;
}

class PHPParser_MatchLogPattern
{
    /**
     * Dumps a node or array.
     *
     * @param array|PHPParser_Node $node Node or array to dump
     *
     * @return string Dumped value
     */
    protected $root=NULL;
    
    protected $glog_index2sug_line=array();
    
    protected $gsug_line=array();
    
    protected $gorigin_log_line=array();
    
    
    protected $log_file="./TestData/test_log.txt"; 
    
    protected $g_log_arr=array();

    protected $gfinal_line_score=array();
    
    # debug to use it then we can feed back to adjust weight
    private $gsug_line2log_index=array();

    protected $is_debug=false;


    function __construct($pattern_file) {
        $this->log_file = $pattern_file;
    }
    public function set_root($node) {
    	$this->root=$node;
    	
    }
    public function get_root() {
    	return $this->root;
    }
    /*
     * $stmt instanceof PHPParser_Node_Stmt_ClassMethod
     * find the PHPParser_Node_Expr_MethodCall node 
     */
    public function get_func_ship($stmt,$class_method_name) {
    	if(!($stmt instanceof PHPParser_Node || is_array($stmt))) {
    		return;
    	}
    	foreach($stmt as $sub_stmt) {
    		if($sub_stmt instanceof PHPParser_Node_Expr_MethodCall) {
    			//if($sub_stmt->var->name == 'this'){
    				print $class_method_name . "++++>" . $sub_stmt->name . "<br>";
    			//}
    		}
    		if($sub_stmt instanceof PHPParser_Node_Expr_StaticCall) {
    			//if($sub_stmt->class->parts[0] == 'self') {
    				print $class_method_name . "--->" . $sub_stmt->name . "<br>";
    			//}
    		}
    		if($sub_stmt instanceof PHPParser_Node_Expr_FuncCall) {
    			print $class_method_name . "+-+>" .$sub_stmt->name . "<br>";
    		}
    		$this->get_func_ship($sub_stmt,$class_method_name);
    	}
    }
    
    public function dump($node) {
    	
        if ($node instanceof PHPParser_Node) {
        	if($node instanceof PHPParser_Node_Stmt_Class) {
        		print_r($node->getMethodNames());
        		print "<br>";
        		$methods=$node->getMethods();
        		foreach($methods as $stmt) {
        			$this->get_func_ship($stmt,$stmt->name);
        		}
        	}
            $r = $node->getType() . '(';
        } elseif (is_array($node)) {
            $r = 'array(';
        } else {
            throw new InvalidArgumentException('Can only dump nodes and arrays.');
        }

        foreach ($node as $key => $value) {
            $r .= "\n" . '    ' . $key . ': ';

            if (null === $value) {
                $r .= 'null';
            } elseif (false === $value) {
                $r .= 'false';
            } elseif (true === $value) {
                $r .= 'true';
            } elseif (is_scalar($value)) {
                $r .= $value;
            } else {
                $r .= str_replace("\n", "\n" . '    ', $this->dump($value));
            }
        }

        return $r . "\n" . ')';
    }
    
    public function dump_child($node,$parent_node,$depth,$count,$pos_num=-3) {											
     	if($depth<0){
     		return;
     	}
     	
//        if ($parent_node instanceof PHPParser_Node) {
//            $r =$parent_node->getType() . '(';
//        } elseif (is_array($parent_node)) {
//            $r = '(' ;
//        } else {
//        	$r="";
//          //  throw new InvalidArgumentException('Can only dump nodes and arrays.');
//        }	
        $r="";	
        $flag= ($r==""? true:false);
        if ($node instanceof PHPParser_Node) {
            $r .=$node->getType() . '(' ;
        } elseif (is_array($node)) {
            $r .= '(';
        } else {
            throw new InvalidArgumentException('Can only dump nodes and arrays.');
        }
        $i=0;
        foreach ($node as $key => $value) {
            #$r .= "\n" . '    ' . $key . ': ';
            $i++;
            if($i<$pos_num-2){
            	continue;
            }
            
            if($i > $pos_num+2){
            	break;
            }
              
            if (null === $value) {
                $r .= 'null';
            } elseif (false === $value) {
                $r .= 'false';
            } elseif (true === $value) {
                $r .= 'true';
            } elseif (is_scalar($value)) {
                $r .= $value;
            } else {
            	if($i == $pos_num) {
            		$r .= str_replace("\n", "\n" . '    ', $this->dump_child($value,$node,$depth-1,$count));
                }
                else {
                	$r .= str_replace("\n", "\n" . '    ', $this->dump_child($value,$node,$depth-1,$count));  	
                }
            }
        }
        
        return $flag==true ? $r . "\n" . ')' : $r. "\n" . '))';
    }
    
    public function match_log_pattern($root,$parent_match_list) {

        if(count($parent_match_list) < 0 ) {
            return array();
        }

        $match_arr=array();
        $match_line=array();
        $match_up_line=array();
        
        #$show_tree = new PHPParser_ShowSyntaxTree;
        #$show_tree->show_tree($root);
        foreach($root as $key => $value) {
            
            if (null === $value || false === $value || true === $value || is_scalar($value) || is_array($value)) {
                // print $key . ' ' . $value . "<br />";
                continue;
            }

            if($value instanceof PHPParser_Node) {
                $match_arr[]=$value->getType();
            }
            elseif(is_array($value)) {
                $match_arr[]="array";
            }
            else{
                $match_arr[]=" ";
            }

            $match_line[]=$value->getLastLine(); 
            $match_up_line[]=$value->getLine() - 1;
        }
        $log_sug_pos_arr=array();
        for ($i=0; $i < count($parent_match_list) ; $i++) { 

          $count=0;
          $log_arr=array();
          $log_index=0;
          $log_line=0;
          $stmt_return_count=0;

          $log_arr = explode("##", $this->g_log_arr[$parent_match_list[$i]]["current"]);
          array_pop($log_arr);

          $log_index =  (int)$this->g_log_arr[$parent_match_list[$i]]["log_index"];

          $len_match_arr=count($match_arr);
          $len_log_arr=count($log_arr);

          for($j=0; $j<$len_match_arr - $len_log_arr + 1; $j++) {

            $sub_match_arr=array_slice($match_arr, $j, $len_log_arr);
            $check_result = array_diff_assoc($log_arr, $sub_match_arr);

            if(count($check_result) == 0 ) {

               # find one match point then give some information

               $sub_match_line=array_slice($match_line,$j,$len_log_arr);
               $sub_match_up_line=array_slice($match_up_line, $j, $len_log_arr);

               //if(abs($log_line - $sub_match_line[$log_index]) > 2) {
                  if($log_index == -1 ) {
                    $temp_match_line = $sub_match_up_line[0];
                  } else {
                    $temp_match_line = $sub_match_line[$log_index];
                  }
                  $log_sug_pos_arr[]=array($parent_match_list[$i],$temp_match_line);

                  #print "find +++ goog goog one check point!!!!!!!!!!! <br>";
                  if($this->is_debug) {
                      //print implode(" ", $this->g_log_arr[$parent_match_list[$i]]) . "<br>" ;
                      //print "may be you can insert some log after line:" . $temp_match_line . "<br>";
                  }
              //}
              }
          }			
      }
      return $log_sug_pos_arr;
  }
    /**
     * load log pattern file to array 
     * then to match it 
     */
    public function load_log_pattern() {
    	$fp=fopen($this->log_file,'r');
    	if($fp) {
    		unset($this->g_log_arr);
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
    
    private function get_parent_type($ptype_arr,$index) {
        $arr_len = count($ptype_arr);


        if ( $arr_len - $index > 0) {
            return $ptype_arr[$arr_len - $index - 1];
        }
        return array("null",0);
    }

    public function get_node_len($node) {
        $len_num=0;
        foreach ($node as $key => $value) {
            if (null === $value || false === $value || true === $value || is_scalar($value)) {
                    continue;
            }
            if($value instanceof PHPParser_Node) {
                $len_num++;
                continue;
            }   
            $len_num += $this->get_node_len($value);   
        }
        return $len_num;
    }

    private function find_log_pattern($pnode,$ptype_arr) {
        if($pnode instanceof PHPParser_Node) {
            $ptype_arr[]=array($pnode->getType(),$this->get_node_len($pnode));
        }

        $grand_parent_arr = $this->get_parent_type($ptype_arr,1);

        $grand_parent_match_list=array();

        foreach($this->g_log_arr as $index => $log_arr) {
            if($grand_parent_arr[0] == $log_arr["grand_parent"] && 
                abs($grand_parent_arr[1] - $log_arr["grand_parent_len"]) < 10) {
                $grand_parent_match_list[]=$index;
            }   
        }

    	if(count($grand_parent_match_list) > 0) {

            $parent_arr = $this->get_parent_type($ptype_arr,0);

            $parent_match_list=array();
            for ($i=0; $i < count($grand_parent_match_list); $i++) { 
                $j=$grand_parent_match_list[$i];

                if( $parent_arr[0] == $this->g_log_arr[$j]["parent"] &&
                    abs($parent_arr[1] - $this->g_log_arr[$j]["parent_len"]) < 5 ) {
                    $parent_match_list[]=$j;
                }
            }
            $res_log_index2sug_line_arr=array();
            if(count($parent_match_list) > 0) {
                $res_log_index2sug_line_arr=$this->match_log_pattern($pnode,$parent_match_list);
            }
            
            $this->insert_glog_index2sug_line($res_log_index2sug_line_arr);
    		
    	}
    	
    	foreach ($pnode as $key => $value) {
	    	if (null === $value || false === $value || true === $value || is_scalar($value)) {
		    	continue;
	    	}

	    	$this->find_log_pattern($value,$ptype_arr);
    	}
    	
    }  
    
    /*
     * $log_index2sug_line_arr:
     * input:array(array(log_index,sug_line), .... , array(log_index,sug_line))
     * 
     */    
    public function insert_glog_index2sug_line($log_index2sug_line_arr) {

        for ($i=0; $i < count($log_index2sug_line_arr); $i++) { 
                    # code...
            $pattern_log_index = $log_index2sug_line_arr[$i][0];
            $sug_log_line = $log_index2sug_line_arr[$i][1];

            if(isset($this->glog_index2sug_line[$pattern_log_index]["count"])) {
                $this->glog_index2sug_line[$pattern_log_index]["count"]++;
            } else {
                $this->glog_index2sug_line[$pattern_log_index]["count"]=1;
            }

            if (isset($this->glog_index2sug_line[$pattern_log_index][$sug_log_line])) {
                        # code...
                $this->glog_index2sug_line[$pattern_log_index][$sug_log_line]++;
            } else {
                $this->glog_index2sug_line[$pattern_log_index][$sug_log_line]=1;
            }

            if($this->is_debug) {
                if (isset($this->gsug_line2log_index[$sug_log_line][$pattern_log_index])) {
                    # code...
                    $this->gsug_line2log_index[$sug_log_line][$pattern_log_index]++;
                } else {
                    $this->gsug_line2log_index[$sug_log_line][$pattern_log_index]=1;
                }
            }
        }
    }

    public function normal_sug_line_weight() {
        #print_r($this->glog_index2sug_line);
        # basic log weight to normal then to [0,1]
        $sum=0.0;
        foreach ($this->glog_index2sug_line as $log_index => $log_sug_arr) { 
            # code...
            $sum += $this->g_log_arr[$log_index]["log_score"];
        }
        foreach ($this->glog_index2sug_line as $log_index => $log_sug_arr) { 
            # code...
            $this->glog_index2sug_line[$log_index]["log_score"] =  $this->g_log_arr[$log_index]["log_score"]*100.0/$sum;
            #print $this->glog_index2sug_line[$log_index]["log_score"] . "<br>";
        }

        $sum=0.0;
        foreach ($this->glog_index2sug_line as $log_index => $log_sug_arr) { 
            # code...
            $sum += $log_sug_arr["count"];
        }

        foreach ($this->glog_index2sug_line as $log_index => $log_sug_arr) { 
            # code...
            $this->glog_index2sug_line[$log_index]["log_score"] +=  ($sum-$log_sug_arr["count"])*10.0/$sum;
            #print $this->glog_index2sug_line[$log_index]["log_score"] . "<br>";
        }
        #print_r($this->glog_index2sug_line);

    }

    public function merge_sug_line_weight($log_sug_arr,$res_arr) {
        foreach ($log_sug_arr as $key => $value) {
            # code...
            if($key =="count" || $key == "log_score") {
                continue;
            }
            if(isset($res_arr[$key])) {
                $res_arr[$key] += $log_sug_arr["log_score"] * $value;
            } else {
                $res_arr[$key] = $log_sug_arr["log_score"] * $value;
            }
        }
        return $res_arr;
    }

    public function adjust_sug_line_weight() {
         //print_r($this->glog_index2sug_line);
        // print "<br />";
        $this->normal_sug_line_weight();
        // print_r($this->glog_index2sug_line);
        $res_arr=array();
        foreach ($this->glog_index2sug_line as $log_index => $log_sug_arr) { 
            # code...
            $res_arr = $this->merge_sug_line_weight($log_sug_arr,$res_arr);
        }

        $this->gfinal_line_score=array();
        foreach ($res_arr as $key => $value) {
            # code...
            $this->gfinal_line_score[]=array($key,$value);
        }
        return true;
    }

    private function show_sug_line2log_index($sug_line) {
        print $sug_line . "------->" ." ";
        if(isset($this->gsug_line2log_index[$sug_line])) {
            
            foreach ($this->gsug_line2log_index[$sug_line] as $log_index => $count) {
                # code...
                print "(" . $log_index . "--" . $this->g_log_arr[$log_index]["log_line"] . ") ";
            }
        }
        print "<br />";
    }

    public function sort_for_result() {
        usort($this->gfinal_line_score, "adjust_cmp");
        // print '<table width="739" border="1" align="center" cellpading="3">';
        // print '<tbody>';
        // print '<tr><td colspan="5" aligh="right">';

        // print '</td></tr>';
        if($this->is_debug) {


            foreach($this->gfinal_line_score as $key => $value) {
                print $value[0] . "\t\t\t" . $value[1] . "<br>";
            #printf("%d\t\t\t%f\n",$value[0],$value[1]);
                
                
                if ($this->is_debug) {
                # code...
                    $this->show_sug_line2log_index($value[0]);
                }
            }
        }
        // print '</tbody>';
        // print "</table>";
        return true;
    }
    /*
     * main function
     */
    public function get_log_sug_line($root) {
        $this->load_log_pattern();
        $this->find_log_pattern($root,array());
        $this->adjust_sug_line_weight();
        $this->sort_for_result();
        return $this->gfinal_line_score;
    }
}