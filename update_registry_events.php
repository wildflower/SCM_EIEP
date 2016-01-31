<?php
parse_str(implode('&', array_slice($argv, 1)), $_GET);
include 'settings.php';
include 'registry_classes.php';

$start_time = time();
$wsdl = "https://www.electricityregistry.co.nz/bin_public/Jadehttp.dll?WebService&listName=WSP_Registry&serviceName=WSRegistry&wsdl=wsdl";
$wsdl = "https://www.electricityregistry.co.nz/bin_public/Jadehttp.dll?WebService&serviceName=WSRegistry&listName=WSP_Registry2&wsdl=wsdl";
$client = new SoapClient($wsdl,array('trace'=>1));

if(isset($_GET['project'])){
	$project = $_GET['project'];
}else
{
	echo "I need a project name \n";
	exit;
}

$Project = new ftl_project($project);

echo "Working with $Project->project_name \n";

$errors = fopen('registry_updates.txt','a');

$options = array(
    PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'
);
$dbh = new PDO($dsn, $user, $password, $options);
$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

if(isset($_GET['dataset'])){
	$dataset = $_GET['dataset'];	
}else{
	echo "dataset is all \n";
	$dataset = 'all';
}

if ($dataset == 'all'){
	$query = "select icp from $Project->project_table";
}elseif ($dataset == 'new'){
	$query = "select icp from $Project->project_table where icpcreationdate is null";
	$query = "select icp from buller_registry where icp not in (select distinct icp from registry_events)";
}elseif ($dataset == 'specific'){
	$query = "select icp from $Project->project_table where icp in ('0001941560ALFC6')" ;
}else{
	$query = "select distinct icp from icpincident" ;
}

//$query = "select icp from electra_registry where icp not in (select distinct icp from registry_events)";

		
$sth = $dbh->prepare($query);
$sth->execute();

//var_dump($client->__getFunctions());

$icp_count =  0;
$event_count = 0;

 //setup the insert statment and params from update_record

 $update_record = new scmEvent();

 $query =  "insert into registry_events (icp, creationDate, eventDataSummary,eventDate, eventType,isReplaced,isReversed,reverseReplaceDate) VALUES ( :icp, :creationDate, :eventDataSummary, :eventDate, :eventType,:isReplaced,:isReversed,:reverseReplaceDate)";
			//echo "$query \n"; 
			$insert_events = $dbh->prepare($query);
			if (!$insert_events) {
			echo "\nPDO::errorInfo():\n";
			print_r($dbh->errorInfo());
		}
	$insert_events->bindParam(':icp', $update_record->icp);
			$insert_events->bindParam(':creationDate', $update_record->creationDate);
			$insert_events->bindParam(':eventDataSummary', $update_record->eventDataSummary);
			$insert_events->bindParam(':eventDate', $update_record->eventDate);
			$insert_events->bindParam(':eventType', $update_record->eventType);
			$insert_events->bindParam(':isReplaced', $update_record->isReplaced);
			$insert_events->bindParam(':isReversed', $update_record->isReversed);
			$insert_events->bindParam(':reverseReplaceDate', $update_record->reverseReplaceDate);
			
			
		
$target_icp = new icpEvents();

$target_icp->userName = "ELEC0003";
$target_icp->password = $MARIA_password;


while ($row = $sth->fetch(PDO::FETCH_ASSOC)){

	$target_icp->icpId = $row["icp"];
	echo "$target_icp->icpId \n";
	
	$target_result = $client->icpEvents_v1($target_icp);
//echo "Response:\n" . $client->__getLastResponse() . "\n";
//print_r($target_result);
//echo "Count is :".count($target_result->icpEvents_v1Result->WS_ICPEvent)."\n";
	$icp_count++;

	$sql = "select count(*) from registry_events where icp = '$target_icp->icpId'";
	$ftl_event_count = $dbh->prepare($sql);
	$ftl_event_count->execute();
	$count = $ftl_event_count->fetch(PDO::FETCH_BOTH);

	if( is_null($target_result->icpEvents_v1Result->allEvents))	{
//var_dump($target_result);
		echo 'Error Code:', $target_result->icpEvents_v1Result->allErrors->WS_Error->code, ' ',$target_result->icpEvents_v1Result->allErrors->WS_Error->item,' ', $target_result->icpEvents_v1Result->allErrors->WS_Error->text, "\n";
		if($target_result->icpDetails_v1Result->allErrors->WS_Error->code > 1000){
			exit;
		}
	continue;
	}
	echo "Count from Maria is ". count($target_result->icpEvents_v1Result->allEvents->WS_ICPEvent) . " \n";
	echo "Count frm MySQL is $count[0] \n";
	if($count[0] != count($target_result->icpEvents_v1Result->allEvents->WS_ICPEvent)){
		fwrite($errors,"Updating events for $target_icp->icpId ".date('Y-m-d')." \n");
		#delete all current records if the counts mismatch then load the records
		$sql = "delete from registry_events where icp = '$target_icp->icpId'";
		$ftl_clean_up = $dbh->exec($sql);
		if (!$ftl_clean_up) {
			echo "\nPDO::errorInfo():\n";
			print_r($dbh->errorInfo());
		}	
		
		for($i=0;$i<count($target_result->icpEvents_v1Result->allEvents->WS_ICPEvent);$i++){
			$update_record->icp = $target_icp->icpId;
			$update_record->creationDate = $target_result->icpEvents_v1Result->allEvents->WS_ICPEvent[$i]->creationDate;
			$update_record->eventDataSummary = $target_result->icpEvents_v1Result->allEvents->WS_ICPEvent[$i]->eventDataSummary;
			$update_record->eventDate = $target_result->icpEvents_v1Result->allEvents->WS_ICPEvent[$i]->eventDate;
			$update_record->eventType = $target_result->icpEvents_v1Result->allEvents->WS_ICPEvent[$i]->eventType;
			$update_record->isReplaced = $target_result->icpEvents_v1Result->allEvents->WS_ICPEvent[$i]->isReplaced;
			$update_record->isReversed = $target_result->icpEvents_v1Result->allEvents->WS_ICPEvent[$i]->isReversed;
			$update_record->reverseReplaceDate = $target_result->icpEvents_v1Result->allEvents->WS_ICPEvent[$i]->reverseReplaceDate;
	
			//because i set up the params at teh top they should just execute?			
			$insert_events->execute();
			//$insert_events->debugDumpParams();
			
			$event_count++;
		}
}
else{
#skip this ICP
}

}

$dbh = null;
$end_time = time();
$time = $end_time - $start_time;
echo "$icp_count ICPs updated with $event_count events in $time seconds \n\n";
fwrite($errors,"$icp_count ICPs updated with $event_count events in $time seconds ". date("h:i:s",$start_time)." ".date("h:i:s",$end_time) ." ".date('Y-m-d')." \n");
?>
