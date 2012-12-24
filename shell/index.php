<?
class HomePage {
	private $err_msg;
	function __construct($err_msg=NULL) {
		$this->err_msg = $err_msg;
	}
	public function start_header()
	{
		# code...
		print ' <html>
				<head>
				<title>Log Auto Suggest for Php</title>
				<meta http-equiv="Content-Type" content="text/html; charset=utf8">
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

	}
	public function start_body()
	{
		# code...
		print ' <table width="755" border="0" align="center" cellpadding="0" cellspacing="0"> 
				<tr> 
					<td colspan="2" height="40"></td> 
				</tr> 
				<tr> 
					<td colspan="2" align="center"> 
						<h1>Welcome to Log Auto Suggest for Php!</h1> 
					</td> 
				</tr> ';
		print ' <tr>
				<td colspan="2" height="40" align="center" valign="top"> 
				<hr>
				</td>
				</tr>';

	

		print ' <tr>
				<td colspan="2" height="40" align="center" valign="top">
				<form action="upload_sug.php" method="post"
				enctype="multipart/form-data">
				<label for="file">Filename:</label>
				<input type="file" name="file" id="file" /> 
				<input type="submit" name="submit" value="Submit" />
				</form>
				</td></tr>';

		print ' <tr>
				<td colspan="2" height="40" align="center" valign="top"> 
				<strong><font color="green">Upload Your Php Src File, Try It!</font></strong>
				</td>
				</tr>';	
		if($this->err_msg!=NULL and $this->err_msg!='') {
			print ' <tr>
					<td colspan="2" height="40" align="center" valign="top"> 
					<strong><font color="red">' . $this->err_msg . '</font></strong>
					</td>
					</tr>';	
		}
		print ' <tr> 
				<td colspan="2" height="40" align="center" valign="bottom"> 
					<hr> 
					<span class="copy">MSE_QA 2012 | Zihuacs</span>
				</td>
			    </tr> 
			    </table>';

	    print ' <div style="position:absolute; top:0; left:0;"><a style="text-decoration: none;" href="admin">&nbsp;&nbsp;</a></div>';

	}

	public function start_end()
	{
		# code...
		print ' </body>';
		print '</html>';
	}

	public function start()
	{
		# code...
		$this->start_header();
		$this->start_body();
		$this->start_end();
	}

}
session_start();
if(isset($_SESSION['upload_err'])) {
	$err_msg   =$_SESSION['upload_err'];
	unset($_SESSION['upload_err']);
	$home_page = new HomePage($err_msg);
}
elseif(isset($_SESSION['parser_error'])){
	$err_msg   =$_SESSION['parser_error'];
	unset($_SESSION['parser_error']);
	$home_page = new HomePage($err_msg);
} elseif (isset($_SESSION['no_file_err'])) {
	$err_msg   =$_SESSION['no_file_err'];
	unset($_SESSION['no_file_err']);
	$home_page = new HomePage($err_msg);
} 
else {
	$home_page = new HomePage();
}
$home_page->start();