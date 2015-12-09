<?php
//echo "set Script      ". xdebug_memory_usage()." memory used \n";
include '../../SCM_EIEP/eiep_files.php';
include '../../SCM_EIEP/eiep_helper_functions.php';
//echo "After includes  ". xdebug_memory_usage()." memory used \n";
$dsn     = 'mysql:host=wildflower.geek.nz;dbname=scm';
$options = array(
    PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'
);
$user = 'haydn';
$password = 'huxlyharla2014';

$dbh = new PDO($dsn, $user, $password, $options);
$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$query = "select primary_id, icp, units, aurora_eiep1.reportmonth, SUBSTRING_INDEX(filename, '/', -1) as filename from aurora_eiep1 inner join aurora_files on fk_aurora_files = idaurora_files where pricecode = '' and fixedvariable = 'V' limit 1,5";

$sth = $dbh->prepare($query);
$sth->execute();

//echo "after sql       ". xdebug_memory_usage()." memory used \n";
while ($row = $sth->fetch(PDO::FETCH_ASSOC)) {
	//echo "after fetch      ". xdebug_memory_usage()." memory used \n";
	//get the file $row['filename']
	$linenumber = 0;
	echo $row['icp']." , ".$row['filename']."\n";
	
	$filelines = file('blanks/'.$row['filename']);
	foreach ($filelines as $fileline){
		$line = str_getcsv(rtrim($fileline));
		//var_dump($line);
		//echo " $line[0] \n";
		if($line[0] == "DET"){
			$DET = new EIEP1_DET($line,$HDR->eiepversion);
			//echo "icp in $filename $DET->ICP \n";
			if($DET->ICP == $row['icp']&& $DET->reportMonth == $row['reportmonth'] && $DET->units == $row['units'] && $DET->fixedVariable == 'V'){
//				{
				//echo "Found the record in ".$row['filename']." $HDR->eiepversion row: $linenumber Rptmonth $DET->reportMonth Units: $DET->units pricecode: $DET->tariffCode\n";
				$sql = "update aurora_eiep1 set pricecode = '$DET->tariffCode' where primary_id = '".$row['primary_id']."';";
				//echo $sql."\n";
				$update =  $dbh->prepare($sql);
				$update->execute();
			}
			
			unset($DET);
			
		}elseif($line[0] == "HDR"){			
				$HDR = new EIEP1_HDR;
				$HDR->load_from_file($line,$row['filename']);				
		}	
			
		$linenumber++;
		//unset($line);
	}
	//echo "before unset ALL ". xdebug_memory_usage()." memory used \n";
	unset($filelines);
	unset($HDR);
	unset($fileline);
	//echo "after unset ALL  ". xdebug_memory_usage()." memory used \n";
} // end while
?>