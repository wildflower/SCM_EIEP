<?php
parse_str(implode('&', array_slice($argv, 1)), $_GET);

include 'eiep_files.php';
include 'eiep_helper_functions.php';
include 'settings.php';

$start_time = time();
$dbh = new PDO('mysql:host=127.0.0.1;dbname=scm', $user, $password);
$dbh->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );

$errors = fopen('eiep-errors.txt','a');
$processing_status = fopen('eiep-status.txt','a');
$timings = fopen('eiep-timings.txt','a');


$eiep1 = new VALIDATE_EIEP1_DET();       	
$eiep3 = new VALIDATE_EIEP3_DET();       	
$list = new VALIDATE_LIST_DET();       	
$input_EIEP1 = new Zend_Filter_Input($eiep1->filters, $eiep1->validators);
$input_EIEP3 = new Zend_Filter_Input($eiep3->filters, $eiep3->validators);
$input_LIST = new Zend_Filter_Input($list->filters, $list->validators);

// Open the text file and get the content
$xfiles = file('xfiles.txt');
$rfiles = file('rfiles.txt');
$count = 0;
$filecount = 0;

//do  the xfiles and rfiles
echo "doing the X Files \n";
fwrite($processing_status,"doing the X Files \n");
foreach ($xfiles as $file){
	echo $file."\n";
	fwrite($processing_status," $file \n");
	do_X_file($file,$dbh,$input_EIEP1,$input_EIEP3);
}
echo "doing the R Files  \n";
fwrite($processing_status,"doing the R Files \n");
foreach ($rfiles as $file){
	echo $file."\n";
	fwrite($processing_status," $file \n");
	do_R_file($file, $dbh,$input_EIEP1,$input_EIEP3);
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