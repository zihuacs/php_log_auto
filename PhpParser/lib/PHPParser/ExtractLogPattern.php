<?php
function recmp($a,$b) {
	if($a[1]==$b[1]) {
		if($a[0]==$b[0]) {
			return 0;
		}
		return $a[0] < $b[0] ? -1:1;
	}
	return $a[1] < $b[1] ? 1:-1;
}

class PHPParser_ExtractLogPattern
{
    /**
     * Dumps a node or array.
     *
     * @param array|PHPParser_Node $node Node or array to dump
     *
     * @return string Dumped value
     */
    protected $root=NULL;
    
    protected $glog_line2sug_line=array();
    
    protected $gsug_line=array();
    
    protected $gfinal_line_score=array();
    
    protected $gorigin_log_line=array();
    
    protected $php_src_file="DB.class.php.txt";
    protected $log_file="./TestData/test_log.txt";
    
    function __construct($log_file='./TestData/test_log.txt') {
        $this->log_file = $log_file;
    }
    public function set_root($node) {
    	$this->root=$node;
    	
    }
    public function get_root() {
    	return $this->root;
    }
    
    public function set_php_src_file($src_file) {
    	$this->php_src_file = $src_file;
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
    public function clear_log_pattern_2_file() {
    	$fp=fopen($this->log_file,'w+');
    	fclose($fp);
    }
    public function write_log_pattern_2_file($log_pattern_arr) {
    	
    	$fp=fopen($this->log_file,'a');
    	fwrite($fp,(implode('',$log_pattern_arr) . "\n"));
    	fclose($fp);
    	return true;
    }
    
    private function get_parent_type($ptype_arr,$index) {
        $arr_len = count($ptype_arr);


        if ( $arr_len - $index > 0) {
            return $ptype_arr[$arr_len - $index - 1];
        }
        return array('null',0);
    }

    public function find_log_pattern($p_node,$pos_num,$log_line,$ptype_arr) {
    	
    	
    	$log_arr=array();
    	
    	$log_arr[]="src_file:" . $this->php_src_file;
    	$log_arr[]="$$";
    	
    	$log_arr[]="log_line:" . $log_line;
    	$log_arr[]="$$";
    	
       # print_r($this->get_parent_type($ptype_arr,1));
        $gp_arr=$this->get_parent_type($ptype_arr,1);

        $log_arr[]="grand_parent:" . $gp_arr[0];
        $log_arr[]="$$";

        $log_arr[]="grand_parent_len:" . $gp_arr[1];
        $log_arr[]="$$";

      #  print_r($this->get_parent_type($ptype_arr,0));
        $p_arr=$this->get_parent_type($ptype_arr,0);
        $log_arr[]="parent:" . $p_arr[0];
        $log_arr[]="$$";

        $log_arr[]="parent_len:" . $p_arr[1];
        $log_arr[]="$$";

        $log_arr[]="current:";
        
        $save_log_arr=$log_arr;

        for ($pre_no=min(5,$pos_num-1); $pre_no >= min(3,$pos_num-1); $pre_no--) { 
          # code...
            for ($las_no=3; $las_no >=0 ; $las_no--) { 
                # code...
                if( ($pos_num - $pre_no <=0 ) || ($pos_num + $las_no > count($p_node)) ) {
                    continue;
                }

                $log_arr=$save_log_arr;
                $count=0;
                $log_index=0;
                $temp_count=0;

                foreach ( $p_node as $key => $value ) {
                    $count++;
                    if($count < $pos_num - $pre_no) {
                        continue;
                    }
                    if($count > $pos_num+$las_no) {
                        continue;
                    }
                    if($count==$pos_num) {
                        
                        $log_index=$temp_count-1; #$temp_count==0? 0 : $temp_count-1;
                        continue;
                    }
                    if($value instanceof PHPParser_Node) {
                        #$log_arr[]=$value->getType();   
                        if($value instanceof PHPParser_Node_Stmt_Return && $value->expr instanceof PHPParser_Node) {
                            $log_arr[]=$value->getType() . '--' . $value->expr->getType();
                        } else {
                            $log_arr[]=$value->getType();
                        }
                    }
                    elseif(is_array($value)) {
                        $log_arr[]="array";
                    }
                    else {
                        $log_arr[]=" ";
                    }
                    $temp_count++;
                    $log_arr[]="##";
                }
                $log_arr[]="$$";
                $log_arr[]="log_count:";
                $log_arr[]=$count;

                $log_arr[]="$$";
                $log_arr[]="log_pos:";
                $log_arr[]=$pos_num;

                $log_arr[]="$$";
                $log_arr[]="log_index:";
                $log_arr[]=$log_index;
                
                $log_arr[]="$$";
                $log_arr[]="log_score:";
                $log_arr[]=$temp_count;

                #print implode('',$log_arr)."<br>";
                if($temp_count > 0) {
                    array_pop($log_arr);
                    $log_arr[]=$temp_count + $gp_arr[1] + $p_arr[1];
                    $this->write_log_pattern_2_file($log_arr);
                }
            }
        }
    	return true;
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

    public function get_log_pattern($pnode,$ptype_arr) {
        
        if($pnode instanceof PHPParser_Node) {
            $ptype_arr[]=array($pnode->getType(),$this->get_node_len($pnode));
        }

    	$pos_num=0;
    	foreach ( $pnode as $key => $value ) {
    		$pos_num++;
        	if ($value !== null && $value !== false && $value !== true && $value instanceof PHPParser_Node) {
        		if($value->getType()=='Expr_FuncCall') {
        			#if(strcmp($value->name->parts[0] ,"Dapper_Log")==0) {
                    if(preg_match("/log/i", $value->name->parts[0])) {
        			   #print $value->getLine() . "<br>";
        			   $this->find_log_pattern($pnode,$pos_num,$value->getLine(),$ptype_arr);
        			}
        		}
        		if($value->getType()=='Expr_StaticCall') {
        			#if(strcmp($value->class->parts[0] ,"Dapper_Log")==0) {
                    if(preg_match("/log/i", $value->class->parts[0])) {
        			   #print $value->getLine() . "<br>";
        			   $this->find_log_pattern($pnode,$pos_num,$value->getLine(),$ptype_arr);
        			}
        		}
        	}
       	}													
        if (!($pnode instanceof PHPParser_Node || is_array($pnode) )) {
   
            throw new InvalidArgumentException('Can only dump nodes and arrays.');
        }

        foreach ($pnode as $key => $value) {
            if (null === $value || false === $value || true === $value || is_scalar($value)) {
                continue;
            }
            
            $this->get_log_pattern($value,$ptype_arr);
        }
        return true;
    }
    
    public function is_in_origin_line($sug_line) {
    	foreach($this->gorigin_log_line as $key => $line) {
    		if(abs($line - $sug_line) < 2) {
    			return true;
    		}
    	}
    	return false;
    }
    
    public function sort_pos2line() {
    	foreach($this->glog_line2sug_line as $log_line => $value) {
    		foreach($value as $sug_line => $sug_count) {
    			if($sug_line!="count") {
    				$this->glog_line2sug_line[$log_line][$sug_line]=$sug_count*1000.0/$this->glog_line2sug_line[$log_line]["count"];
    				if(isset($this->gsug_line[$sug_line])) {
    					$this->gsug_line[$sug_line]+=$this->glog_line2sug_line[$log_line][$sug_line];
    				} else {
    					$this->gsug_line[$sug_line]=$this->glog_line2sug_line[$log_line][$sug_line];
    				}
    				$this->gsug_line[$sug_line]=(int)$this->gsug_line[$sug_line];
    			}
    		}
    	}
    	print (implode(',',$this->gorigin_log_line)) . "<br>";
    	foreach($this->gsug_line as $key => $value) {
    		#print $key . "-->" . $value . "<br>";
    		if($this->is_in_origin_line($key)) {
    			continue;
    		}
    		$this->gfinal_line_score[]=array($key,$value);
    	}
    	usort($this->gfinal_line_score,"recmp");
    	foreach($this->gfinal_line_score as $key => $value) {
    		print $value[0] . "-->" . $value[1] . "<br>";
    	}
    }
}