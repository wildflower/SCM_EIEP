<?php

set_error_handler('exceptions_error_handler');

function exceptions_error_handler($severity, $message, $filename, $lineno) {
  if (error_reporting() == 0) {
    return;
  }
  if (error_reporting() & $severity) {
    throw new ErrorException($message, 0, $severity, $filename, $lineno);
  }
}

function set_update_component($path,$target_result){

$updateComponent = new icpComponent() ;
		$updateComponent->icp =$target_result->icpDetails_v1Result->myIcp->icpId;
		$updateComponent->MeteringInstallationNumber = $path->vMeteringInstallationNumber;
		$updateComponent->MeteringComponentSerialNumber = $path->serialNumber;
		$updateComponent->MeteringComponentType = $path->vComponentType;
		$updateComponent->MeterType = $path->meterType;
		$updateComponent->AMIFlag = $path->amiFlag;
		$updateComponent->MeteringInstallationCategory = $path->meteringCategory;
		$updateComponent->CompensationFactor = $path->compensationFactor;
		$updateComponent->Owner = $path->owner;
		$updateComponent->NumberOfChannelRecords = $path->vNumberOfChannels;
		$updateComponent->RemovalDate = $path->removalDate;
return $updateComponent;
}

function set_update_channel($path,$icp,$updateComponent){
$updateChannel = new icpChannel();
				$updateChannel->icp =$icp;
				$updateChannel->MeteringInstallationNumber = $updateComponent->MeteringInstallationNumber;
				$updateChannel->MeteringComponentSerialNumber = $updateComponent->MeteringComponentSerialNumber;
				$updateChannel->ChannelNumber =$path->channelNumber;
				if($updateComponent->MeteringComponentType == 'M'){
					$updateChannel->NumberofDials = $path->numberOfDials;
				}else{
					$updateChannel->NumberofDials = 0;
				}
				$updateChannel->RegisterContentCode = $path->vRegisterContentCode;
				if(($updateComponent->MeteringComponentType == 'M')||($updateComponent->MeteringComponentType == 'D')){
					$updateChannel->PeriodofAvailability = $path->periodOfAvailability;
					$updateChannel->UnitofMeasurement = $path->vUnitOfMeasurement;
					$updateChannel->EnergyFlowDirection = $path->energyFlowDirection;
					$updateChannel->AccumulatorType =$path->accumulatorType;
				}else{
					$updateChannel->PeriodofAvailability = 0;
					$updateChannel->UnitofMeasurement = 0;
					$updateChannel->EnergyFlowDirection = 0;
					$updateChannel->AccumulatorType = 0;
				}
				
				$updateChannel->SettlementIndicator = $path->settlementIndicator;
				$updateChannel->EventReading =$path->eventReading;
				$updateChannel->GenericPricecode = $updateChannel->RegisterContentCode.$updateChannel->PeriodofAvailability;
				
return $updateChannel;			
}

