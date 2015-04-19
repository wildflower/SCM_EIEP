<?php

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
return $updateComponent;
}

function set_update_channel($path,$icp,$MeteringInstallationNumber,$MeteringComponentSerialNumber){
$updateChannel = new icpChannel();
				$updateChannel->icp =$icp;
				$updateChannel->MeteringInstallationNumber =$MeteringInstallationNumber;
				$updateChannel->MeteringComponentSerialNumber =$MeteringComponentSerialNumber;
				$updateChannel->ChannelNumber =$path->channelNumber;
				$updateChannel->NumberofDials = $path->numberOfDials;
				$updateChannel->RegisterContentCode = $path->vRegisterContentCode;
				$updateChannel->PeriodofAvailability = $path->periodOfAvailability;
				$updateChannel->UnitofMeasurement = $path->vUnitOfMeasurement;
				$updateChannel->EnergyFlowDirection = $path->energyFlowDirection;
				$updateChannel->AccumulatorType =$path->accumulatorType;
				$updateChannel->SettlementIndicator = $path->settlementIndicator;
				$updateChannel->EventReading =$path->eventReading;
				
return $updateChannel;			
}

function set_installation_query($updateInstallation,$dbh){
global $project;
$installation = $project.'_metering_installation';
$query = "INSERT INTO  $installation
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
global $project;
$component = $project.'_metering_component';

			
$query = "INSERT INTO $component
			(icp,MeteringInstallationNumber,MeteringComponentSerialNumber,MeteringComponentType ,MeterType ,AMIFlag,MeteringInstallationCategory,CompensationFactor, Owner,NumberOfChannelRecords)
			values (:icp,:MeteringInstallationNumber,:MeteringComponentSerialNumber,:MeteringComponentType,:MeterType,:AMIFlag,:MeteringInstallationCategory,:CompensationFactor,:Owner,:NumberOfChannelRecords) on duplicate key update 
			MeteringComponentType=values(MeteringComponentType),
			MeterType=values(MeterType),
			AMIFlag=values(AMIFlag),
			MeteringInstallationCategory=values(MeteringInstallationCategory),
			CompensationFactor=values(CompensationFactor),
			Owner=values(Owner),
			NumberOfChannelRecords=values(NumberOfChannelRecords)";
			
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
			
		$stmt->execute();
		
}

function set_channel_query($updateChannel ,$dbh){
global $project;

$channel = $project.'_metering_channel';

			
			$query = "INSERT INTO  $channel
				(icp,MeteringInstallationNumber,MeteringComponentSerialNumber,ChannelNumber ,NumberofDials ,RegisterContentCode,PeriodofAvailability,UnitofMeasurement,EnergyFlowDirection,AccumulatorType,SettlementIndicator ,EventReading)
				values (:icp,:MeteringInstallationNumber,:MeteringComponentSerialNumber,:ChannelNumber,:NumberofDials,:RegisterContentCode,:PeriodofAvailability,:UnitofMeasurement,:EnergyFlowDirection,:AccumulatorType,:SettlementIndicator,:EventReading) on duplicate key update 
				NumberofDials=values(NumberofDials),
				RegisterContentCode=values(RegisterContentCode),
				PeriodofAvailability=values(PeriodofAvailability),
				UnitofMeasurement=values(UnitofMeasurement),
				EnergyFlowDirection=values(EnergyFlowDirection),
				AccumulatorType=values(AccumulatorType),
				SettlementIndicator=values(SettlementIndicator),
				EventReading=values(EventReading)";
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
				
				$stmt->execute();
				
}

?>
