<?php
parse_str(implode('&', array_slice($argv, 1)), $_GET);

if(isset($_GET['file'])){
	$file = $_GET['file'];
}else{
	$file = 'eiep-status.txt';
}

$lines = file($file);

$xfiles = fopen('xfiles.txt','a');
$rfiles = fopen('rfiles.txt','a');
$hdrfiles = fopen('hdrfiles.txt','a');
$det = fopen('detrows.txt','a');

foreach($lines as $line_num => $line){
	 //echo "Line #{$line_num} : $line \n";
	 if (strstr($line, 'X file found')){
		 echo "Line #{$line_num} : $line \n";
		$parts = explode(' ', $line);
		echo "\n $parts[3] \n";
		fwrite($xfiles,"$parts[3]\n");
	 }
	 if (strstr($line, 'R file found')){
		 echo "Line #{$line_num} : $line \n";
		 $parts = explode(' ', $line);
		 echo "\n $parts[3] \n";
		 fwrite($rfiles,"$parts[3]\n");
	 }
	 if (strstr($line, 'Wrong HDR Line Count')){
		 echo "Line #{$line_num} : $line \n";
		 $parts = explode(' ', $line);
		 echo "\n $parts[7] \n";
		 fwrite($hdrfiles,"$parts[7]\n");
	 }
	 if (strstr($line, '# '))
	 {
		 echo "Line #{$line_num} : $line \n";
		 $parts = explode(' ', $line);
		// fwrite($det,"$parts[1]\n");
	 }
	 if ((strpos($line, '0000') !== false) && (strpos($line, '0000') < 5) ){
		 echo "Line #{$line_num} : $line \n";
		 $parts = explode(',', $line);
		 array_unshift($parts,'DET');
		 $line = implode(',',$parts);
		// fwrite($det,"$line");
	 }
	 
}
fclose($xfiles);
fclose($rfiles);
fclose($hdrfiles);
fclose($det);
?>