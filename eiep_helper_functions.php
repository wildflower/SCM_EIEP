<?php

function detectDelimiter($handle) {
    if ($handle) {
        $line=fgets($handle, 96);
        rewind($handle);            

        $test=explode(',', $line);
        if (count($test)>1) return ',';

        $test=explode("\t", $line);
        if (count($test)>1) return "\t";

        //.. and so on
    }
    //return default delimiter
    return ',';
}


function project_table($HDR){
// return eiep1 or eiep3
echo "Project table \n";
$class = stristr(strtolower(get_class($HDR)),'_',TRUE);

	if ( $HDR->sender == "HAWK" && ($HDR->recipient == "MERI" ||  $HDR->recipient == "PUNZ"))
	{
		$table = 'unison_invoice';
	}elseif($HDR->filetype == "ICPLIST"){
		$table = get_project($HDR->recipient)."registry";
	}
	else{
	//$table = get_project($HDR->recipient).$class."_staging";
	$table = get_project($HDR->recipient).$class;
	
	}
return $table;
}

function get_project($recipient){
echo "Recipient is : $recipient \n" ;

	switch($recipient){
		case 'HEDL':
			$table = 'sunrise_';
			break;
		case 'WAIP':
			$table = 'champion_';
			break;		
		case 'HAWK':
			$table = 'unison_';
			break;
		case 'ELEC':
			$table = 'electra_';
			break;
		case 'NPOW':
			$table = 'northpower_';
			break;
		case 'DUNW':
			$table = 'aurora_';
			break;
		case 'DUNE':
			$table = 'aurora_';
			break;			
		default:
			$table = 'sunrise_';
	}
return $table;
}

function get_valid_HDR($lineDetails,$filename){
	switch ($lineDetails[1]) {
	
	case  "ICPHH":
		$HDR = new EIEP3_HDR($lineDetails);		
		break;
	case  "ICPMMAB":
	case  "ICPMMRM":
	case  "ICPMMNM":
	case  "SUMMMAB":
	case  "SUMAB":
	case  "SUMMMNM":
	case  "SUMNM":  
	case  "ICPHHAB":
		$HDR = new EIEP1_HDR($lineDetails,$filename);							
		break;
	case  "RSICPLIST":
		$HDR = new LIST_HDR($lineDetails);							
		break;
	default:
		echo "function get_valid_HDR can't tell what file this is by the HDR: $lineDetails[1]) \n";	
		exit();
	}
	if($HDR->validate()){
		return $HDR;	
	}else{
		
		return null;
	}
}


function validateLineCount($HDR,$filename){
global $errors;
global $processing_status;
global $delimiter;
$linecounthandle   = fopen($filename, 'r');
$linecount = 0;
	while (($linecountdetails = fgetcsv($linecounthandle, 1000, $delimiter)) !== FALSE) {
			if (strtoupper($linecountdetails [0]) == 'DET'){
				$linecount++;
			}
		}
		
		if ($HDR->numberOfDetailRecords  != ($linecount)){
			echo "Wrong HDR Line Count $HDR->numberOfDetailRecords for $linecount $filename \n";
			fwrite($errors, "Wrong HDR Line Count $HDR->numberOfDetailRecords for $linecount $filename \n");
			fwrite($processing_status, "Wrong HDR Line Count $HDR->numberOfDetailRecords for $linecount $filename \n");
			$HDR->lineCountIsValid = 0;
			return 1;			
		}
	echo "HDR says $HDR->numberOfDetailRecords and $linecount found for $filename \n";
	$HDR->lineCountIsValid = 1;
fclose($linecounthandle);
}

function validateFilename($HDR,$FILE)
{
	global $errors;
	global $processing_status;
	if(($HDR->sender != $FILE->from) || ($HDR->recipient != $FILE->to) || ($HDR->filetype != $FILE->filetype)|| ($HDR->reportMonth != $FILE->reportmonth)){
		echo "Filename doesn't validate against HDR record \n";
		fwrite($errors, "Filename doesn't validate against HDR record $filename \n");
		fwrite($processing_status, "Filename doesn't validate against HDR record $filename \n");
	 $HDR->isValidFilename= 0;
	 }
$HDR->isValidFilename = 1;
}


