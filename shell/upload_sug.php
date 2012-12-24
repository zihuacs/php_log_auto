<?php
 @header('Content-type: text/html;charset=UTF-8');
session_start();

function file_extend($file_name) {
	$extend =explode("." , $file_name);
	$va=count($extend)-1;
	return $extend[$va];
}

if ($_FILES["file"]["error"] > 0) {
	$_SESSION["upload_err"] = "You Not Select File" . "<br />";
	echo '<script>location.href="index.php"</script>';
}
else {
	// echo "Upload: " . $_FILES["file"]["name"] . "<br />";
	// echo "Type: " . $_FILES["file"]["type"] . "<br />";
	// echo "Size: " . ($_FILES["file"]["size"] / 1024) . " Kb<br />";
	// echo "Stored in: " . $_FILES["file"]["tmp_name"] . "<br />";

	if(file_extend($_FILES["file"]["name"]) == "php") {

		// $file_md5=md5_file($_FILES['file']['tmp_name']);
		// echo $file_md5;

		// $file_name_and_md5=basename($_FILES['file']['name'])+"_md5_" + $file_md5 +".php";

		if(file_exists("data/log_sug_temp/" . $_FILES["file"]["name"])) {
			#echo $_FILES["file"]["name"] . " already exists.";
		}
		else {
			move_uploaded_file($_FILES["file"]["tmp_name"],
				"../data/log_sug_temp/" . $_FILES["file"]["name"]);
			#echo "Stored in: " . "../data/log_sug_temp/" . $_FILES["file"]["name"] . "<br />";
		}
		// $build_log_sug = new BuildLogSug('../data/log_pattern/pattern');
		// $build_log_sug->start_build('../data/log_sug_temp/' . $_FILES["file"]["name"]);
		
		$_SESSION['php_src'] =  $_FILES["file"]["name"];
		echo '<script>location.href="build_log_sug.php"</script>';
	}
	else {
		#echo $_FILES["file"]["name"] . " is not a php file." . "<br />";
		$_SESSION["upload_err"] = "Your File(" . $_FILES["file"]["name"] . ") Suffix Is Not Php" . "<br />";
		echo '<script>location.href="index.php"</script>';
	}
}