function set_installation_query($updateInstallation,$dbh){
global $Project;

$query = "INSERT INTO  $Project->installation
			(icp,MeteringInstallationNumber,HighestMeteringCategory,MeteringInstallationLocationCode ,ATHParticipantIdentifier,MeteringInstallationType,MeteringInstallationCertificationType,MeteringInstallationCertificationDate,MeteringInstallationCertificationExpiryDate,ControlDeviceCertificationFlag,CertificationVariations ,CertificationVariationsExpiryDate ,CertificationNumber,MaximumInterrogationCycle,LeasePriceCode,NumberOfComponentRecords)
			values (
			:icp,:MeteringInstallationNumber,:HighestMeteringCategory,:MeteringInstallationLocationCode,:ATHParticipantIdentifier,:MeteringInstallationType,:MeteringInstallationCertificationType,:MeteringInstallationCertificationDate,:MeteringInstallationCertificationExpiryDate,:ControlDeviceCertificationFlag,:CertificationVariations,:CertificationVariationsExpiryDate,:CertificationNumber,:MaximumInterrogationCycle,:LeasePriceCode ,:NumberOfComponentRecords
			) on duplicate key update 
			HighestMeteringCategory=values(HighestMeteringCategory),
			MeteringInstallationLocationCode=values(MeteringInstallationLocationCode),
			ATHParticipantIdentifier=values(ATHParticipantIdentifier),
			MeteringInstallationType=values(MeteringInstallationType),
			MeteringInstallationCertificationType=values(MeteringInstallationCertificationType),
			MeteringInstallationCertificationDate=values(MeteringInstallationCertificationDate),
			MeteringInstallationCertificationExpiryDate=values(MeteringInstallationCertificationExpiryDate),
			ControlDeviceCertificationFlag=values(ControlDeviceCertificationFlag),
			CertificationVariations =values(CertificationVariations ),
			CertificationVariationsExpiryDate =values(CertificationVariationsExpiryDate ),
			CertificationNumber= values(CertificationNumber),
			MaximumInterrogationCycle = values(MaximumInterrogationCycle),
			LeasePriceCode = values(LeasePriceCode),
			NumberOfComponentRecords = values(NumberOfComponentRecords)";
$stmt = $dbh->prepare($query);

	$stmt->bindValue(':icp',$updateInstallation->icp);
	$stmt->bindValue(':MeteringInstallationNumber',$updateInstallation->MeteringInstallationNumber);
	$stmt->bindValue('HighestMeteringCategory',$updateInstallation->HighestMeteringCategory);
			$stmt->bindValue('MeteringInstallationLocationCode',$updateInstallation->MeteringInstallationLocationCode);
			$stmt->bindValue('ATHParticipantIdentifier',$updateInstallation->ATHParticipantIdentifier);
			$stmt->bindValue('MeteringInstallationType',$updateInstallation->MeteringInstallationType);
			$stmt->bindValue('MeteringInstallationCertificationType',$updateInstallation->MeteringInstallationCertificationType);
			$stmt->bindValue('MeteringInstallationCertificationDate',$updateInstallation->MeteringInstallationCertificationDate);
			$stmt->bindValue('MeteringInstallationCertificationExpiryDate',$updateInstallation->MeteringInstallationCertificationExpiryDate);
			$stmt->bindValue('ControlDeviceCertificationFlag',$updateInstallation->ControlDeviceCertificationFlag);
			$stmt->bindValue('CertificationVariations', $updateInstallation->CertificationVariations);
			$stmt->bindValue('CertificationVariationsExpiryDate',$updateInstallation->CertificationVariationsExpiryDate);
			$stmt->bindValue('CertificationNumber',$updateInstallation->CertificationNumber);
			$stmt->bindValue('MaximumInterrogationCycle',$updateInstallation->MaximumInterrogationCycle);
			$stmt->bindValue('LeasePriceCode',$updateInstallation->LeasePriceCode);
			$stmt->bindValue('NumberOfComponentRecords',$updateInstallation->NumberOfComponentRecords);	
	

$stmt->execute();



}
	 