function do_X_file($filename,$dbh,$input_EIEP1,$input_EIEP3){
global $errors;
global $processing_status;
global $count;

if(file_exists($filename))
{
$handle   = fopen($filename, 'r');
}else{
	echo "not there";
	return false;
}
$delimiter = detectDelimiter($handle);

while (($lineDetails = fgetcsv($handle, 1000, $delimiter)) !== FALSE) {
	switch($lineDetails[0]){
	case 'HDR':{
		$HDR = get_valid_HDR($lineDetails,$filename);
		$HDR->fk_files = store_header_details($HDR,$filename);		
		$stmt = get_statement($HDR,$dbh);	
		$project_table = project_table($HDR);		
		break;
		}
	case 'DET':{
		if (strpos(get_class($HDR),'1')){
			$DET = new EIEP1_DET($lineDetails,$HDR->eiepversion);
			$query = "UPDATE $project_table set database_action = 'D' WHERE  icp = '$DET->ICP' and reportmonth = '$DET->reportMonth' and fixedvariable = '$DET->fixedVariable' and retailer = '$HDR->sender'";
			$dbh->exec($query);
			do_DET($HDR,$DET,$input_EIEP1,$stmt);
		}else{
			$DET = new EIEP3_DET($lineDetails);
			$query = "DELETE FROM $project_table WHERE icp = '$DET->ICP'  and reportmonth = '$HDR->reportMonth' and retailer = '$HDR->sender'";
			$dbh->exec($query);
			do_DET($HDR,$DET,$input_EIEP3,$stmt);
		}
		
		$count = $count + 1;
		}	
	}	
}
}


function do_R_file($filename,$dbh,$input_EIEP1,$input_EIEP3){
global $errors;
global $processing_status;
global $count;

if ($filename == '../scm/aurora/eiep-14/CTCT_E_DUNE_ICPMMNM_201404_20140528_1858761745.txt')
{
	echo "here \n";
}
if(file_exists($filename))
{
$handle   = fopen($filename, 'r');
}else{
	echo "not there \n";
	return false;
}

$delimiter = detectDelimiter($handle);
while (($lineDetails = fgetcsv($handle, 1000, $delimiter)) !== FALSE) {
	switch($lineDetails[0]){
	case 'HDR':{
		$HDR = get_valid_HDR($lineDetails,$filename);		
		$project_table = project_table($HDR);
			$dbh->exec("UPDATE $project_table  set database_action = 'D' WHERE reportmonth = '$HDR->reportMonth' and retailer = '$HDR->sender'");
			$HDR->fk_files = store_header_details($HDR,$filename);
			$stmt = get_statement($HDR,$dbh);
			break;
		}
	case 'DET':{
		if (strpos(get_class($HDR),'1')){
			$DET = new EIEP1_DET($lineDetails,$HDR->eiepversion);
			do_DET($HDR,$DET,$input_EIEP1,$stmt);
		}else{
			$DET = new EIEP3_DET($lineDetails);
			do_DET($HDR,$DET,$input_EIEP3,$stmt);
		}	
		
		$count = $count + 1;
		}	
	}	
}

}

function get_statement($HDR,$dbh){
echo "Get (prepare SQL) Statement \n";
	$project_table = project_table($HDR);
	if (strpos(get_class($HDR),'1')){
		$stmt = $dbh->prepare("INSERT INTO $project_table (fileid, icp, startdate,  enddate,  unittype,  units, status,  pricecode,  pricerate,  fixedvariable,  chargeabledays,  charge, register_code,reportmonth, retailer,fileStatus, fk_electra_files,database_action)  VALUES ( :fileid, :ICP, :reportPeriodStartDate, :reportPeriodEndDate,:unitType,:units,:status,:tariffCode,:tariffRate,:fixedVariable,:chargeableDays,:networkCharge,:register_code,:reportMonth,:sender,:fileStatus,:fk_electra_files,:database_action)");
	}elseif(strpos(get_class($HDR),'3')){
		$stmt = $dbh->prepare("INSERT INTO $project_table (fileid, icp, register, readstatus, readdate,  period,  kwh, kvarh, kvah, reportmonth,fileStatus) VALUES ( :fileid, :ICP, :dataStreamIdentifier, :status, :date, :tradingPeriod, :consumption, :reactiveEnergy, :apparentEnergy, :reportMonth,:fileStatus)");			
	}
	else{
		$stmt = $dbh->prepare("INSERT INTO $project_table ( icp) VALUES ( :ICP) on duplicate key update icp = :ICP ");			
	}
	return $stmt;
}

