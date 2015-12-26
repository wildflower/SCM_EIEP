<?php

parse_str(implode('&', array_slice($argv, 1)), $_GET);

include 'settings.php';
error_reporting(E_ALL);
ini_set('display_errors', true);

include 'registry_classes.php';
include 'registry_functions.php';

$start_time = time();
$wsdl       = "https://www.electricityregistry.co.nz/bin_public/Jadehttp.dll?WebService&serviceName=WSRegistry&listName=WSP_Registry2&wsdl=wsdl";
//$wsdl = "https://part10test.electricityregistry.co.nz/bin_public/Jadehttp.dll?WebService&listName=WSP_Registry2&serviceName=WSRegistry&wsdl=wsdl";
#LIS20120601122041.txt WAIP
$client     = new SoapClient($wsdl, array(
    'trace' => 1
));

if(isset($_GET['project'])){
	$project = $_GET['project'];
}else
{
	echo "I need a project Name \n";
	exit;
}
$Project = new ftl_project($project);



echo "Working with $Project->project_table \n";

$errors = fopen('registry_updates.txt', 'a');

$count = 0;
 
$dsn     = 'mysql:host=127.0.0.1;dbname=scm';
$options = array(
    PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'
);

if (isset($_GET['dataset'])) {
    $dataset = $_GET['dataset'];
} else {
echo "Parameter dataset is required : all, new, icpincident, specific \n ";
exit;
}

$dbh = new PDO($dsn, $user, $password, $options);
$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
include 'statements.php';
 

//$query = 'select "0000339010EL523"  from dual';
if ($dataset == 'all') {
    $query = "select icp from $Project->project_table ";
} elseif ($dataset == 'icpincident') {
    $query = "select distinct icp from icpincident";
} elseif ($dataset == 'new'){
	$query = "select icp from $Project->project_table where icpcreationdate is null";
}elseif ($dataset == 'specific'){
	$query = "select icp from $Project->project_table where icp in ('0001832612AL528', '0001311335ALC5A','0001010026AL420','0000000592CE895','0000001273CE0C8','0000001746CEF7A','0000203551DE799','0000203576DE706') ";
}else {

echo "Parameter dataset is required : all, new, icpincident \n ";
exit;
}
$sth = $dbh->prepare($query);
$sth->execute();

//var_dump($client->__getFunctions());

$target_icp            = new icpDetails();
$target_icp->userName  = "ELEC0003";
$target_icp->password  = $MARIA_password;
$target_icp->eventDate = "";

$update_record = new icpRegistry();