function set_component_query($updateComponent,$dbh){
global $Project;
			
$query = "INSERT INTO $Project->component
			(icp,MeteringInstallationNumber,MeteringComponentSerialNumber,MeteringComponentType ,MeterType ,AMIFlag,MeteringInstallationCategory,CompensationFactor, Owner,NumberOfChannelRecords,removalDate)
			values (:icp,:MeteringInstallationNumber,:MeteringComponentSerialNumber,:MeteringComponentType,:MeterType,:AMIFlag,:MeteringInstallationCategory,:CompensationFactor,:Owner,:NumberOfChannelRecords,:removalDate) on duplicate key update 
			MeteringComponentType=values(MeteringComponentType),
			MeterType=values(MeterType),
			AMIFlag=values(AMIFlag),
			MeteringInstallationCategory=values(MeteringInstallationCategory),
			CompensationFactor=values(CompensationFactor),
			Owner=values(Owner),
			NumberOfChannelRecords=values(NumberOfChannelRecords),
			removalDate=values(removalDate)";
			
		$stmt = $dbh->prepare($query);	
			$stmt->bindValue(':icp',$updateComponent->icp);
			$stmt->bindValue(':MeteringInstallationNumber',$updateComponent->MeteringInstallationNumber);
			$stmt->bindValue(':MeteringComponentSerialNumber',$updateComponent->MeteringComponentSerialNumber);
			$stmt->bindValue(':MeteringComponentType',$updateComponent->MeteringComponentType);
			$stmt->bindValue(':MeterType',$updateComponent->MeterType);
			$stmt->bindValue(':AMIFlag',$updateComponent->AMIFlag);
			$stmt->bindValue(':MeteringInstallationCategory',$updateComponent->MeteringInstallationCategory);
			$stmt->bindValue(':CompensationFactor',$updateComponent->CompensationFactor);
			$stmt->bindValue(':Owner',$updateComponent->Owner);
			$stmt->bindValue(':NumberOfChannelRecords',$updateComponent->NumberOfChannelRecords) ;
			$stmt->bindValue(':removalDate',$updateComponent->RemovalDate) ;
			
		$stmt->execute();
		
}

function set_channel_query($updateChannel ,$dbh){
global $Project;

			$query = "INSERT INTO  $Project->channel
				(icp,MeteringInstallationNumber,MeteringComponentSerialNumber,ChannelNumber ,NumberofDials ,RegisterContentCode,PeriodofAvailability,UnitofMeasurement,EnergyFlowDirection,AccumulatorType,SettlementIndicator ,EventReading,Generic_pricecode)
				values (:icp,:MeteringInstallationNumber,:MeteringComponentSerialNumber,:ChannelNumber,:NumberofDials,:RegisterContentCode,:PeriodofAvailability,:UnitofMeasurement,:EnergyFlowDirection,:AccumulatorType,:SettlementIndicator,:EventReading,:GenericPricecode) on duplicate key update 
				NumberofDials=values(NumberofDials),
				RegisterContentCode=values(RegisterContentCode),
				PeriodofAvailability=values(PeriodofAvailability),
				UnitofMeasurement=values(UnitofMeasurement),
				EnergyFlowDirection=values(EnergyFlowDirection),
				AccumulatorType=values(AccumulatorType),
				SettlementIndicator=values(SettlementIndicator),
				EventReading=values(EventReading),
				Generic_pricecode=values(Generic_pricecode)";
				
$stmt = $dbh->prepare($query);
				$stmt->bindValue(':icp',$updateChannel->icp);
				$stmt->bindValue(':MeteringInstallationNumber',$updateChannel->MeteringInstallationNumber);
				$stmt->bindValue(':MeteringComponentSerialNumber',$updateChannel->MeteringComponentSerialNumber);
				$stmt->bindValue(':ChannelNumber',$updateChannel->ChannelNumber);
				$stmt->bindValue(':NumberofDials',$updateChannel->NumberofDials);
				$stmt->bindValue(':RegisterContentCode',$updateChannel->RegisterContentCode);
				$stmt->bindValue(':PeriodofAvailability',$updateChannel->PeriodofAvailability);
				$stmt->bindValue(':UnitofMeasurement',$updateChannel->UnitofMeasurement);
				$stmt->bindValue(':EnergyFlowDirection',$updateChannel->EnergyFlowDirection);
				$stmt->bindValue(':AccumulatorType',$updateChannel->AccumulatorType);
				$stmt->bindValue(':SettlementIndicator', $updateChannel->SettlementIndicator);
				$stmt->bindValue(':EventReading',$updateChannel->EventReading);
				$stmt->bindValue(':GenericPricecode',$updateChannel->GenericPricecode);
				
				$stmt->execute();
				
}

?>
