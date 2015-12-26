<?php
class ftl_project
{
	public $project_name;
	public $metering_table;
	public $metering_view;
	public $project_table;
	public $installation;
	public $component;
	public $channel;
	
	function __construct($name)
    {
        $this->project_name = $name;
		$this->project_table = $this->project_name.'_registry';
		$this->metering_view = 'vw_metering_info_'.$this->project_name;
		$this->metering_table = $this->project_name.'_mat_metering_info_';
		$this->installation  = $this->project_name.'_metering_installation';
		$this->component = $this->project_name.'_metering_component';
		$this->channel = $this->project_name.'_metering_channel';
	}
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
	
	function __construct()
    {
		$this->includeReversed = "1";
		$this->includeSwitch = "1";
		$this->includeRecon = "1";
		$this->includeTrader = "1";
		$this->includePricing = "1";
		$this->includeStatus = "1";
		$this->includeAddress = "1";
		$this->includeMeter = "1";
		$this->includeNetwork = "1";
	}
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
  public $propertyname;
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
  public $submissionTypeNHH;
  public $submissionTypeHHR;
  
  
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
public $GenericPricecode;
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
?>

