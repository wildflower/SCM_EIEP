<?php

$replace_stmt = $dbh->prepare("REPLACE INTO electra_registry (
	icp,icpcreationdate,icpcommisiondate,eventstart,eventend,auditnumber,connection,reconcilliation,dedicatednsp,installation,proposedretailer,umldist,networkuserref,networkpricingaudit,distpricecat,distlosscat,chargeablecapacity,reference,reconciliationauditnumber,retailer,profile,reconciliationuserreference,meteringaudit,metercontact,category,metertypehhr,metertypenhh,metertypeunm,metertypepp,ami,dailyunmeteredkwh,unmeteredretailer,meterregister,metermultiplier,meteruserref,statusaudit,icpstatus,icpstatusreason,statususerref,addressaudit,addressunit,addressnumber,addressregion,addressstreet,addresstown,  
addresspostcode,addressuserref,gps_easting,gps_northing,poc,shared_icp_list,direct_billed_details,generationcapacity,generationfueltype,initial_energisationdate,networkidentifier,directbilledstatus,directbilleddetails,anzsic,proposedmep,unmflag,unmloadtrader,switchInProgressMEP,switchInProgress,submissionTypeHHR,submissionTypeNHH)
VALUES (:icp, :icpcreationdate, :icpcommisiondate, :eventstart, :eventend, :auditnumber, :connection, :reconcilliation, :dedicatednsp, :installation, :proposedretailer, :umldist, :networkuserref, :networkpricingaudit, :distpricecat, :distlosscat, :chargeablecapacity, :reference, :reconciliationauditnumber, :retailer, :profile, :reconciliationuserreference, :meteringaudit, :metercontact, :category, :metertypehhr, :metertypenhh, :metertypeunm, :metertypepp, :ami, :dailyunmeteredkwh, :unmeteredretailer, :meterregister, :metermultiplier, :meteruserref, :statusaudit, :icpstatus, :icpstatusreason, :statususerref, :addressaudit, :addressunit, :addressnumber, :addressregion, :addressstreet, :addresstown,   
 :addresspostcode, :addressuserref, :gps_easting, :gps_northing, :poc, :shared_icp_list, :direct_billed_details, :generationcapacity, :generationfueltype, :initial_energisationdate, :networkidentifier, :directbilledstatus, :directbilleddetails,  :anzsic, :proposedmep, :unmflag, :unmloadtrader, :switchInProgressMEP, :switchInProgress ,:submissionTypeHHR,:submissionTypeNHH) on duplicate key update ");
 

$test_stmt = $dbh->prepare("update electra_registry  set icp = :icp , icpcreationdate = :icpcreationdate");

/*
$class_vars = get_class_vars('icpRegistry');
$statement = "INSERT INTO electra_registry set ";
foreach ($class_vars as $name => $value) {
    $statement  .="$name = :$name , ";
    $columns .= " $name ,";
    $values .= " :$name ,";
    if($name!='icp'){
	$OnDuplicate .= " $name  = values($name), ";
    }
    //echo "$name\n";
}
$statement = "INSERT INTO electra_registry  (".$columns.") VALUES (".$values.")";
$statement  .=" on duplicate key update ";
$statement .= $OnDuplicate;
//$handle = fopen('/home/haydn/scm/sql.txt','w');
//fwrite($handle, "$statement");
//fclose($handle);
//echo $statement ."\n";

//$stmt = $dbh->prepare($statement);
*/

