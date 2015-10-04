<?php
parse_str(implode('&', array_slice($argv, 1)), $_GET);

include 'eiep_files.php';
include 'eiep_helper_functions.php';
include 'settings.php';

$start_time = time();
$dbh = new PDO('mysql:host=127.0.0.1;dbname=scm', $user, $password);
$dbh->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );

$errors = fopen('electra-errors.txt','a');
$processing_status = fopen('electra-status.txt','a');
$timings = fopen('eiep-timings.txt','a');


$eiep1 = new VALIDATE_EIEP1_DET();       	
$eiep3 = new VALIDATE_EIEP3_DET();       	
$list = new VALIDATE_LIST_DET();       	
$input_EIEP1 = new Zend_Filter_Input($eiep1->filters, $eiep1->validators);
$input_EIEP3 = new Zend_Filter_Input($eiep3->filters, $eiep3->validators);
$input_LIST = new Zend_Filter_Input($list->filters, $list->validators);

// Open the text file and get the content
$xfiles = array();
$rfiles = array();
$count = 0;
$filecount = 0;


if(isset($_GET['path'])){
	$path = $_GET['path'];
}else{
	$path = '../electra/*/*';
}

$files = glob($path);

echo "Starting to process ". count($files)." files matching $path \n";
fwrite($processing_status,"Starting to process ". count($files)." files matching $path \n");

foreach ($files as $filename) {

$handle   = fopen($filename, 'r');

$filecount++;
 
 $delimiter = detectDelimiter($handle);
 //$delimiter = ',';
 fwrite($processing_status ,"starting $filename with delimiter $delimiter \n");

 
while (($lineDetails = fgetcsv($handle, 1000, $delimiter)) !== FALSE) {
	switch($lineDetails[0]){
	case 'HDR':{
		$HDR = get_valid_HDR($lineDetails,$filename);
		if($HDR == null){
			fwrite($processing_status ,"Validate is null \n");	
			continue 3;
		}
		validateLineCount($HDR,$filename);
		
		fwrite($processing_status ,"After validate line count \n");			    
		
		if(!$HDR->lineCountIsValid)
		{
			break 2;		
		}	
		
		if(($HDR->filetype == "SUMMMAB")||($HDR->filetype == "SUMAB")|| ($HDR->filetype == "SUMMMNM")||($HDR->filetype == "SUMNM")){					
			continue 3;
		}	
		
		if ($HDR->fileStatus == 'X'){
			//write this filename to X array and continue to next file, once the first list is done go through the X array	
			fwrite($processing_status ,"X file found $filename \n");
			$xfiles[] = $filename;	
			break 2;		
		}
		if ($HDR->fileStatus == 'R'){
			fwrite($processing_status ,"R file found $filename \n");
			$rfiles[] = $filename;			
			break 2;			
		}
		//These are the Initial files - fk_files is the foreign key to the $project_file record
		//prepare the insert statement for the type of DET lines to follow
		$HDR->fk_files = store_header_details($HDR,$filename);
		$stmt = get_statement($HDR,$dbh);
		fwrite($processing_status ,"Statment received  \n");			    
	break;
	}
    
       case 'DET':{
    	//should check startdate and enddate are near reportmonth?
	  
		if (strpos(get_class($HDR),'1')){
			$DET = new EIEP1_DET($lineDetails,$HDR->eiepversion);			
			do_DET($HDR,$DET,$input_EIEP1,$stmt);
		}elseif(strpos(get_class($HDR),'3')){
			$DET = new EIEP3_DET($lineDetails);
			do_DET($HDR,$DET,$input_EIEP3,$stmt);
		}else{
			$DET = new LIST_DET($lineDetails);
			do_DET($HDR,$DET,$input_LIST,$stmt);
		}
		
		
		$count = $count + 1;	

		} //DET caase
		
		
	} //Switch
} //While


fclose($handle);
// move finished files out the way - might be moving X and R files

}

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
  // fwrite($out, $lineDetails[1].",".$lineDetails[2].",".$lineDetails[3].",".$lineDetails[4].",".$lineDetails[5].",".$lineDetails[6].",".$lineDetails[7].",".$lineDetails[8].",".$lineDetails[10].",".$lineDetails[11].",".$lineDetails[12].",".$lineDetails[13].",".$lineDetails[14].",".$lineDetails[15].",".$lineDetails[16].",".$lineDetails[17].",".$lineDetails[18].",".$lineDetails[19].",".$lineDetails[20].",".$lineDetails[22].",".$lineDetails[23].",".$lineDetails[24].",".$lineDetails[25].",".$lineDetails[26].",".$lineDetails[27].",".$lineDetails[28].",".$lineDetails[29].",".$lineDetails[30].",".$lineDetails[31].",".$lineDetails[32].",".$lineDetails[33].",".$lineDetails[34].",".$lineDetails[35].",".$lineDetails[36].",".$lineDetails[37].",".$lineDetails[38].",".$lineDetails[39].",".$lineDetails[40].",".$lineDetails[41].",".$lineDetails[42]."\n");


?>
