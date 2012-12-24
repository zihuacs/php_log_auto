<?php

class PHPParser_GetFuncInfo
{
    public $methods_node;
    public $methods_name;
    private $stmts;
    private $prettyPrinter;

    private $filt_set;

    function __construct($stmts) {

        $this->stmts         = $stmts;
        $this->methods_node  = array();
        $this->methods_name  = array();
        $this->prettyPrinter = new PHPParser_PrettyPrinter_Zend;
        
        $this->filt_set      = array();
    }

    public function get_func_ship($stmt,$parent_method_name) {
    	$r="";
        if(!($stmt instanceof PHPParser_Node || is_array($stmt))) {
    		return $r;
    	}
    	foreach($stmt as $sub_stmt) {
    		if($sub_stmt instanceof PHPParser_Node_Expr_MethodCall) {
    			//if($sub_stmt->var->name == 'this'){
    				#print $parent_method_name . "++++>" . $sub_stmt->name . "<br>";
                if(!$this->is_filt($sub_stmt->getLine())) {
                    $r .="\n^^^^^|@@-->" . $sub_stmt->var->name ."::". $sub_stmt->name . " <" . $sub_stmt->getLine() . ">" ;
                    $this->filt_set[$sub_stmt->getLine()]=1;
                }
    			//}
    		}
    		elseif($sub_stmt instanceof PHPParser_Node_Expr_StaticCall) {
    			//if($sub_stmt->class->parts[0] == 'self') {
    				#print $parent_method_name . "--->" . $sub_stmt->name . "<br>";
                if(!$this->is_filt($sub_stmt->getLine())) {
                    $r .="\n^^^^^|@@-->" . $sub_stmt->class->parts[0] . "::" . $sub_stmt->name . " <" . $sub_stmt->getLine() . ">" ;
                    $this->filt_set[$sub_stmt->getLine()]=1;
                }
    			//}
    		}
    		elseif($sub_stmt instanceof PHPParser_Node_Expr_FuncCall) {
    			#print $parent_method_name . "+-+>" .$sub_stmt->name . "<br>";
                if(!$this->is_filt($sub_stmt->getLine())) {
                    $r .="\n^^^^^|@--->" . $sub_stmt->name . " <" . $sub_stmt->getLine() . ">" ;
                    $this->filt_set[$sub_stmt->getLine()]=1;
                }
    		}
    		$r .= $this->get_func_ship($sub_stmt,$parent_method_name);
    	}
        return $r;
    }
    
