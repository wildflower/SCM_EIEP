<?php

$fileslist = file('rfiles.txt');
foreach ($fileslist as $filename) {
	$filename = rtrim($filename);  // this is to get a list/array of filenames to process from a file
	
	$file = glob("$filename");
	if (sizeof($file) != 0){
		
		rename ($file[0], "./r/".basename($filename) );
	}else	{
		echo "can't find $filename \n";
	}

}

?>