function execute_stmt($HDR,$DET,$stmt){
	
	switch (get_class($HDR))
	{
	case  "EIEP1_HDR":
	$stmt->bindValue(':fileid',$HDR->fileid);
	$stmt->bindValue(':ICP', $DET->ICP);
	$stmt->bindValue(':reportPeriodStartDate', $DET->reportPeriodStartDate);
	$stmt->bindValue(':reportPeriodEndDate', $DET->reportPeriodEndDate);
	$stmt->bindValue(':unitType',$DET->unitType);
	$stmt->bindValue(':units',$DET->units);
	$stmt->bindValue(':status',$DET->status);
	$stmt->bindValue(':tariffCode',$DET->tariffCode);
	$stmt->bindValue(':tariffRate',$DET->tariffRate);
	$stmt->bindValue(':fixedVariable',$DET->fixedVariable);
	$stmt->bindValue(':chargeableDays',$DET->chargeableDays);
	$stmt->bindValue(':networkCharge',$DET->networkCharge);
	$stmt->bindValue(':register_code',$DET->registerContentCode);
	$stmt->bindValue(':reportMonth',$DET->reportMonth);
	$stmt->bindValue(':sender',$HDR->sender);
	$stmt->bindValue(':fileStatus',$HDR->fileStatus);	
	$stmt->bindValue(':fk_electra_files',$HDR->fk_files);	
	$stmt->bindValue(':database_action',$HDR->database_action);	
	break;
	case  "EIEP3_HDR":	
	$stmt->bindValue(':fileid',$HDR->fileid);
	$stmt->bindValue(':ICP', $DET->ICP);
	$stmt->bindValue(':dataStreamIdentifier', $DET->dataStreamIdentifier);
	$stmt->bindValue(':status',$DET->status);
	$stmt->bindValue(':date', $DET->date);
	$stmt->bindValue(':tradingPeriod',$DET->tradingPeriod);
	$stmt->bindValue(':consumption',$DET->consumption);	
	$stmt->bindValue(':reactiveEnergy',$DET->reactiveEnergy);
	$stmt->bindValue(':apparentEnergy',$DET->apparentEnergy);
	$stmt->bindValue(':reportMonth',$HDR->reportMonth);	
	$stmt->bindValue(':fileStatus',$HDR->fileStatus);	
	break;
	case  "LIST_HDR":
	$stmt->bindValue(':ICP',$DET->ICP);	
	break;
	}
	$stmt->execute();
}
function execute_UB_stmt($HDR,$DET,$stmt){
	
	$stmt->bindValue(':fileid',$HDR->fileid);
	$stmt->bindValue(':ICP', $DET->ICP);
	$stmt->bindValue(':reportPeriodStartDate', $HDR->reportPeriodStartDate);
	$stmt->bindValue(':reportPeriodEndDate', $HDR->reportPeriodEndDate);	
	$stmt->bindValue(':unitType',$DET->unitType);
	$stmt->bindValue(':units',$DET->units);
	$stmt->bindValue(':status',$DET->status);
	$stmt->bindValue(':tariffCode',$DET->tariffCode);
	$stmt->bindValue(':tariffRate',$DET->tariffRate);
	$stmt->bindValue(':fixedVariable',$DET->fixedVariable);
	$stmt->bindValue(':chargeableDays',$DET->chargeableDays);
	$stmt->bindValue(':networkCharge',$DET->networkCharge);	
	$stmt->bindValue(':reportMonth',$DET->reportMonth);
	$stmt->bindValue(':sender',$HDR->sender);	
	$stmt->bindValue(':fileStatus',$HDR->fileStatus);	
	$stmt->bindValue(':fk_electra_files',$HDR->fk_files);	

	$stmt->execute();
}

