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
	}elseif($HDR->filetype == "RSICPLIST"){
		$table = get_project($HDR->recipient)."registry";
	}
	else{
	$table = get_project($HDR->recipient).$class."_staging";
	
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
			
		default:
			$table = 'sunrise_';
	}
return $table;
}

function get_valid_HDR($lineDetails){
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
		$HDR = new EIEP1_HDR($lineDetails);							
		break;
	case  "RSICPLIST":
		$HDR = new LIST_HDR($lineDetails);							
		break;
	default:
		"function get_valid_HDR can't tell what file this is by the HDR: $lineDetails[1]) \n";	
		exit();
	}
	$HDR->validate();
return $HDR;	
}


function validateLineCount($HDR,$filename){
global $errors;
global $processing_status;
global $delimiter;
$linecounthandle   = fopen($filename, 'r');
$linecount = 0;
	while (($linecountdetails = fgetcsv($linecounthandle, 1000, $delimiter)) !== FALSE) {
			if ($linecountdetails [0] =='DET'){
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
	if(($HDR->sender != $FILE->from) || ($HDR->recipient != $FILE->to) || ($HDR->filetype != $FILE->filetype)|| ($HDR->reportMonth != $FILE->reportmonth)){
	 echo "Filename doesn't validate against HDR record \n";
	 $HDR->isValidFilename= 0;
	 }
$HDR->isValidFilename = 1;
}


function do_X_file($filename,$dbh,$input_EIEP1,$input_EIEP3){
global $errors;
global $processing_status;
global $count;
global $delimiter;

$handle   = fopen($filename, 'r');
while (($lineDetails = fgetcsv($handle, 1000, $delimiter)) !== FALSE) {
	switch($lineDetails[0]){
	case 'HDR':{
		$HDR = get_valid_HDR($lineDetails);	
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
global $delimiter;

$handle   = fopen($filename, 'r');
while (($lineDetails = fgetcsv($handle, 1000, $delimiter)) !== FALSE) {
	switch($lineDetails[0]){
	case 'HDR':{
		$HDR = get_valid_HDR($lineDetails);
		$project_table = project_table($HDR);
			$dbh->exec("UPDATE $project_table  set database_action = 'D' WHERE reportmonth = '$HDR->reportMonth' and retailer = '$HDR->sender'");
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
	$project_table = project_table($HDR);
	if (strpos(get_class($HDR),'1')){
		$stmt = $dbh->prepare("INSERT INTO $project_table (fileid, icp, startdate,  enddate,  unittype,  units, status,  pricecode,  pricerate,  fixedvariable,  chargeabledays,  charge,  reportmonth, retailer,fileStatus, fk_electra_files,database_action)  VALUES ( :fileid, :ICP, :reportPeriodStartDate, :reportPeriodEndDate,:unitType,:units,:status,:tariffCode,:tariffRate,:fixedVariable,:chargeableDays,:networkCharge,:reportMonth,:sender,:fileStatus,:fk_electra_files,:database_action)");
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
		$input->setData($DET->build_array());
		if($input->isValid()){
			switch (get_class($HDR) ){
			case "EIEP1_HDR" :			
				$DET->reportPeriodStartDate = implode('-', array_reverse(explode('/', $input->reportPeriodStartDate)));
				$DET->reportPeriodEndDate = implode('-', array_reverse(explode('/', $input->reportPeriodEndDate)));
				$DET->tariffRate = $input->tariffRate;
				$DET->units = $input->units;			
				$DET->networkCharge = $input->networkCharge;
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
			//fwrite($errors,"# $filename \n");
			fwrite($processing_status ,"# $filename \n");
			$DET->write($errors);
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
$stmt = $dbh->prepare("INSERT INTO $project_table (filetype, sender, onbehalfsender, recipient, reportRunDate,  reportRunTime, fileid, numberOfRecords,reportPeriodStart,     reportPeriodEnd,     reportMonth,   utilityType,  fileStatus,  filename, isfilenamevalid, islinecountvalid)VALUES ( :filetype,    :sender,    :onbehalfsender,    :recipient,    :reportRunDate,    :reportRunTime,    :fileid,    :numberOfDetailRecords,    :reportPeriodStartDate,    :reportPeriodEndDate,    :reportMonth,    :utilityType, :fileStatus, :filename, :isfilenamevalid, :islinecountvalid)");

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
//var_dump($stmt);
$stmt->execute();
//print_r($stmt->errorInfo());
//print_r( $stmt->errorCode());
//echo "\n";
//var_dump($dbh->errorInfo());
return $dbh->lastInsertId();

}
/*

function get_insert_line($HDR,$DET){
	$project_table = project_table($HDR);
	
	if (strpos(get_class($HDR),'1')){
		$query = "INSERT INTO $project_table (fileid, icp, startdate,  enddate,  unittype,  units, status,  pricecode,  pricerate,  fixedvariable,  chargeabledays,  charge,  reportmonth, retailer)
   VALUES ( '".$HDR->fileid."','".$DET->ICP."','".$DET->reportPeriodStartDate."','".$DET->reportPeriodEndDate."','".$DET->unitType."','".$DET->units."','".$DET->status."','".$DET->tariffCode."','".$DET->tariffRate."','".$DET->fixedVariable."','".$DET->chargeableDays."','".$DET->networkCharge."','".$DET->reportMonth."','".$HDR->sender."');";
	}else{
		$query = "INSERT INTO `$project_table` (fileid, icp, register, readstatus, readdate,  period,  kwh, kvarh, kvah, reportmonth)
   VALUES ( '$HDR->fileid','".$DET->ICP."','".$DET->dataStreamIdentifier ."','".$DET->status."','".$DET->date."','".$DET->tradingPeriod."','".$DET->consumption."','".$DET->reactiveEnergy ."','".$DET->apparentEnergy."','$HDR->reportMonth');";			
	}

	return $query;
}

function get_UB_insert_line($HDR,$DET){
	$project_table = project_table($HDR);
	$query = "INSERT INTO $project_table (fileid, icp, startdate,  enddate, status, reportmonth, retailer)
   VALUES ( '".$HDR->fileid."','".$DET->ICP."','".$HDR->reportPeriodStartDate."','".$HDR->reportPeriodEndDate."','".$DET->status."','".$HDR->reportMonth."','".$HDR->sender."');";
return $query;
}

function get_delete_line($HDR,$DET){
	$project_table = project_table($HDR);
	if (strpos(get_class($HDR),'1')){
		$query = "DELETE FROM $project_table WHERE  icp = '$DET->ICP' and reportmonth = '$DET->reportMonth' and fixedvariable = '$DET->fixedVariable' and retailer = '$HDR->sender'";
	}else{
		$query = "DELETE FROM $project_table WHERE icp = '$DET->ICP'  and reportmonth = '$DET->reportMonth' and retailer = '$HDR->sender'";
	}

	return $query;
}


$stmt->bindParam(':fileid', $fileid);
$stmt->bindParam(':ICP', $ICP);
$stmt->bindParam(':reportPeriodStartDate', $reportPeriodStartDate);
$stmt->bindParam(':reportPeriodEndDate', $reportPeriodEndDate);
$stmt->bindParam(':unitType',$unitType);
$stmt->bindParam(':units',$units);
$stmt->bindParam(':status',$status);
$stmt->bindParam(':tariffCode',$tariffCode);
$stmt->bindParam(':tariffRate',$tariffRate);
$stmt->bindParam(':fixedVariable',$fixedVariable);
$stmt->bindParam(':chargeableDays',$chargeableDays);
$stmt->bindParam(':networkCharge',$networkCharge);
$stmt->bindParam(':reportMonth',$reportMonth);
$stmt->bindParam(':sender',$sender);

			$fileid = $HDR->fileid;
			$ICP = $DET->ICP;
			$reportPeriodStartDate = $DET->reportPeriodStartDate;
			$reportPeriodEndDate = $DET->reportPeriodEndDate;
			$unitType = $DET->unitType;
			$units = $DET->units;
			$status = $DET->status;
			$tariffCode = $DET->tariffCode;
			$tariffRate = $DET->tariffRate;
			$fixedVariable = $DET->fixedVariable;
			$chargeableDays = $DET->chargeableDays;
			$networkCharge = $DET->networkCharge;
			$reportMonth = $DET->reportMonth;
			$sender = $HDR->sender;
			
		//check for a duplicate record before inserting a new one
		//Not always a duplicate more likely a UB record from another Retailer
		$query = "SELECT count(*) FROM $project_table WHERE icp = '".$lineDetails[1]."' and startdate = '".$startdate->format('Y-m-d')."' and enddate = '".$enddate->format('Y-m-d')."' and status = '".$lineDetails[7]."' and pricecode = '".$lineDetails[11] ."' and pricerate = '". $lineDetails[12] ."' and fixedvariable ='".$lineDetails[13] ."' and charge = '". $lineDetails[15] ."' and retailer = '$sender'";
		$result = mysql_query($query);
		// Check result
		// This shows the actual query sent to MySQL, and the error. Useful for debugging.
			if (!$result) {
				$message  = 'Invalid query: ' . mysql_error() . "\n";
				$message .= 'Whole query: ' . $query;
				die($message);
			}		
			
			if(mysql_result($result, 0)){
				echo mysql_result($result, 0). $query."\n";
				fwrite($errors, mysql_result($result, 0). $query."\n");
				//" ICP $lineDetails[1] $reportmonth from $fileid of $filename \n";
				//continue;				
			}

		}	
		
		*/

?>
