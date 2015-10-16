<?php
parse_str(implode('&', array_slice($argv, 1)), $_GET);

include 'eiep_files.php';
include 'eiep_helper_functions.php';
include 'settings.php';

if(isset($_GET['type'])){
	$type = $_GET['type'];
}else{
	$type = 'XandR';
}

$start_time = time();
$dbh = new PDO('mysql:host=127.0.0.1;dbname=scm', $user, $password);
$dbh->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );

$errors = fopen('eiep-xandr-errors.txt','a');
$processing_status = fopen('eiep-xandr-status.txt','a');
$timings = fopen('eiep-timings.txt','a');


$eiep1 = new VALIDATE_EIEP1_DET();       	
$eiep3 = new VALIDATE_EIEP3_DET();       	
$list = new VALIDATE_LIST_DET();       	
$input_EIEP1 = new Zend_Filter_Input($eiep1->filters, $eiep1->validators);
$input_EIEP3 = new Zend_Filter_Input($eiep3->filters, $eiep3->validators);
$input_LIST = new Zend_Filter_Input($list->filters, $list->validators);

$count = 0;
$filecount = 0;

if ($type == 'XandR'){
	// Open the text file and get the content
	$xfiles = file('xfiles.txt');
	$rfiles = file('rfiles.txt');
}elseif($type == 'HDR'){
	$hdrfiles = file('hdrfiles.txt');
}elseif($type == 'DET'){
	$detrows = file('../scm/aurora/detrows.txt');
}

if(isset($xfiles)) {
//do  the xfiles and rfiles
	echo "doing the X Files \n";
	fwrite($processing_status,"doing the X Files \n");
	foreach ($xfiles as $file){
		$file = rtrim($file);
		echo $file."\n";
		fwrite($processing_status," $file \n");
		do_X_file($file,$dbh,$input_EIEP1,$input_EIEP3);
	}
}
if(isset($rfiles)){
	echo "doing the R Files  \n";
	fwrite($processing_status,"doing the R Files \n");
	foreach ($rfiles as $file){
		$file = rtrim($file);
		echo $file."\n";
		fwrite($processing_status," $file \n");
		do_R_file($file, $dbh,$input_EIEP1,$input_EIEP3);
	}
}

if(isset($hdrfiles)){
	echo "doing the HDR Files  \n";
	fwrite($processing_status,"doing the HDR Files \n");
	foreach ($rfiles as $file){
		$file = rtrim($file);
		echo $file."\n";
		fwrite($processing_status," $file \n");
		do_R_file($file, $dbh,$input_EIEP1,$input_EIEP3);
	}
}

if(isset($detrows)){
	echo "doing the DET Rows  \n";
	fwrite($processing_status,"doing the DET Files \n");
	foreach ($detrows as $row){
		$row = rtrim($row);		
		$filename = pathinfo($row,PATHINFO_FILENAME);
		echo $filename."\n";
		//find the HDR records in the DB
		$stmt = $dbh->prepare("select * from aurora_files where filename like :filename");
		$stmt->bindValue(':filename', '%'.$filename.'%');
		$stmt->execute();		
		$result = $stmt->fetch(PDO::FETCH_ASSOC);
		var_dump($result);
		
		
		
		//do_DET($file, $dbh,$input_EIEP1,$input_EIEP3);
	}
}



$end_time = time();
$time = $end_time - $start_time;
echo "$filecount files Done, inserted $count records in ", date("h:i:s",$time), " \n";

fwrite ($processing_status, "$filecount files Done, inserted $count records in $time seconds \n");
fwrite ($timings, "$filecount files Done, inserted $count records in $time seconds \n");

fclose($errors);
fclose($processing_status);
fclose($timings);
$dbh = null;




?>