$sql = "INSERT INTO electra_registry ( icp , icpcreationdate , icpcommisiondate , eventstart , eventend , auditnumber , connection , reconcilliation , dedicatednsp , installation , proposedretailer , umldist , networkuserref , networkpricingaudit , distpricecat , distlosscat , chargeablecapacity , reference , reconciliationauditnumber , retailer , profile , reconciliationuserreference , meteringaudit , metercontact , category , metertypehhr , metertypenhh , metertypeunm , metertypepp , ami , dailyunmeteredkwh , unmeteredretailer , meterregister , metermultiplier , meteruserref , statusaudit , icpstatus , icpstatusreason , statususerref , addressaudit , addressunit , addressnumber , addressregion , addressstreet , addresstown , addresspostcode , addressuserref , gps_easting , gps_northing , poc , shared_icp_list , direct_billed_details , generationcapacity , generationfueltype , initial_energisationdate , networkidentifier , directbilledstatus , directbilleddetails , anzsic , proposedmep , unmflag , unmloadtrader , switchInProgressMEP , switchInProgress , installdetails ,submissionTypeHHR,submissionTypeNHH) VALUES ( :icp , :icpcreationdate , :icpcommisiondate , :eventstart , :eventend , :auditnumber , :connection , :reconcilliation , :dedicatednsp , :installation , :proposedretailer , :umldist , :networkuserref , :networkpricingaudit , :distpricecat , :distlosscat , :chargeablecapacity , :reference , :reconciliationauditnumber , :retailer , :profile , :reconciliationuserreference , :meteringaudit , :metercontact , :category , :metertypehhr , :metertypenhh , :metertypeunm , :metertypepp , :ami , :dailyunmeteredkwh , :unmeteredretailer , :meterregister , :metermultiplier , :meteruserref , :statusaudit , :icpstatus , :icpstatusreason , :statususerref , :addressaudit , :addressunit , :addressnumber , :addressregion , :addressstreet , :addresstown , :addresspostcode , :addressuserref , :gps_easting , :gps_northing , :poc , :shared_icp_list , :direct_billed_details , :generationcapacity , :generationfueltype , :initial_energisationdate , :networkidentifier , :directbilledstatus , :directbilleddetails , :anzsic , :proposedmep , :unmflag , :unmloadtrader , :switchInProgressMEP , :switchInProgress , :installdetails ,:submissionTypeHHR,:submissionTypeNHH) 
on duplicate key update  icpcreationdate  = values(icpcreationdate),  icpcommisiondate  = values(icpcommisiondate),  eventstart  = values(eventstart),  eventend  = values(eventend),  auditnumber  = values(auditnumber),  connection  = values(connection),  reconcilliation  = values(reconcilliation),  dedicatednsp  = values(dedicatednsp),  installation  = values(installation),  proposedretailer  = values(proposedretailer),  umldist  = values(umldist),  networkuserref  = values(networkuserref),  networkpricingaudit  = values(networkpricingaudit),  distpricecat  = values(distpricecat),  distlosscat  = values(distlosscat),  chargeablecapacity  = values(chargeablecapacity),  reference  = values(reference),  reconciliationauditnumber  = values(reconciliationauditnumber),  retailer  = values(retailer),  profile  = values(profile),  reconciliationuserreference  = values(reconciliationuserreference),  meteringaudit  = values(meteringaudit),  metercontact  = values(metercontact),  category  = values(category),  metertypehhr  = values(metertypehhr),  metertypenhh  = values(metertypenhh),  metertypeunm  = values(metertypeunm),  metertypepp  = values(metertypepp),  ami  = values(ami),  dailyunmeteredkwh  = values(dailyunmeteredkwh),  unmeteredretailer  = values(unmeteredretailer),  meterregister  = values(meterregister),  metermultiplier  = values(metermultiplier),  meteruserref  = values(meteruserref),  statusaudit  = values(statusaudit),  icpstatus  = values(icpstatus),  icpstatusreason  = values(icpstatusreason),  statususerref  = values(statususerref),  addressaudit  = values(addressaudit),  addressunit  = values(addressunit),  addressnumber  = values(addressnumber),  addressregion  = values(addressregion),  addressstreet  = values(addressstreet),  addresstown  = values(addresstown),  addresspostcode  = values(addresspostcode),  addressuserref  = values(addressuserref),  gps_easting  = values(gps_easting),  gps_northing  = values(gps_northing),  poc  = values(poc),  shared_icp_list  = values(shared_icp_list),  direct_billed_details  = values(direct_billed_details),  generationcapacity  = values(generationcapacity),  generationfueltype  = values(generationfueltype),  initial_energisationdate  = values(initial_energisationdate),  networkidentifier  = values(networkidentifier),  directbilledstatus  = values(directbilledstatus),  directbilleddetails  = values(directbilleddetails),  anzsic  = values(anzsic),  proposedmep  = values(proposedmep),  unmflag  = values(unmflag),  unmloadtrader  = values(unmloadtrader),  switchInProgressMEP  = values(switchInProgressMEP),  switchInProgress  = values(switchInProgress),  installdetails  = values(installdetails) , submissionTypeHHR = values(submissionTypeHHR), submissionTypeNHH = values(submissionTypeNHH)";


//$manual_statement = "insert into electra_registry SET icp = :icp, icpcreationdate = :icpcreationdate, auditnumber = :auditnumber, connection = :connection on duplicate key update ";
$manual_statement = "insert into electra_registry (icp, icpcreationdate, auditnumber, connection) VALUES( :icp, :icpcreationdate, :auditnumber,:connection ) on duplicate key update 
icpcreationdate=values(icpcreationdate), auditnumber=values(auditnumber), connection= values(connection)";