//$row = $sth->fetch(PDO::FETCH_ASSOC);
//$result = array('icp' => ['0000129363ELF84','0015736017ELEEF']);
while ($row = $sth->fetch(PDO::FETCH_ASSOC)) {
    
    $target_icp->icpId = $row['icp'];
    fwrite($errors, "Getting ICP : $target_icp->icpId ".date('Y-m-d')." \n");
    $target_result = $client->icpDetails_v1($target_icp);
    //print_r($target_result);
  //  echo "Response:\n" . $client->__getLastResponse() . "\n";
    //var_dump($target_result);
    //exit();
    //echo "here";
    if (is_null($target_result->icpDetails_v1Result->myIcp->icpId)) {
        echo 'Error Code:', $target_result->icpDetails_v1Result->allErrors->WS_Error->code, ' ', $target_result->icpDetails_v1Result->allErrors->WS_Error->item, ' ', $target_result->icpDetails_v1Result->allErrors->WS_Error->text, "\n";
        if ($target_result->icpDetails_v1Result->allErrors->WS_Error->code > 1000) {
            exit;
        }
        continue;
    }
    
    $update_record->icp             = $target_result->icpDetails_v1Result->myIcp->icpId;
    $update_record->icpcreationdate = $target_result->icpDetails_v1Result->myStatusHistory->eventDate;
    //$update_record->icpcommisiondate = $target_result->icpDetails_v1Result->myStatusHistory->eventDate
    
    $update_record->switchInProgress    = $target_result->icpDetails_v1Result->myIcp->switchInProgress;
    $update_record->switchInProgressMEP = $target_result->icpDetails_v1Result->myIcp->switchInProgressMEP;
    if (isset($target_result->icpDetails_v1Result->myAddressHistory)) {
        $update_record->addressaudit    = $target_result->icpDetails_v1Result->myAddressHistory->currentAuditNumber;
		if (isset($target_result->icpDetails_v1Result->myAddressHistory->propertyName)){
			$update_record->propertyname     = $target_result->icpDetails_v1Result->myAddressHistory->propertyName;
		}
        $update_record->addressunit     = $target_result->icpDetails_v1Result->myAddressHistory->unit;
        $update_record->addressnumber   = $target_result->icpDetails_v1Result->myAddressHistory->number;
        $update_record->addressregion   = $target_result->icpDetails_v1Result->myAddressHistory->vRegion;
        $update_record->addressstreet   = $target_result->icpDetails_v1Result->myAddressHistory->street;
        $update_record->addresstown     = $target_result->icpDetails_v1Result->myAddressHistory->town;
        $update_record->addresspostcode = $target_result->icpDetails_v1Result->myAddressHistory->postCode;
        $update_record->addressuserref  = $target_result->icpDetails_v1Result->myAddressHistory->userRef;
        $update_record->gps_northing    = $target_result->icpDetails_v1Result->myAddressHistory->vGPS_Northing;
        $update_record->gps_easting     = $target_result->icpDetails_v1Result->myAddressHistory->vGPS_Easting;
    }
    
    
    //echo "poc ".$target_result->icpDetails_v1Result->myNetworkHistory->bus ;
    
    $update_record->poc = $target_result->icpDetails_v1Result->myNetworkHistory->bus;
    //echo " poc2 $update_record->poc";
    if (isset($target_result->icpDetails_v1Result->myNetworkHistory->allSharedICPsList)) {
		if (count($target_result->icpDetails_v1Result->myNetworkHistory->allSharedICPsList) > 1){
			var_dump($target_result);
			exit();
		}
	/*echo "Getting Class: \n";
	$class = get_class($target_result->icpDetails_v1Result->myNetworkHistory->allSharedICPsList);
	echo "Class is $class: \n";
	var_export($target_result->icpDetails_v1Result->myNetworkHistory->allSharedICPsList) ;
	
	echo "\n Getting List: \n";
	$sharedicplist = (array) $target_result->icpDetails_v1Result->myNetworkHistory->allSharedICPsList;
	echo "List is $sharedicplist[1]: \n";
	var_dump($target_result->icpDetails_v1Result->myNetworkHistory->allSharedICPsList);
	echo "\nNumber of Elelments in list: ". count($target_result->icpDetails_v1Result->myNetworkHistory->allSharedICPsList),"\n";
	
        $update_record->shared_icp_list = trim($target_result->icpDetails_v1Result->myNetworkHistory->allSharedICPsList[0], "\n\r");
	var_dump($target_result->icpDetails_v1Result->myNetworkHistory->allSharedICPsList);
	echo "All Shared ICP list $target_result->icpDetails_v1Result->myNetworkHistory->allSharedICPsList \n";	
	var_dump($target_result);
	exit();*/
    }
    
    $update_record->directbilleddetails      = $target_result->icpDetails_v1Result->myNetworkHistory->directBilledDetails;
    $update_record->directbilledstatus       = $target_result->icpDetails_v1Result->myNetworkHistory->vDirectBilledStatus;
    $update_record->generationcapacity       = $target_result->icpDetails_v1Result->myNetworkHistory->generationCapacityKW;
    $update_record->generationfueltype       = $target_result->icpDetails_v1Result->myNetworkHistory->vGenerationFuelType;
    $update_record->initial_energisationdate = $target_result->icpDetails_v1Result->myNetworkHistory->initialEnergisationDate;
    $update_record->networkidentifier        = $target_result->icpDetails_v1Result->myNetworkHistory->networkIdentifier;
    
    $update_record->auditnumber      = $target_result->icpDetails_v1Result->myNetworkHistory->currentAuditNumber;
    $update_record->connection       = $target_result->icpDetails_v1Result->myNetworkHistory->bus;
    $update_record->dedicatednsp     = $target_result->icpDetails_v1Result->myNetworkHistory->dedicatedNSP;
    $update_record->installation     = $target_result->icpDetails_v1Result->myNetworkHistory->installationType;
    $update_record->reconcilliation  = $target_result->icpDetails_v1Result->myNetworkHistory->vReconciliationType;
    $update_record->proposedretailer = $target_result->icpDetails_v1Result->myNetworkHistory->proposedRetailer;
    $update_record->umldist          = $target_result->icpDetails_v1Result->myNetworkHistory->unmeteredLoadDistributor;
    $update_record->networkuserref   = $target_result->icpDetails_v1Result->myNetworkHistory->userRef;
    
    if (isset($target_result->icpDetails_v1Result->myPricingHistory)) {
        $update_record->installdetails = $target_result->icpDetails_v1Result->myPricingHistory->installDetails;
        
        $update_record->networkpricingaudit = $target_result->icpDetails_v1Result->myPricingHistory->currentAuditNumber;
        if (isset($target_result->icpDetails_v1Result->myPricingHistory->allPriceCategoryCodes->PriceCategoryCode->code))
            $update_record->distpricecat = $target_result->icpDetails_v1Result->myPricingHistory->allPriceCategoryCodes->PriceCategoryCode->code;
        else {
            $distpricecat = "";
            for ($m = 0; $m < count($target_result->icpDetails_v1Result->myPricingHistory->allPriceCategoryCodes->PriceCategoryCode); $m++) {
                $distpricecat = $distpricecat . $target_result->icpDetails_v1Result->myPricingHistory->allPriceCategoryCodes->PriceCategoryCode[$m]->code . " ";
            }
            $update_record->distpricecat = $distpricecat;
            echo "Distpricecat is $distpricecat. \n";
        }
        
        $update_record->distlosscat        = $target_result->icpDetails_v1Result->myPricingHistory->myLossFactorCode->code;
        $update_record->chargeablecapacity = $target_result->icpDetails_v1Result->myPricingHistory->chargeableCapacity;
        $update_record->reference          = $target_result->icpDetails_v1Result->myPricingHistory->userRef;
    }
    
    $update_record->submissionTypeHHR       = $target_result->icpDetails_v1Result->myTraderHistory->submissionTypeHHR;
    $update_record->submissionTypeNHH       = $target_result->icpDetails_v1Result->myTraderHistory->submissionTypeNHH;
	//  echo '$update_record->submissionTypeNHH is '." $update_record->submissionTypeNHH ";
	//	echo '$update_record->submissionTypeHHR is '." $update_record->submissionTypeHHR ";
    $update_record->proposedmep       = $target_result->icpDetails_v1Result->myTraderHistory->vProposedMEPidentifier;
    $update_record->unmloadtrader     = $target_result->icpDetails_v1Result->myTraderHistory->unmeteredLoadTrader;
    $update_record->unmflag           = $target_result->icpDetails_v1Result->myTraderHistory->unmFlag;
	if ($update_record->unmflag == false){
		$update_record->dailyunmeteredkwh = 0;
	}
	else{
		$update_record->dailyunmeteredkwh = $target_result->icpDetails_v1Result->myTraderHistory->dailyUnmeteredkWh;
	}
	
    $update_record->anzsic            = $target_result->icpDetails_v1Result->myTraderHistory->vANZSICcode;
    
    
    $update_record->reconciliationauditnumber   = $target_result->icpDetails_v1Result->myTraderHistory->currentAuditNumber;
    $update_record->reconciliationuserreference = $target_result->icpDetails_v1Result->myTraderHistory->userRef;
    $update_record->retailer                    = $target_result->icpDetails_v1Result->myTraderHistory->myRetailer->code;
	//if allProfiles xsi:nil="true"
	if( isset($target_result->icpDetails_v1Result->myTraderHistory->allProfiles)){
    
    if (isset($target_result->icpDetails_v1Result->myTraderHistory->allProfiles->RetailerProfile->myProfile->profileCode))
        $update_record->profile = $target_result->icpDetails_v1Result->myTraderHistory->allProfiles->RetailerProfile->myProfile->profileCode;
    else {
        $profile = "";
		try{
			for ($l = 0; $l < count($target_result->icpDetails_v1Result->myTraderHistory->allProfiles->RetailerProfile); $l++) {
				$profile = $profile . $target_result->icpDetails_v1Result->myTraderHistory->allProfiles->RetailerProfile[$l]->myProfile->profileCode . " ";
			}
			$update_record->profile = $profile;
		}catch  (Exception $e) {
			echo $e->getLine()." - can't find icpDetails_v1Result -> myTraderHistory -> allProfiles -> RetailerProfile \n";
			echo "Response:\n" . $client->__getLastResponse() . "\n";
		}
        //	echo "Profile is $profile. \n";
    }
	}else{
		echo "there are no Profiles \n"; 
		$update_record->profile = NULL;
		
	}
		
    if(isset($target_result->icpDetails_v1Result->myMeteringHistory)){
		try{
			$update_record->meteringaudit   = $target_result->icpDetails_v1Result->myMeteringHistory->currentAuditNumber;
		}catch (Exception $e) {
			echo $e->getLine()." - can't find icpDetails_v1Result ->myMeteringHistory->currentAuditNumber \n";
			echo "Response:\n" . $client->__getLastResponse() . "\n";
		}
		$update_record->meteruserref    = $target_result->icpDetails_v1Result->myMeteringHistory->userRef;
		$update_record->metercontact    = $target_result->icpDetails_v1Result->myMeteringHistory->userId;
		$update_record->category        = $target_result->icpDetails_v1Result->myMeteringHistory->highestMeteringCategory;
		$update_record->metertypehhr    = $target_result->icpDetails_v1Result->myMeteringHistory->hhrFlag;
		$update_record->metertypenhh    = $target_result->icpDetails_v1Result->myMeteringHistory->nhhFlag;
		$update_record->metertypepp     = $target_result->icpDetails_v1Result->myMeteringHistory->ppFlag;
    //$update_record->metertypeunm  = $target_result->icpDetails_v1Result->myMeteringHistory->meterTypeUNM;
		$update_record->ami             = $target_result->icpDetails_v1Result->myMeteringHistory->amiFlag;
    //$update_record->dailyunmeteredkwh  = $target_result->icpDetails_v1Result->myMeteringHistory->dailyUnmeteredkWh//;
    //$update_record->unmeteredretailer  = mysql_real_escape_string($target_result->icpDetails_v1Result->myMeteringHistory->unmeteredLoadRetailer);
		$update_record->meterregister   = $target_result->icpDetails_v1Result->myMeteringHistory->meterRegisterCount;
		$update_record->metermultiplier = $target_result->icpDetails_v1Result->myMeteringHistory->meterMultiplierFlag;
    
		if ($target_result->icpDetails_v1Result->myMeteringHistory->vNumberOfInstallations == 1) {
			for ($i = 0; $i < count($target_result->icpDetails_v1Result->myMeteringHistory->allMeteringInstallations->MeteringInstallation); $i++) {
            $updateInstallation                                              = new icpInstallation();
            $updateInstallation->icp                                         = $target_result->icpDetails_v1Result->myIcp->icpId;
            $updateInstallation->MeteringInstallationNumber                  = $target_result->icpDetails_v1Result->myMeteringHistory->allMeteringInstallations->MeteringInstallation->installationNumber;
            $updateInstallation->HighestMeteringCategory                     = $target_result->icpDetails_v1Result->myMeteringHistory->allMeteringInstallations->MeteringInstallation->highestMeteringCategory;
            $updateInstallation->MeteringInstallationLocationCode            = $target_result->icpDetails_v1Result->myMeteringHistory->allMeteringInstallations->MeteringInstallation->locationCode;
            $updateInstallation->ATHParticipantIdentifier                    = $target_result->icpDetails_v1Result->myMeteringHistory->allMeteringInstallations->MeteringInstallation->vATHparticipantIdentifier;
            $updateInstallation->MeteringInstallationType                    = $target_result->icpDetails_v1Result->myMeteringHistory->allMeteringInstallations->MeteringInstallation->vMeteringInstallationType;
            $updateInstallation->MeteringInstallationCertificationType       = $target_result->icpDetails_v1Result->myMeteringHistory->allMeteringInstallations->MeteringInstallation->vCertificationType;
            $updateInstallation->MeteringInstallationCertificationDate       = $target_result->icpDetails_v1Result->myMeteringHistory->allMeteringInstallations->MeteringInstallation->certificationDate;
            $updateInstallation->MeteringInstallationCertificationExpiryDate = $target_result->icpDetails_v1Result->myMeteringHistory->allMeteringInstallations->MeteringInstallation->certificationExpiryDate;
            $updateInstallation->ControlDeviceCertificationFlag              = $target_result->icpDetails_v1Result->myMeteringHistory->allMeteringInstallations->MeteringInstallation->controlDeviceCertificationFlag;
            $updateInstallation->CertificationVariations                     = $target_result->icpDetails_v1Result->myMeteringHistory->allMeteringInstallations->MeteringInstallation->vCertVariations;
            $updateInstallation->CertificationVariationsExpiryDate           = $target_result->icpDetails_v1Result->myMeteringHistory->allMeteringInstallations->MeteringInstallation->certVariationsExpiryDate;
            $updateInstallation->CertificationNumber                         = $target_result->icpDetails_v1Result->myMeteringHistory->allMeteringInstallations->MeteringInstallation->certificationNumber;
            $updateInstallation->MaximumInterrogationCycle                   = $target_result->icpDetails_v1Result->myMeteringHistory->allMeteringInstallations->MeteringInstallation->maxInterrogationCycle;
            $updateInstallation->LeasePriceCode                              = $target_result->icpDetails_v1Result->myMeteringHistory->allMeteringInstallations->MeteringInstallation->leasePriceCode;
            $updateInstallation->NumberOfComponentRecords                    = $target_result->icpDetails_v1Result->myMeteringHistory->allMeteringInstallations->MeteringInstallation->vNumberOfComponents;
            
            set_installation_query($updateInstallation, $dbh);
            
            if ($updateInstallation->NumberOfComponentRecords > 1) {
                //echo "more than 1 Component in this record \n\n";
                for ($k = 0; $k < count($target_result->icpDetails_v1Result->myMeteringHistory->allMeteringInstallations->MeteringInstallation->allMeteringComponents->MeteringComponent); $k++) {
                    //	echo "k is $k \n";
                    $path            = $target_result->icpDetails_v1Result->myMeteringHistory->allMeteringInstallations->MeteringInstallation->allMeteringComponents->MeteringComponent[$k];
                    $updateComponent = set_update_component($path, $target_result);
                    set_component_query($updateComponent, $dbh);
                    
                    if ($updateComponent->NumberOfChannelRecords > 1) {
                        //there is more than 1 so we need to get the values from referencing from the array of the parent branch
                        //		echo "More than 1 channel in this Component \n";
                        for ($j = 0; $j < count($target_result->icpDetails_v1Result->myMeteringHistory->allMeteringInstallations->MeteringInstallation->allMeteringComponents->MeteringComponent[$k]->allMeteringChannels->MeteringChannel); $j++) {
                            $path          = $target_result->icpDetails_v1Result->myMeteringHistory->allMeteringInstallations->MeteringInstallation->allMeteringComponents->MeteringComponent[$k]->allMeteringChannels->MeteringChannel[$j];
                            $updateChannel = set_update_channel($path, $target_result->icpDetails_v1Result->myIcp->icpId, $updateComponent);
                            set_channel_query($updateChannel, $dbh);
                        }
                    } elseif ($updateComponent->NumberOfChannelRecords == 1) {
                        //		echo "One Channel but more than one Component still \n\n";
                        // there is only 1 Channel  so there isn't an array that we need to reference from but there's still an Array of Components
                        //			echo $target_result->icpDetails_v1Result->myIcp->icpId;
                        $path          = $target_result->icpDetails_v1Result->myMeteringHistory->allMeteringInstallations->MeteringInstallation->allMeteringComponents->MeteringComponent[$k]->allMeteringChannels->MeteringChannel;
                        $updateChannel = set_update_channel($path, $target_result->icpDetails_v1Result->myIcp->icpId, $updateComponent);
                        set_channel_query($updateChannel, $dbh);
                    } else {
                        //No Channels in this Component
                        echo "No channels here \n";
                    }
                }
            } else {
                //only 1 component record  but could be multiple Channels
                //	echo " One Component record \n\n";
                //echo "k is $k \n";
                if (isset($target_result->icpDetails_v1Result->myMeteringHistory->allMeteringInstallations->MeteringInstallation->allMeteringComponents->MeteringComponent)) {
                    $path            = $target_result->icpDetails_v1Result->myMeteringHistory->allMeteringInstallations->MeteringInstallation->allMeteringComponents->MeteringComponent;
                    $updateComponent = set_update_component($path, $target_result);
                    set_component_query($updateComponent, $dbh);
                    //we still need to double up on the Channel logic -need to break this out into a function along with the above
                    if ($updateComponent->NumberOfChannelRecords > 1) {
                        //there is more than 1 so we need to get the values from referencing from the array of the parent branch
                        //echo "More than 1 channel in this Component \n";
                        //echo "k is $k \n";
                        for ($j = 0; $j < count($target_result->icpDetails_v1Result->myMeteringHistory->allMeteringInstallations->MeteringInstallation->allMeteringComponents->MeteringComponent->allMeteringChannels->MeteringChannel); $j++) {
                            $path          = $target_result->icpDetails_v1Result->myMeteringHistory->allMeteringInstallations->MeteringInstallation->allMeteringComponents->MeteringComponent->allMeteringChannels->MeteringChannel[$j];
                            $updateChannel = set_update_channel($path, $target_result->icpDetails_v1Result->myIcp->icpId, $updateComponent);
                            set_channel_query($updateChannel, $dbh);
                        }
                    } elseif ($updateComponent->NumberOfChannelRecords == 1) {
                        // there is only 1 Channel  so there isn't an array that we need to reference from 
                        //echo $target_result->icpDetails_v1Result->myIcp->icpId;
                        $path          = $target_result->icpDetails_v1Result->myMeteringHistory->allMeteringInstallations->MeteringInstallation->allMeteringComponents->MeteringComponent->allMeteringChannels->MeteringChannel;
                        $updateChannel = set_update_channel($path, $target_result->icpDetails_v1Result->myIcp->icpId, $updateComponent);
                        set_channel_query($updateChannel, $dbh);
                    }else {
                        //No Channels in this Component
                        echo "No channels here \n";
                    }
                }
            }
        }
    }
	}
    
    $update_record->statusaudit     = $target_result->icpDetails_v1Result->myStatusHistory->currentAuditNumber;
    $update_record->statususerref   = $target_result->icpDetails_v1Result->myStatusHistory->userRef;
    $update_record->icpstatus       = $target_result->icpDetails_v1Result->myStatusHistory->status;
    $update_record->icpstatusreason = $target_result->icpDetails_v1Result->myStatusHistory->statusReason;
       
    
    $stmt->bindValue(':icp', $update_record->icp);
    $stmt->bindValue(':icpcreationdate', $update_record->icpcreationdate);
    $stmt->bindValue(':icpcommisiondate', $update_record->icpcommisiondate);
    $stmt->bindValue(':eventstart', $update_record->eventstart);
    $stmt->bindValue(':eventend', $update_record->eventend);
    $stmt->bindValue(':auditnumber', $update_record->auditnumber);
    $stmt->bindValue(':connection', $update_record->connection);
    $stmt->bindValue(':reconcilliation', $update_record->reconcilliation);
    $stmt->bindValue(':dedicatednsp', $update_record->dedicatednsp);
    $stmt->bindValue(':installation', $update_record->installation);
    $stmt->bindValue(':proposedretailer', $update_record->proposedretailer);
    $stmt->bindValue(':umldist', $update_record->umldist);
    $stmt->bindValue(':networkuserref', $update_record->networkuserref);
    $stmt->bindValue(':networkpricingaudit', $update_record->networkpricingaudit);
    $stmt->bindValue(':distpricecat', $update_record->distpricecat);
    $stmt->bindValue(':distlosscat', $update_record->distlosscat);
    $stmt->bindValue(':chargeablecapacity', $update_record->chargeablecapacity);
    $stmt->bindValue(':reference', $update_record->reference);
    $stmt->bindValue(':reconciliationauditnumber', $update_record->reconciliationauditnumber);
    $stmt->bindValue(':retailer', $update_record->retailer);
    $stmt->bindValue(':profile', $update_record->profile);
    $stmt->bindValue(':reconciliationuserreference', $update_record->reconciliationuserreference);
    $stmt->bindValue(':meteringaudit', $update_record->meteringaudit);
    $stmt->bindValue(':metercontact', $update_record->metercontact);
    $stmt->bindValue(':category', $update_record->category);
    $stmt->bindValue(':metertypehhr', $update_record->metertypehhr);
    $stmt->bindValue(':metertypenhh', $update_record->metertypenhh);
    $stmt->bindValue(':metertypeunm', $update_record->metertypeunm);
    $stmt->bindValue(':metertypepp', $update_record->metertypepp);
    $stmt->bindValue(':ami', $update_record->ami);
    $stmt->bindValue(':dailyunmeteredkwh', $update_record->dailyunmeteredkwh);
    $stmt->bindValue(':unmeteredretailer', $update_record->unmeteredretailer);
    $stmt->bindValue(':meterregister', $update_record->meterregister);
    $stmt->bindValue(':metermultiplier', $update_record->metermultiplier);
    $stmt->bindValue(':meteruserref', $update_record->meteruserref);
    $stmt->bindValue(':statusaudit', $update_record->statusaudit);
    $stmt->bindValue(':icpstatus', $update_record->icpstatus);
    $stmt->bindValue(':icpstatusreason', $update_record->icpstatusreason);
    $stmt->bindValue(':statususerref', $update_record->statususerref);
    $stmt->bindValue(':addressaudit', $update_record->addressaudit);
    $stmt->bindValue(':addressunit', $update_record->addressunit);
    $stmt->bindValue(':addressnumber', $update_record->addressnumber);
    $stmt->bindValue(':addressregion', $update_record->addressregion);
    $stmt->bindValue(':addressstreet', $update_record->addressstreet);
    $stmt->bindValue(':addresstown', $update_record->addresstown);
    $stmt->bindValue(':addresspostcode', $update_record->addresspostcode);
    $stmt->bindValue(':addressuserref', $update_record->addressuserref);
    $stmt->bindValue(':gps_easting', $update_record->gps_easting);
    $stmt->bindValue(':gps_northing', $update_record->gps_northing);
    $stmt->bindValue(':poc', $update_record->poc);
    $stmt->bindValue(':shared_icp_list', $update_record->shared_icp_list);
    $stmt->bindValue(':direct_billed_details', $update_record->direct_billed_details);
    $stmt->bindValue(':generationcapacity', $update_record->generationcapacity);
    $stmt->bindValue(':generationfueltype', $update_record->generationfueltype);
    $stmt->bindValue(':initial_energisationdate', $update_record->initial_energisationdate);
    $stmt->bindValue(':networkidentifier', $update_record->networkidentifier);
    $stmt->bindValue(':directbilledstatus', $update_record->directbilledstatus);
    $stmt->bindValue(':directbilleddetails', $update_record->directbilleddetails);
    $stmt->bindValue(':anzsic', $update_record->anzsic);
    $stmt->bindValue(':proposedmep', $update_record->proposedmep);
    $stmt->bindValue(':unmflag', $update_record->unmflag);
    $stmt->bindValue(':unmloadtrader', $update_record->unmloadtrader);
    $stmt->bindValue(':switchInProgressMEP', $update_record->switchInProgressMEP);
    $stmt->bindValue(':switchInProgress', $update_record->switchInProgress);
    $stmt->bindValue(':installdetails', $update_record->installdetails);
    $stmt->bindValue(':submissionTypeNHH', $update_record->submissionTypeNHH);
    $stmt->bindValue(':submissionTypeHHR', $update_record->submissionTypeHHR);
	$stmt->bindValue(':propertyname', $update_record->propertyname);
    
    
    
   // echo " before execute \n";	
    //$stmt->debugDumpParams();
    try{
		$stmt->execute();
	}
	catch(Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), "\n";
	$arr = $stmt->errorInfo();
    print_r($arr);
}
    
    //$result = $stmt->fetchAll();
    //	echo " after execute \n ";	
    //var_dump($result);
    $count++;
}
// Need to call different procedures or reference the different project views to load the project specific table
//$newmetering = $dbh->exec($metering_update );
//echo "$newmetering rows updated by Joe's code \n";

$end_time = time();
$time     = $end_time - $start_time;
echo "$count ICPs updated  in $time seconds \n";

function parms($string, $data)
{
    $indexed = $data == array_values($data);
    foreach ($data as $k => $v) {
        if (is_string($v))
            $v = "'$v'";
        if ($indexed)
            $string = preg_replace('/\?/', $v, $string, 1);
        else
            $string = str_replace(":$k", $v, $string);
    }
    return $string;
}



?>
