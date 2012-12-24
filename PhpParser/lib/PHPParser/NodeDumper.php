<?php
function cmp($a,$b) {
	if($a[1]==$b[1]) {
		if($a[0]==$b[0]) {
			return 0;
		}
		return $a[0] < $b[0] ? -1:1;
	}
	return $a[1] < $b[1] ? 1:-1;
}

class PHPParser_NodeDumper
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
    public function filt_0(&$arr) {
        if(!is_array($arr)) {
            return false;
        }
        if(isset($arr[0]) && count($arr)==1) {
            $arr=$arr[0];
            return true;;
        }
        if(isset($arr['array']) && count($arr)==1) {
            $arr = $arr['array'];
            return true;
        }
        $ret=false;

        foreach ($arr as $key => $value) {
            # code.
            if( $this->filt_0($arr[$key]) ) {
                $ret=true;
            }

        }
        return $ret;
    }
    public function dump_array($node) {
        $arr = $this->get_dump_array($node);
        while($this->filt_0($arr)) {

        }
        return $arr;
    }
    public function get_dump_array($node) {
        $arr=array();

        $pre_key;
        if ($node instanceof PHPParser_Node) {
            #$r = $node->getType() . '(';
            #$arr[$node->getType()];
            $pre_key = $node->getType();
        } elseif (is_array($node)) {
            #$r = 'array(';
            #$arr['array'];
            $pre_key = 'array';
        } else {
            throw new InvalidArgumentException('Can only dump nodes and arrays.');
        }

        foreach ($node as $key => $value) {
            #$r .= "\n" . '    ' . $key . ': ';
            if (null === $value) {
            #    $r .= 'null';
                $arr[$pre_key][$key][] = 'null';

            } elseif (false === $value) {
            #    $r .= 'false';
                $arr[$pre_key][$key][] = 'false';
            } elseif (true === $value) {
            #    $r .= 'true';
                $arr[$pre_key][$key][] = 'true';
            } elseif (is_scalar($value)) {
            #    $r .= $value;
                $arr[$pre_key][$key][] = $value;
            } else {
            #    $r .= str_replace("\n", "\n" . '    ', $this->dump($value));
                #print_r($this->dump_array($value));
                $arr[$pre_key][$key][]=$this->dump_array($value);
            }
        }

        return $arr;
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
    
    public function first_find_log_pattern($root,$node) {
    	$flag=-1;
    	if($root instanceof PHPParser_Node && $node instanceof PHPParser_Node ) {
    		if($root->getType() == $node->getType() && sizeof($root)==sizeof($node)){
    			$flag=0;
    		}
    	} elseif (is_array($root) && is_array($node)) {
           if(sizeof($root) == sizeof($node)) {
           	     $flag=1;  
           }
        } else {
            //throw new InvalidArgumentException('Can only dump nodes and arrays.');
        }
        if($flag==1) {
        	$root_len=sizeof($root);
        	$node_len=sizeof($root);
        	for($i=0; $i<$root_len; $i++) {
        		#var_dump($root[$i]);
        		#var_dump($node[$i]);
        		#$root[$i] . "--" .$node[$i] . "<br>";
        		if($root[$i] != $node[$i]) {
        			break;
        		}
        	}
        	if($i == $root_len) {
        		print "find a log pattern!<br>";
        	}
        }
        else if($flag==0) {
        	$i=0;
        	$j=0;
        	foreach($root as $root_key => $root_value) {
        		$i++;
        		$j=0;
        		foreach($node as $node_key => $node_value) {
        		  $j++;
        		}
        	}
        }
        $r='';
        foreach ($root as $key => $value) {
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
                $r .= str_replace("\n", "\n" . '    ', $this->find_log_pattern($value,$node));
            }
        }
        return $r . "\n" . ')';
    }

    public function match_log_pattern($root,$node,$pos_num) {
    	$is_parent_match = false;
    	if($root instanceof PHPParser_Node && $node instanceof PHPParser_Node ) {
	    	if($root->getType() == $node->getType()){
		    	if(abs(count($root) - count($node)) < 5) {
		    		
			    	$is_parent_match = true;
		    	}
	    	}
    	} elseif (is_array($root) && is_array($node)) {
	    	if(abs(count($root) - count($node)) < 5) {
		    	
		    	$is_parent_match = true;
	    	}
    	} 
    	
    	$log_sug_pos_arr=array();
    	if($is_parent_match == true) {
    		
    		$count=0;
    		$log_arr=array();
    		$log_index=0;
    		$log_line=0;
    		$stmt_return_count=0;
    		foreach($node as $key => $value) {
    			$count++;
    			if($count < $pos_num-3) {
    				continue;
    			}
    			if($count > $pos_num+3) {
    				break;
    			}
    			if($count==$pos_num) {
    				#$log_arr[]="match"
    				
    				$log_index=count($log_arr)==0? 0 : count($log_arr)-1;
    				$log_line=$value->getLastLine();
    				#print "..............${log_index}<br> ";
    				continue;
    			}
    			if($value instanceof PHPParser_Node) {
    				$log_arr[]=$value->getType();	
    				if($value instanceof PHPParser_Node_Stmt_Return) {
    					$log_arr[]=$value->expr->name;
    					$stmt_return_count++;
    					#print "sssssssssss!!!!!!!!!!" . $value->expr->name . "<br> ";
    				}
    			}
    			elseif(is_array($value)) {
    				$log_arr[]="array";
    			}
    			else {
    				$log_arr[]="";
    			}
    		}
    		print (implode('',$log_arr)) . "<br>";
    		if(count($root) >= (count($log_arr) - $stmt_return_count)) {
	    		
	    		$match_arr=array();
	    		$match_line=array();
	    		foreach($root as $key => $value) {
		    		if($value instanceof PHPParser_Node) {
			    		$match_arr[]=$value->getType();
			    		if($value instanceof PHPParser_Node_Stmt_Return) {
				    		$match_arr[]=$value->expr->name;
				    		$match_line[]=$value->getLastLine();
			    		}
		    		}
		    		elseif(is_array($value)) {
			    		$match_arr[]="array";
		    		}
		    		else{
			    		$match_arr[]="";
		    		}
		    		
		    		$match_line[]=$value->getLastLine(); 
		    		
	    		}
    			
    			$len_match_arr=count($match_arr);
    			$len_log_arr=count($log_arr);
    			
    			for($i=0; $i<$len_match_arr - $len_log_arr + 1; $i++) {
    				
    				$sub_match_arr=array_slice($match_arr, $i, $len_log_arr);
    				$check_result = array_diff_assoc($log_arr, $sub_match_arr);
    				
    				if(count($check_result) == 0 ) {
    					
    					# find one match point then give some information

    					$sub_match_line=array_slice($match_line,$i,$len_log_arr);
    					if(abs($log_line - $sub_match_line[$log_index]) > 2) {
    						
	    					$log_sug_pos_arr[]=$sub_match_line[$log_index];
	    					print "find +++ goog goog one check point!!!!!!!!!!! <br>";
	    					print "may be you can insert some log after line:" . $sub_match_line[$log_index] . "<br>";
    					}
                    }
    			}
    			
//    			if(count($log_arr) > $log_index+1) {
//	    			
//	    			$log_arr=array_slice($log_arr, 0, $log_index+1);
//	    			
//	    			$len_match_arr=count($match_arr);
//	    			$len_log_arr=count($log_arr);
//	    			
//	    			for($i=0; $i<$len_match_arr - $len_log_arr + 1; $i++) {
//		    			
//		    			$sub_match_arr=array_slice($match_arr, $i, $len_log_arr);
//		    			$check_result = array_diff_assoc($log_arr, $sub_match_arr);
//		    			
//		    			if(count($check_result) == 0 ) {
//			    			
//			    			# find one match point then give some information
//			    			$sub_match_line=array_slice($match_line,$i,$len_log_arr);
//			    			
//			    			print "find --- goog one check point!!!!!!!!!!! <br> ";
//			    			print "may be you can insert some log after line:" . $sub_match_line[$log_index] . "<br>";
//		    			}
//	    			}	
//    			}				
    		}
    	}
    	return $log_sug_pos_arr;
    }
    public function find_log_pattern($root,$node,$parent_node,$pos_num,$log_line) {
    	
    	$is_parent_match = false;
    	if($root instanceof PHPParser_Node && $parent_node instanceof PHPParser_Node ) {
    		if($root->getType() == $parent_node->getType()){
    			$is_parent_match = true;
    		}
    	} elseif (is_array($root) && is_array($parent_node)) {
           $is_parent_match = true;
        } else {
        	#throw new InvalidArgumentException('Can only find_log pattern in nodes and arrays.');
        	$is_parent_match = false;
        }
        
    	if($is_parent_match == true) {
    		
    		foreach ($root as $key => $value) {
    			$res_log_sug_pos_arr=$this->match_log_pattern($value,$node,$pos_num);
    			
    			foreach($res_log_sug_pos_arr as $index => $line_no) {
    				if(isset($this->glog_line2sug_line[$log_line]["count"])) {
    					$this->glog_line2sug_line[$log_line]["count"]++;
    				} else {
    					$this->glog_line2sug_line[$log_line]["count"]=1;
    				}
    				
    				if(isset($this->glog_line2sug_line[$log_line][$line_no])) {
    					$this->glog_line2sug_line[$log_line][$line_no]++;
    				} else {
    					$this->glog_line2sug_line[$log_line][$line_no]=1;
    				}
    			}
    			
    		}
    		#print $log_line;
    		#print_r($this->glog_line2sug_line);
    	}
    	
    	foreach ($root as $key => $value) {
	    	if (null === $value || false === $value || true === $value || is_scalar($value)) {
		    	continue;
	    	} 
	    	$this->find_log_pattern($value,$node,$parent_node,$pos_num,$log_line);
    	}
    	
    	return true;
    }    
    public function get_log_pattern($node,$parent_node) {
    	$pos_num=0;
    	foreach ( $node as $key => $value ) {
    		$pos_num++;
        	if ($value !== null && $value !== false && $value !== true && $value instanceof PHPParser_Node) {
        		if($value->getType()=='Expr_FuncCall') {
        			if(strcmp($value->name->parts[0] ,"Dapper_Log")==0) {
        			   print $value->getLine() . "<br>";
        			   #dump_child($node,1);
        			   if ($parent_node instanceof PHPParser_Node) {
	        			   $r =$parent_node->getType() . '(';
        			   } elseif (is_array($parent_node)) {
	        			   $r = '(' ;
        			   } else {
	        			   $r="";
	        			   //  throw new InvalidArgumentException('Can only dump nodes and arrays.');
        			   }								
        			   print $r . $this->dump_child($node,$parent_node,1,20,$pos_num) . ")<br>";
        			   #echo '<pre>' . htmlspecialchars($this->dump_child($node,$parent_node,1,20,$pos_num)) . '</pre>';
        			   $this->find_log_pattern($this->get_root(),$node,$parent_node,$pos_num,$value->getLine());
        			  $this->gorigin_log_line[]=$value->getLine();
        			}
        		}
        		if($value->getType()=='Expr_StaticCall') {
        			if(strcmp($value->class->parts[0] ,"Dapper_Log")==0) {
        			   print $value->getLine() . "<br>";
        			   #dump_child($node,1);
        			   if ($parent_node instanceof PHPParser_Node) {
	        			   $r =$parent_node->getType() . '(';
        			   } elseif (is_array($parent_node)) {
	        			   $r = '(' ;
        			   } else {
	        			   $r="";
	        			   //  throw new InvalidArgumentException('Can only dump nodes and arrays.');
        			   }	
        			   print $r . $this->dump_child($node,$parent_node,1,20,$pos_num) . ")<br>";
        			   #echo '<pre>' . htmlspecialchars($this->dump_child($node,$parent_node,1,20,$pos_num)) . '</pre>';
        			   $this->find_log_pattern($this->get_root(),$node,$parent_node,$pos_num,$value->getLine());
        			   $this->gorigin_log_line[]=$value->getLine();
        			}
        		}
        	}
       	}													
        if ($node instanceof PHPParser_Node) {
            $r =$node->getType() . '(' . sizeof($node);
        } elseif (is_array($node)) {
            $r = 'array(' . sizeof($node);
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
                $r .= str_replace("\n", "\n" . '    ', $this->get_log_pattern($value,$node));
            }
        }

        return $r . "\n" . ')';
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
    	usort($this->gfinal_line_score,"cmp");
    	foreach($this->gfinal_line_score as $key => $value) {
    		print $value[0] . "-->" . $value[1] . "<br>";
    	}
    }
}