function do_DET($HDR,$DET,$input,$stmt){
global  $processing_status;
global $errors;
global $filename;

	if(!$DET->isUBRecord()){
		//set the $input object to contain a $DET row for validation		
		$input->setData($DET->build_array());
		if($input->isValid()){
		// after validation is called, the $input object has been updated with the applied Zend Filter classes
		// these updated fields need to be recorded in the $DET row and stored
		// this way we are gettign valid/usable data stored in the eiep1 table for analysis
			switch (get_class($HDR) ){
			case "EIEP1_HDR" :			
				$DET->reportPeriodStartDate = implode('-', array_reverse(explode('/', $input->reportPeriodStartDate)));
				$DET->reportPeriodEndDate = implode('-', array_reverse(explode('/', $input->reportPeriodEndDate)));
				$DET->tariffRate = $input->tariffRate;
				$DET->units = $input->units;			
				$DET->unitType = $input->unitType;	
				$DET->networkCharge = $input->networkCharge;
				$DET->chargeableDays = $input->chargeableDays;								
				break;
			case  "EIEP3_HDR":
				$DET->date = implode('-', array_reverse(explode('/', $input->date)));
				$DET->tariffRate = $input->tariffRate;
				$DET->units = $input->units;			
				$DET->networkCharge = $input->networkCharge;
				break;			
			case "LIST_HDR":
				break;
			}
			execute_stmt($HDR,$DET,$stmt);
		} elseif ($input->hasInvalid() || $input->hasMissing()) {
			//write out invalid line	
			$messages = $input->getMessages();
			$invalid_fields = array_keys($messages);
			echo "Error on $input->ICP line: ".count($messages)." fields invalid  \n";
			fwrite($processing_status ,"Error on $input->ICP line: ".count($messages)." fields invalid  \n");
			foreach($messages as $key => $value){
				//$keys = array_keys($messages);
				echo "Field $key has failed ".count($value)." tests  \n";
				var_dump($messages);
				fwrite($processing_status ,"Field $key has failed ".count($value)." tests  \n");
			}				
			fwrite($errors,"# $filename \n");
			$HDR->write($errors);
			$DET->write($errors);
			
			fwrite($processing_status ,"# $filename \n");
			$DET->write($processing_status );					
		}
	}else{
		execute_UB_stmt($HDR,$DET,$stmt);
	}
}


function store_header_details($HDR,$filename){
global $dbh;
$FILE = new EIEP_Filename(strtoupper(basename($filename)));
validateFilename($HDR,$FILE);
echo "Store headers \n";
$project_table = get_project($HDR->recipient)."files";

echo "Line count value ".$HDR->lineCountIsValid." \n";

var_dump($HDR);
$stmt = $dbh->prepare("INSERT INTO $project_table (filetype, sender, onbehalfsender, recipient, reportRunDate,  reportRunTime, fileid, numberOfRecords,reportPeriodStart,     reportPeriodEnd,     reportMonth,   utilityType,  fileStatus,  filename, isfilenamevalid, islinecountvalid, eiepversion)VALUES ( :filetype,    :sender,    :onbehalfsender,    :recipient,    :reportRunDate,    :reportRunTime,    :fileid,    :numberOfDetailRecords,    :reportPeriodStartDate,    :reportPeriodEndDate,    :reportMonth,    :utilityType, :fileStatus, :filename, :isfilenamevalid, :islinecountvalid, :eiepversion)");

$stmt->bindValue(':filetype',$HDR->filetype);
    $stmt->bindValue(':sender',$HDR->sender);
    $stmt->bindValue(':onbehalfsender',$HDR->onbehalfsender);
    $stmt->bindValue(':recipient',$HDR->recipient);
    $stmt->bindValue(':reportRunDate',$HDR->reportRunDate);
    $stmt->bindValue(':reportRunTime',$HDR->reportRunTime);
    $stmt->bindValue(':fileid',$HDR->fileid);
    $stmt->bindValue(':numberOfDetailRecords',$HDR->numberOfDetailRecords);
    $stmt->bindValue(':reportPeriodStartDate',$HDR->reportPeriodStartDate);
    $stmt->bindValue(':reportPeriodEndDate',$HDR->reportPeriodEndDate);
    $stmt->bindValue(':reportMonth',$HDR->reportMonth);
    $stmt->bindValue(':utilityType',$HDR->utilityType);
    $stmt->bindValue(':fileStatus',$HDR->fileStatus);
    $stmt->bindValue(':filename',$filename);
    $stmt->bindValue(':isfilenamevalid',$HDR->isValidFilename);   
    $stmt->bindValue(':islinecountvalid',$HDR->lineCountIsValid); 
	$stmt->bindValue(':eiepversion',$HDR->eiepversion); 
//var_dump($stmt);
$stmt->execute();	
//print_r($stmt->errorInfo());
//print_r( $stmt->errorCode());
//echo "\n";
//var_dump($dbh->errorInfo());
return $dbh->lastInsertId();

}

?>
