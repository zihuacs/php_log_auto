<?
class PHPParser_ShowSyntaxTree
{
    private $node_dumper;
    
    function __construct() {
        $this->node_dumper = new PHPParser_NodeDumper;
    }

    function show_tree($stmts) {
        if($stmts != NULL) {
            echo '<pre>' . htmlspecialchars($this->node_dumper->dump($stmts)) . '</pre>';
        }
    }

    function get_show_tree_str($stmts) {
    	if($stmts != NULL) {
    		return htmlspecialchars($this->node_dumper->dump($stmts));
    	}
    }

    function get_stmts2array($stmts) {
        if($stmts != NULL) {
            return $this->node_dumper->dump_array($stmts);
        }
    }
}