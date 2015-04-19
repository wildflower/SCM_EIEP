<?php

$replace_stmt = $dbh->prepare("REPLACE INTO electra_registry (
	icp,icpcreationdate,icpcommisiondate,eventstart,eventend,auditnumber,connection,reconcilliation,dedicatednsp,installation,proposedretailer,umldist,networkuserref,networkpricingaudit,distpricecat,distlosscat,chargeablecapacity,reference,reconciliationauditnumber,retailer,profile,reconciliationuserreference,meteringaudit,metercontact,category,metertypehhr,metertypenhh,metertypeunm,metertypepp,ami,dailyunmeteredkwh,unmeteredretailer,meterregister,metermultiplier,meteruserref,statusaudit,icpstatus,icpstatusreason,statususerref,addressaudit,addressunit,addressnumber,addressregion,addressstreet,addresstown,  
addresspostcode,addressuserref,gps_easting,gps_northing,poc,shared_icp_list,direct_billed_details,generationcapacity,generationfueltype,initial_energisationdate,networkidentifier,directbilledstatus,directbilleddetails,anzsic,proposedmep,unmflag,unmloadtrader,switchInProgressMEP,switchInProgress )
VALUES (:icp, :icpcreationdate, :icpcommisiondate, :eventstart, :eventend, :auditnumber, :connection, :reconcilliation, :dedicatednsp, :installation, :proposedretailer, :umldist, :networkuserref, :networkpricingaudit, :distpricecat, :distlosscat, :chargeablecapacity, :reference, :reconciliationauditnumber, :retailer, :profile, :reconciliationuserreference, :meteringaudit, :metercontact, :category, :metertypehhr, :metertypenhh, :metertypeunm, :metertypepp, :ami, :dailyunmeteredkwh, :unmeteredretailer, :meterregister, :metermultiplier, :meteruserref, :statusaudit, :icpstatus, :icpstatusreason, :statususerref, :addressaudit, :addressunit, :addressnumber, :addressregion, :addressstreet, :addresstown,   
 :addresspostcode, :addressuserref, :gps_easting, :gps_northing, :poc, :shared_icp_list, :direct_billed_details, :generationcapacity, :generationfueltype, :initial_energisationdate, :networkidentifier, :directbilledstatus, :directbilleddetails,  :anzsic, :proposedmep, :unmflag, :unmloadtrader, :switchInProgressMEP, :switchInProgress ) on duplicate key update ");
 

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

