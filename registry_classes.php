<?php

class icpDetails
{	public $icpId;
	public $userName;
	public $password;
	public $eventDate;
}

class icpRegistry
{
 public $icp;
  public $icpcreationdate;
  public $icpcommisiondate;
  public $eventstart;
  public $eventend;
  public $auditnumber;
  public $connection;
  public $reconcilliation;
  public $dedicatednsp;
  public $installation;
  public $proposedretailer;
  public $umldist;
  public $networkuserref;
  public $networkpricingaudit;
  public $distpricecat;
  public $distlosscat;
  public $chargeablecapacity;
  public $reference;
  public $reconciliationauditnumber;
  public $retailer;
  public $profile;
  public $reconciliationuserreference;
  public $meteringaudit;
  public $metercontact;
  public $category;
  public $metertypehhr;
  public $metertypenhh;
  public $metertypeunm;
  public $metertypepp;
  public $ami;
  public $dailyunmeteredkwh;
  public $unmeteredretailer;
  public $meterregister;
  public $metermultiplier;
  public $meteruserref;
  public $statusaudit;
  public $icpstatus;
  public $icpstatusreason;
  public $statususerref;
  public $addressaudit;
  public $addressunit;
  public $addressnumber;
  public $addressregion;
  public $addressstreet;
  public $addresstown;  
  public $addresspostcode;
  public $addressuserref;
  public $gps_easting;
  public $gps_northing;
  public $poc;
  public $shared_icp_list;
  public $direct_billed_details;
  public $generationcapacity;
  public $generationfueltype;
  public $initial_energisationdate;
  public $networkidentifier;
  public $directbilledstatus;
  public $directbilleddetails;
  //public $electra_registrycol;
  public $anzsic;
  public $proposedmep;
  public $unmflag;
  public $unmloadtrader;
  public $switchInProgressMEP;
  public $switchInProgress;
  public $installdetails;
  
  
  }
  
  class icpChannel
{
	public $icp;
	public $MeteringInstallationNumber;
public $MeteringComponentSerialNumber;
public $ChannelNumber;
public $NumberofDials;
public $RegisterContentCode;
public $PeriodofAvailability;
public $UnitofMeasurement;
public $EnergyFlowDirection;
public $AccumulatorType;
public $SettlementIndicator;
public $EventReading;
	}
	
class icpComponent	
{
public $icp;
public $MeteringInstallationNumber;
public $MeteringComponentSerialNumber;
public $MeteringComponentType;
 public $MeterType;
 public $AMIFlag;
 public $MeteringInstallationCategory;
 public $CompensationFactor;
 public $Owner;
 public $NumberOfChannelRecords;
}

class icpInstallation 
{
public $icp;
public $MeteringInstallationNumber;
public $HighestMeteringCategory;
public $MeteringInstallationLocationCode;
 public $ATHParticipantIdentifier;
 public $MeteringInstallationType;
 public $MeteringInstallationCertificationType;
 public $MeteringInstallationCertificationDate;
 public $MeteringInstallationCertificationExpiryDate;
 public $ControlDeviceCertificationFlag;
 public $CertificationVariations;
 public $CertificationVariationsExpiryDate;
 public $CertificationNumber;
 public $MaximumInterrogationCycle;
 public $LeasePriceCode;
 public $NumberOfComponentRecords;
}


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
 

?>
