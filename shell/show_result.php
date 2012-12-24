<?php

class ShowResult {
	private $gfinal_line_score;
	private $gsug_line2log_index;

	private $content_size;
	private $show_page;

	private $start_page;
	private $end_page;
	private $one_page_size=20;

	private $php_src_file;
	function __construct($gfinal_line_score=array(),$gsug_line2log_index=array(),$gorigin_log_line=array(),$show_page=0,$php_src_file='') {
		$this->gfinal_line_score   =$gfinal_line_score;
		$this->gsug_line2log_index = $gsug_line2log_index;
		$this->gorigin_log_line    = $gorigin_log_line;
		$this->show_page           = $show_page;
		$this->php_src_file        = $php_src_file;

		$this->cal_page();
	}
	private function cal_page() {
		$this->content_size = count($this->gfinal_line_score);
		$this->start_page=0;
		$this->end_page = (int)($this->content_size / $this->one_page_size);	 
		if($this->content_size % $this->one_page_size != 0) {
			$this->end_page++;
		}
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
		print '<h1 align="center">Log Auto Suggestion</h1>';

	}
	public function show_begin() {

		print '<table width="750" border="10" align="center" cellpading="10">';
        print '<tbody>';
        print '<tr><td colspan="5" align="center">';
        // print '<input name="keywords" type="text" id="keywords" size="32" maxlength="200">';
        // print '<input name="submit" type="submit" value="Search">';
        print  $this->php_src_file ;
        print '</td></tr>';
	}
	public function show_page_list() {
		print '<tr>';
		print '<td colspan="5">';
		
		if($this->content_size <=0 ) {
			print 'no reuslt';
		} else {
			for ($i=$this->start_page; $i < $this->end_page ; $i++) {
			 	# code...
			 	$url = '?volume=' . $i;
			 	print '<a href="' . $url . '">[' . $i .']</a>';
			}
		}
		print '</td>';
		print '</tr>';
	}
    
    private function show_sug_line2log_index($sug_line) {
        #print $sug_line . "------->" ." ";
        $r="";
        if(isset($this->gsug_line2log_index[$sug_line])) {
            
            $r .= "<a href=\"ShowPattern.php?pattern=";
            $tr = "";
            foreach ($this->gsug_line2log_index[$sug_line] as $log_index => $count) {
                # code...
                #print "(" . $log_index . "--" . $this->g_log_arr[$log_index]["log_line"] . ") ";
                $r .= $log_index  . ",";
                $tr .= $log_index . "(" . $count . ") ";

            }
            $r .= '&line=' . $sug_line . '&usize=20&dsize=10"';
            $r .= ' target="_blank">' . $tr . "</a>";
        }
        return $r;
        #print "<br />";
    }
    
    private function is_origin_log_line($log_line) {
    	foreach ($this->gorigin_log_line as $index => $line) {
    		# code...
    		if($line == $log_line) {
    			return true;
    		}
    	}
    	return false;
    }

    private function show_sug_line($sug_line) {
    	$r='<a href="ShowSrcFile.php#' . ($sug_line) . '" target="_blank"">';
    	if($this->is_origin_log_line($sug_line)) {
    		$r .= '<font color="red">' . $sug_line . "</font>";
    	}
    	else {
    		$r .= $sug_line;
    	}
        $r .= '</a>';
    	return $r;
    }
	public function show_content()
	{
		print '<tr align="center">';
        print '<th width="100">ID</th>';
        print '<th width="100">Line</th>';
        print '<th width="350">Title</th>';
        print '<th width="100">Score</th>';
        print '<th width="100">Good/Bad</th>';
        print '</tr>';
		$ID=-1;
        foreach($this->gfinal_line_score as $key => $value) {
            #print $value[0] . "\t\t\t" . $value[1] . "<br>";
            #printf("%d\t\t\t%f\n",$value[0],$value[1]);
            $ID++;
            if($ID < $this->show_page * $this->one_page_size) {
            	continue;
            }
            if($ID >= ($this->show_page+1) * $this->one_page_size ) {
            	break;
            } 
            print '<tr>';
            print '<td align="left">' . $ID . '</td>';

            print '<td align="left">' . $this->show_sug_line($value[0]) . '</td>';

            print '<td align="left">' . $this->show_sug_line2log_index($value[0]) . '</td>';
            print '<td align="right">' . (int)$value[1] . '</td>';
            print '<td align="right">' . "test" . '</td>';
            print '</tr>';
           
        }
	}
	public function show_end()
	{
		# code...
		print '</tbody>';
        print "</table>";
        print "</body>";
        print '</html>';
	}
	public function start_show() {
		$this->show_header();
        $this->show_begin();
        $this->show_page_list();
        $this->show_content();
        $this->show_end();
	}

}