$sql = "INSERT INTO electra_registry ( icp , icpcreationdate , icpcommisiondate , eventstart , eventend , auditnumber , connection , reconcilliation , dedicatednsp , installation , proposedretailer , umldist , networkuserref , networkpricingaudit , distpricecat , distlosscat , chargeablecapacity , reference , reconciliationauditnumber , retailer , profile , reconciliationuserreference , meteringaudit , metercontact , category , metertypehhr , metertypenhh , metertypeunm , metertypepp , ami , dailyunmeteredkwh , unmeteredretailer , meterregister , metermultiplier , meteruserref , statusaudit , icpstatus , icpstatusreason , statususerref , addressaudit , addressunit , addressnumber , addressregion , addressstreet , addresstown , addresspostcode , addressuserref , gps_easting , gps_northing , poc , shared_icp_list , direct_billed_details , generationcapacity , generationfueltype , initial_energisationdate , networkidentifier , directbilledstatus , directbilleddetails , anzsic , proposedmep , unmflag , unmloadtrader , switchInProgressMEP , switchInProgress , installdetails ) VALUES ( :icp , :icpcreationdate , :icpcommisiondate , :eventstart , :eventend , :auditnumber , :connection , :reconcilliation , :dedicatednsp , :installation , :proposedretailer , :umldist , :networkuserref , :networkpricingaudit , :distpricecat , :distlosscat , :chargeablecapacity , :reference , :reconciliationauditnumber , :retailer , :profile , :reconciliationuserreference , :meteringaudit , :metercontact , :category , :metertypehhr , :metertypenhh , :metertypeunm , :metertypepp , :ami , :dailyunmeteredkwh , :unmeteredretailer , :meterregister , :metermultiplier , :meteruserref , :statusaudit , :icpstatus , :icpstatusreason , :statususerref , :addressaudit , :addressunit , :addressnumber , :addressregion , :addressstreet , :addresstown , :addresspostcode , :addressuserref , :gps_easting , :gps_northing , :poc , :shared_icp_list , :direct_billed_details , :generationcapacity , :generationfueltype , :initial_energisationdate , :networkidentifier , :directbilledstatus , :directbilleddetails , :anzsic , :proposedmep , :unmflag , :unmloadtrader , :switchInProgressMEP , :switchInProgress , :installdetails ) on duplicate key update  icpcreationdate  = values(icpcreationdate),  icpcommisiondate  = values(icpcommisiondate),  eventstart  = values(eventstart),  eventend  = values(eventend),  auditnumber  = values(auditnumber),  connection  = values(connection),  reconcilliation  = values(reconcilliation),  dedicatednsp  = values(dedicatednsp),  installation  = values(installation),  proposedretailer  = values(proposedretailer),  umldist  = values(umldist),  networkuserref  = values(networkuserref),  networkpricingaudit  = values(networkpricingaudit),  distpricecat  = values(distpricecat),  distlosscat  = values(distlosscat),  chargeablecapacity  = values(chargeablecapacity),  reference  = values(reference),  reconciliationauditnumber  = values(reconciliationauditnumber),  retailer  = values(retailer),  profile  = values(profile),  reconciliationuserreference  = values(reconciliationuserreference),  meteringaudit  = values(meteringaudit),  metercontact  = values(metercontact),  category  = values(category),  metertypehhr  = values(metertypehhr),  metertypenhh  = values(metertypenhh),  metertypeunm  = values(metertypeunm),  metertypepp  = values(metertypepp),  ami  = values(ami),  dailyunmeteredkwh  = values(dailyunmeteredkwh),  unmeteredretailer  = values(unmeteredretailer),  meterregister  = values(meterregister),  metermultiplier  = values(metermultiplier),  meteruserref  = values(meteruserref),  statusaudit  = values(statusaudit),  icpstatus  = values(icpstatus),  icpstatusreason  = values(icpstatusreason),  statususerref  = values(statususerref),  addressaudit  = values(addressaudit),  addressunit  = values(addressunit),  addressnumber  = values(addressnumber),  addressregion  = values(addressregion),  addressstreet  = values(addressstreet),  addresstown  = values(addresstown),  addresspostcode  = values(addresspostcode),  addressuserref  = values(addressuserref),  gps_easting  = values(gps_easting),  gps_northing  = values(gps_northing),  poc  = values(poc),  shared_icp_list  = values(shared_icp_list),  direct_billed_details  = values(direct_billed_details),  generationcapacity  = values(generationcapacity),  generationfueltype  = values(generationfueltype),  initial_energisationdate  = values(initial_energisationdate),  networkidentifier  = values(networkidentifier),  directbilledstatus  = values(directbilledstatus),  directbilleddetails  = values(directbilleddetails),  anzsic  = values(anzsic),  proposedmep  = values(proposedmep),  unmflag  = values(unmflag),  unmloadtrader  = values(unmloadtrader),  switchInProgressMEP  = values(switchInProgressMEP),  switchInProgress  = values(switchInProgress),  installdetails  = values(installdetails) ";


//$manual_statement = "insert into electra_registry SET icp = :icp, icpcreationdate = :icpcreationdate, auditnumber = :auditnumber, connection = :connection on duplicate key update ";
$manual_statement = "insert into electra_registry (icp, icpcreationdate, auditnumber, connection) VALUES( :icp, :icpcreationdate, :auditnumber,:connection ) on duplicate key update 
icpcreationdate=values(icpcreationdate), auditnumber=values(auditnumber), connection= values(connection)";

$stmt = $dbh->prepare($sql);






?>