<?

class PHPParser_GetLogLine {
	public $log_line_arr;
	private $stmts;
	function __construct($stmts) {
		$this->log_line_arr=array();
		$this->stmts=$stmts;
	}

	public function get_log_line() {
		# clear it
		unset($this->log_line_arr);
		$this->log_line_arr=array();
		# find it
		$this->find_log_line($this->stmts);
		# return it
		return $this->log_line_arr;
	}

	private function find_log_line($node) {					

        if (!($node instanceof PHPParser_Node || is_array($node) )) {
   
            throw new InvalidArgumentException('Can only dump nodes and arrays.');
        }
        if($node instanceof PHPParser_Node) {
	        # if current node wheather is a log node and get it
	        if($node->getType() == 'Expr_FuncCall') {
	        	if (preg_match("/log/i", $node->name->parts[0])) {
	        		# insert it 
	        		$this->log_line_arr[]=$node->getLine();
	        	}
	        }
	        if($node->getType() == 'Expr_StaticCall') {
	        	if ( preg_match("/log/i", $node->class->parts[0])) {
	        		# insert it 
	        		$this->log_line_arr[]=$node->getLine();
	        	}
	        }
        }
        # find it in child node
        foreach ($node as $key => $value) {
            if (null === $value || false === $value || true === $value || is_scalar($value)) {
                continue;
            }
            
            $this->find_log_line($value);
        }
	}
}
