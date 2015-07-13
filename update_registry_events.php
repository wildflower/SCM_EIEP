<?php
parse_str(implode('&', array_slice($argv, 1)), $_GET);
include 'settings.php';
$start_time = time();
$wsdl = "https://www.electricityregistry.co.nz/bin_public/Jadehttp.dll?WebService&listName=WSP_Registry&serviceName=WSRegistry&wsdl=wsdl";
$wsdl = "https://www.electricityregistry.co.nz/bin_public/Jadehttp.dll?WebService&serviceName=WSRegistry&listName=WSP_Registry2&wsdl=wsdl";
$client = new SoapClient($wsdl,array('trace'=>1));

$project_table = 'electra_registry';
$errors = fopen('registry_updates.txt','a');

$link = mysql_connect('localhost', $user, $password);
if (!$link) {
    die('Could not connect: ' . mysql_error());
}
mysql_select_db('scm',$link);

if(isset($_GET['dataset'])){
	$dataset = $_GET['dataset'];	
}else{
	$dataset = 'all';
}

if ($dataset == 'all'){
	$query = "select icp from $project_table";
}else{
	$query = "select distinct icp from icpincident" ;
}

$icp_list = mysql_query($query);

if (!$icp_list) {
    $message  = 'Invalid query: ' . mysql_error() . "\n";
    $message .= 'Whole query: ' . $query;
    die($message);
}
//var_dump($client->__getFunctions());

$icp_count =  0;
$event_count = 0;

class icpEvents
{ 
	public $icpId;
	public $userName;
	public $password;
	public $includeReversed;
	public $includeSwitch;
	public $includeRecon;
	public $includeNetwork;
	public $includePricing;
	public $includeStatus;
	public $includeAddress;
	public $includeMeter;
}

class scmEvent
{
	public $icp;
	public $creationDate;
	public $eventDataSummary;
	public $eventDate;
	public $eventType;
	public $isReplaced;
	public $isReversed;
	public $reverseReplaceDate;	
}
  
$update_record = new scmEvent();
$target_icp = new icpEvents();

$target_icp->userName = "ELEC0003";
$target_icp->password = $MARIA_password;
$target_icp->includeReversed = "1";
$target_icp->includeSwitch = "1";
$target_icp->includeRecon = "1";
$target_icp->includeTrader = "1";
$target_icp->includePricing = "1";
$target_icp->includeStatus = "1";
$target_icp->includeAddress = "1";
$target_icp->includeMeter = "1";
$target_icp->includeNetwork = "1";

while ($row = mysql_fetch_assoc($icp_list)){

$target_icp->icpId = $row["icp"];

$target_result = $client->icpEvents_v1($target_icp);
//echo "Response:\n" . $client->__getLastResponse() . "\n";
//print_r($target_result);
//echo "Count is :".count($target_result->icpEvents_v1Result->WS_ICPEvent)."\n";
$icp_count++;

$sql = "select count(*) from registry_events where icp = '$target_icp->icpId'";
$eventcount = mysql_query($sql);
$count = mysql_result($eventcount,0);
if( is_null($target_result->icpEvents_v1Result->allEvents))
{
//var_dump($target_result);
echo 'Error Code:', $target_result->icpEvents_v1Result->allErrors->WS_Error->code, ' ',$target_result->icpEvents_v1Result->allErrors->WS_Error->item,' ', $target_result->icpEvents_v1Result->allErrors->WS_Error->text, "\n";
	if($target_result->icpDetails_v1Result->allErrors->WS_Error->code > 1000){
	exit;
	}
continue;
}
if($count != count($target_result->icpEvents_v1Result->allEvents->WS_ICPEvent)){
#delete all current records if the counts mismatch then load the records
$sql = "delete from registry_events where icp = '$target_icp->icpId'";
mysql_query($sql);

for($i=0;$i<count($target_result->icpEvents_v1Result->allEvents->WS_ICPEvent);$i++){
	$update_record->icp = $target_icp->icpId;
	$update_record->creationDate = $target_result->icpEvents_v1Result->allEvents->WS_ICPEvent[$i]->creationDate;
	$update_record->eventDataSummary = mysql_real_escape_string($target_result->icpEvents_v1Result->allEvents->WS_ICPEvent[$i]->eventDataSummary);
	$update_record->eventDate = $target_result->icpEvents_v1Result->allEvents->WS_ICPEvent[$i]->eventDate;
	$update_record->eventType = $target_result->icpEvents_v1Result->allEvents->WS_ICPEvent[$i]->eventType;
	$update_record->isReplaced = $target_result->icpEvents_v1Result->allEvents->WS_ICPEvent[$i]->isReplaced;
	$update_record->isReversed = $target_result->icpEvents_v1Result->allEvents->WS_ICPEvent[$i]->isReversed;
	$update_record->reverseReplaceDate = $target_result->icpEvents_v1Result->allEvents->WS_ICPEvent[$i]->reverseReplaceDate;
	
	$query = "INSERT INTO registry_events set   icp='$update_record->icp', creationDate ='$update_record->creationDate',  eventDataSummary = '$update_record->eventDataSummary' ,eventDate='$update_record->eventDate', eventType ='$update_record->eventType', isReplaced ='$update_record->isReplaced', isReversed = '$update_record->isReversed' , reverseReplaceDate='$update_record->reverseReplaceDate'";
	$result = mysql_query($query);
		// Check result This shows the actual query sent to MySQL, and the error. Useful for debugging.
	if (!$result) {
		$message  = 'Invalid query: ' . mysql_error() . "\n";
		$message .= 'Whole query: ' . $query;
		die($message);
	}  
	/*if($target_result->icpEvents_v1Result->allEvents->WS_ICPEvent[$i]->isReversed){
	//echo $target_icp->icpId."\n";
	print_r($target_result->icpEvents_v1Result->allEvents->WS_ICPEvent[$i]);
	die();
	}*/
	$event_count++;
}
}
else{
#skip this ICP
}

}

mysql_close($link);
$end_time = time();
$time = $end_time - $start_time;
echo "$icp_count ICPs updated with $event_count events in $time seconds";
fwrite($errors,"$icp_count ICPs updated with $event_count events in $time seconds ".date('Y-m-d')." \n");
?>