    public function is_filt($node) {
        if( !is_scalar($node->name) && !is_scalar($node->name->parts[0])) 
        { 
            return true;
        }
        if( isset($this->filt_set[$node->getLine()])   ) {
            return true;
        }
        return false;
    }
    public function get_node_str($node) {
        $r="";
        if(!($node instanceof PHPParser_Node || is_array($node))) {
            return $r;
        }
        foreach ($node as $key => $value) {
            # code...
            if(is_scalar($value) && ($key == 'name' || $key == 'value') ) {
                $r .= $value;
            } 
            $r .= $this->get_node_str($value);

        }
        return $r;
    }
    public function get_func_info($node,$dep) {
        #print $dep . "<br>";
    	$r='';
        $pre_space="";
        for ($i=0; $i < $dep; $i++) { 
            # code...
            $pre_space .= '.....';
        }
        $r ="\n" . $pre_space;

        #print_r($this->filt_set);
        try {
            if($node instanceof PHPParser_Node_Stmt_Function || $node instanceof PHPParser_Node_Stmt_ClassMethod ) {
                #print $node->name . $node->getLine() . $this->cal_node_complexity($node) . "<br><br>";
                if(!$this->is_filt($node)) {
                    $r .= "|+---" . $node->name . " <" . $node->getLine() . ">    ";  
                    $r .=  $this->cal_node_complexity($node) ;
                    $this->filt_set[$node->getLine()]=1;
                }
                $dep++;
                #$r .=  $this->get_func_ship($node,$node->name);
            }
            elseif($node instanceof PHPParser_Node_Stmt_Class) {
                #print $node->name . "<br>";
                if(!$this->is_filt($node)) {
                    $r .= "|----" . $node->name . "(class)";
                    $this->filt_set[$node->getLine()]=1;
                }

        		// $methods=$node->getMethods();
        		// foreach($methods as $stmt) {
        		// 	#print $stmt->name . ' ' . $this->cal_node_complexity($stmt) . "<br><br>";
          //           #print $this->prettyPrinter->pStmt_ClassMethod_Name($stmt) . '<br><br>';
          //           if(!$this->is_filt($stmt->getLine())) { 
          //              $r .=  "\n^^^^^|+---" .  $stmt->name . " <" . $stmt->getLine() . ">    ";
          //               $r .=  $this->cal_node_complexity($stmt);
          //               $this->filt_set[$stmt->getLine()]=1;
          //           }
          //           $r .=  str_replace("\n", "\n" . '^^^^^',$this->get_func_ship($stmt,$stmt->name));
        		// }
                $dep++;
        	}  else {
                if($node instanceof PHPParser_Node_Expr_MethodCall) {
                    //if($node->var->name == 'this'){
                        #print $parent_method_name . "++++>" . $node->name . "<br>";
                    if(!$this->is_filt($node)) {
                        
                        $red_flag=false;
                        if(preg_match("/log/i",$node->var->name) || preg_match("/log/i", $node->name)) {
                            $r .= '<font color="red">';
                            $red_flag=true;
                        }  

                        $r .="|@@-->" . $node->var->name ."::". $node->name . " <" . $node->getLine() . ">" ;
                        $this->filt_set[$node->getLine()]=1;

                        if($red_flag) {
                            $r .= "</font>";
                        }
                    }
                    //}
                }
                elseif($node instanceof PHPParser_Node_Expr_StaticCall) {
                    //if($node->class->parts[0] == 'self') {
                        #print $parent_method_name . "--->" . $node->name . "<br>";
                    if(!$this->is_filt($node)) {
                        $red_flag=false;
                        if(preg_match("/log/i",$node->class->parts[0]) || preg_match("/log/i", $node->name)) {
                            $r .= '<font color="red">';
                            $red_flag=true;
                        } 

                        $r .="|@@-->" . $node->class->parts[0] . "::" . $node->name . " <" . $node->getLine() . ">" ;
                        $this->filt_set[$node->getLine()]=1;

                        if($red_flag) {
                            $r .= "</font>";
                        }
                    }
                    //}
                }
                elseif($node instanceof PHPParser_Node_Expr_FuncCall) {
                    #print $parent_method_name . "+-+>" .$node->name . "<br>";
                    if(!$this->is_filt($node)) {

                        $red_flag=false;
                        if(preg_match("/log/i", $node->name)) {
                            $r .= '<font color="red">';
                            $red_flag=true;
                        } 

                        $r .="|@--->" . $node->name . " <" . $node->getLine() . ">" ;
                        $this->filt_set[$node->getLine()]=1;

                        if($red_flag) {
                            $r .= "</font>";
                        }                      
                    }
                }
            }
        } catch (Exception $e) { print $e; }

        if($r == ("\n" . $pre_space)) { $r='';}
        foreach ($node as $key => $value) {
            if (null === $value || false === $value || true === $value || is_scalar($value)) {
               continue;
            } 
            #$this->get_func_info($value);
            #$r .= str_replace("\n", "\n" . '^^^^^', $this->get_func_info($value,$dep));
            $r .= $this->get_func_info($value,$dep);
        }
        if($r == ("\n" . $pre_space)) {  return '';}
        return $r ;
    }
    # 深入叶子节点
    public function cal_node_complexity($node) {
        $len_num=0;
        foreach ($node as $key => $value) {
            if (null === $value || false === $value || true === $value || is_scalar($value)) {
                    continue;
            }
            if($value instanceof PHPParser_Node) {
                $len_num++;
                #continue;
            }   
            $len_num += $this->cal_node_complexity($value);  
        }
        return $len_num;
    }
}