$stmt = $dbh->prepare($sql);



$metering_update ="


DROP TABLE IF EXISTS `mat_metering_info`;
CREATE TABLE `mat_metering_info` (
  `RecordType` varchar(12) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `icp` varchar(15) NOT NULL DEFAULT '',
  `MeteringInstallationNumber` int(11) NOT NULL DEFAULT '0',
  `MeteringComponentSerialNumber` char(25) DEFAULT NULL,
  `MeteringComponentType` char(1) DEFAULT NULL,
  `MeterType` char(3) DEFAULT NULL,
  `AMIFlag` char(6) DEFAULT NULL,
  `MeteringInstallationCategory` char(1) DEFAULT NULL,
  `CompensationFactor` decimal(6,0) DEFAULT NULL,
  `Owner` char(6) DEFAULT NULL,
  `NumberOfChannelRecords` smallint(6) DEFAULT NULL,
  `ChannelNumber` smallint(6) DEFAULT NULL,
  `NumberofDials` smallint(6) DEFAULT NULL,
  `RegisterContentCode` char(6) DEFAULT NULL,
  `PeriodofAvailability` smallint(6) DEFAULT NULL,
  `GenericPricecode`  char(16) DEFAULT NULL,
  `UnitofMeasurement` char(6) DEFAULT NULL,
  `EnergyFlowDirection` char(1) DEFAULT NULL,
  `AccumulatorType` char(1) DEFAULT NULL,
  `SettlementIndicator` char(6) DEFAULT NULL,
  `EventReading` char(12) DEFAULT NULL,
  `HighestMeteringCategory` smallint(6) DEFAULT NULL,
  `MeteringInstallationLocationCode` char(50) DEFAULT NULL,
  `ATHParticipantIdentifier` char(4) DEFAULT NULL,
  `MeteringInstallationType` char(3) DEFAULT NULL,
  `MeteringInstallationCertificationType` char(1) DEFAULT NULL,
  `MeteringInstallationCertificationDate` date DEFAULT NULL,
  `MeteringInstallationCertificationExpiryDate` date DEFAULT NULL,
  `ControlDeviceCertificationFlag` binary(1) DEFAULT NULL,
  `CertificationVariations` char(1) DEFAULT NULL,
  `CertificationVariationsExpiryDate` date DEFAULT NULL,
  `CertificationNumber` char(25) DEFAULT NULL,
  `MaximumInterrogationCycle` int(11) DEFAULT NULL,
  `LeasePriceCode` char(6) DEFAULT NULL,
  `NumberOfComponentRecords` smallint(6) DEFAULT NULL,
  `orderby1` bigint(20) NOT NULL DEFAULT '0',
  `orderby2` int(11) NOT NULL DEFAULT '0',
  `orderby3` varchar(25) NOT NULL DEFAULT '',
  `orderby4` varchar(6) CHARACTER SET utf8 NOT NULL DEFAULT '',
  KEY `metering_icp_idx` (`icp`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
delimiter ;;
CREATE TRIGGER `before_metering` BEFORE INSERT ON `mat_metering_info` FOR EACH ROW BEGIN

if new.RegisterContentCode is not null
and new.RegisterContentCode != ' '
and substr(new.RegisterContentCode,1,1) != '7'
THEN
        set new.GenericPriceCode = concat(new.RegisterContentCode, new.PeriodofAvailability);
ELSE
        set new.GenericPriceCode = '';
end if;

IF new.CompensationFactor = 0
THEN
        set new.CompensationFactor = null;
END IF;
IF new.MeterType = ''
THEN
        set new.MeterType = null;
END IF;
IF new.MeteringInstallationCategory = ''
THEN
        set new.MeteringInstallationCategory = null;
END IF;
IF new.RegisterContentCode = ''
THEN
        set new.RegisterContentCode = null;
END IF;
IF new.UnitofMeasurement = ''
THEN
        set new.UnitofMeasurement = null;
END IF;
IF new.EnergyFlowDirection = ''
THEN
        set new.EnergyFlowDirection = null;
END IF;
IF new.AccumulatorType = ''
THEN
        set new.AccumulatorType = null;
END IF;
IF new.SettlementIndicator = ''
THEN
        set new.SettlementIndicator = null;
END IF;

END;
 ;;
delimiter ;

insert into mat_metering_info  select * from vw_metering_info;

";


?>
