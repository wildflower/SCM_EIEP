<?php
error_reporting(E_ALL ^ E_NOTICE);

require_once 'Zend/Loader/Autoloader.php';

Zend_Loader_Autoloader::getInstance();

class LIST_HDR
{
    var $filetype;
    var $sender;
    var $recipient;
    var $date;
    var $time;
    var $numberOfDetailRecords;
    var $fileStatus;
    var $fk_files;
    
    function build_array()
    {
        return $assoc_Array = array(
            "filetype" => $this->filetype,
            "sender" => $this->sender,            
            "recipient" => $this->recipient,            
            "date" => $this->date,
            "time" => $this->time,            
            "numberOfDetailRecords" => $this->numberOfDetailRecords,
	    "filestatus" => $this->fileStatus,
	    "$fk_files" => $this->fk_files
        );
    }
    
    function LIST_HDR($lineDetails)
    {
        $this->filetype              = $lineDetails[1];	
        $this->sender                = $lineDetails[2];        
        $this->recipient             = $lineDetails[3];
        $this->date         = $lineDetails[4];
        $this->time         = $lineDetails[5];
        $this->numberOfDetailRecords            = $lineDetails[6];
	$this->fileStatus = "I";
    }
    
    function validate()
    {
     global $processing_status;
    global $errors;
        $filters     = array();
        $date        = new Zend_Validate_Date(array(
            'format' => 'd/m/Y'
        ));
        $time        = new Zend_Validate_Date(array(
            'format' => 'G:i:s'
        ));
    }
    
}

class VALIDATE_LIST_DET
{
    public    $filters =  null;
    public $validators = null;
    
    function __construct(){
    
    $this->validators = array(
            'ICP' => array(
                'Alnum',
                new Zend_Validate_StringLength(array(
                    'max' => 15,
                    'min' => 15
                ))
            )
	    );
    
    }   
}

class LIST_DET{

var $ICP;

    function __construct($lineDetails)
    {
        $this->ICP                   = $lineDetails[1];
	}
	
    function build_array()
    {
        return $assoc_Array = array(
            "ICP" => $this->ICP);
	}
	
	function isUBRecord()
	{
		return FALSE;
	}  
}

class EIEP1_HDR
{
    var $filetype;
    var $eiepversion;
    var $sender;
    var $onbehalfsender;
    var $recipient;
    var $reportRunDate;
    var $reportRunTime;
    var $fileid;
    var $numberOfDetailRecords;
    var $reportPeriodStartDate;
    var $reportPeriodEndDate;
    var $reportMonth;
    var $utilityType;
    var $fileStatus;
    var $isValidFilename;
    var $lineCountIsValid;
    var $database_action;
   
    
    function validate()
    {
     global $processing_status;
    global $errors;
        $filters     = array();
        $date        = new Zend_Validate_Date(array(
            'format' => 'd/m/Y'
        ));
        $time        = new Zend_Validate_Date(array(
            'format' => 'G:i:s'
        ));
        $reportmonth = new Zend_Validate_Date(array(
            'format' => 'ym'
        ));
        
        $validators = array(
            'filetype' => array(
                'Alpha',
                new Zend_Validate_StringLength(array(
                    'max' => 7
                ))
            ),
            'sender' => array(
                'Alpha',
                new Zend_Validate_StringLength(array(
                    'max' => 4
                ))
            ),
            'onbehalfsender' => array(
                'Alpha',
                new Zend_Validate_StringLength(array(
                    'max' => 4
                ))
            ),
            'recipient' => array(
                'Alpha',
                new Zend_Validate_StringLength(array(
                    'max' => 4
                ))
            ),
            'reportRunDate' => $date,
            'reportRunTime' => $time,
            'fileid' => array(
                'Digits',
                new Zend_Validate_StringLength(array(
                    'max' => 12
                ))
            ),
            'numberOfDetailRecords' => array(
                'Digits',
                new Zend_Validate_StringLength(array(
                    'max' => 8
                ))
            ),
            'reportPeriodStartDate' => $date,
            'reportPeriodEndDate' => $date,
            'reportMonth' => $reportmonth,
            'utilityType' => new Zend_Validate_InArray(array(
                'E',
                'G'
            )),
            'fileStatus' => new Zend_Validate_InArray(array(
                'I',
                'X',
                'R'
            ))
        );
        
        $input = new Zend_Filter_Input($filters, $validators, $this->build_array());
        if ($input->isValid()) {
            echo "EIEP1 HDR OK\n";
	    fwrite($processing_status,"EIEP1 HDR OK\n");
	    //gotta flip the dates over to fit them into MySQL
	   $date = new Zend_Date($input->reportPeriodStartDate,'d/m/Y');		
	   $this->reportPeriodStartDate = $date->toString('Y-m-d');
	   $date = new Zend_Date($input->reportPeriodEndDate,'d/m/Y');
	  $this->reportPeriodEndDate = $date->toString('Y-m-d');
	  $date = new Zend_Date($input->reportRunDate,'d/m/Y');
	  $this->reportRunDate = $date->toString('Y-m-d');
        } else {
		// How to get this into store_header_details($dbh,$filename,$HDR,TRUE);
            echo "Something is invalid in here\n";
	    fwrite($errors,"Something is invalid in the HDR here\n");
        }
        
    }
    
    function build_array()
    {
        return $assoc_Array = array(
            "filetype" => $this->filetype,
            "sender" => $this->sender,
            "onbehalfsender" => $this->onbehalfsender,
            "recipient" => $this->recipient,
            "reportRunDate" => $this->reportRunDate,
            "reportRunTime" => $this->reportRunTime,
            "fileid" => $this->fileid,
            "numberOfDetailRecords" => $this->numberOfDetailRecords,
            "reportPeriodStartDate" => $this->reportPeriodStartDate,
            "reportPeriodEndDate" => $this->reportPeriodEndDate,
            "reportMonth" => $this->reportMonth,
            "utilityType" => $this->utilityType,
            "fileStatus" => $this->fileStatus,
            "fk_files" => $this->fk_files,
	    "database_action" => $this->database_action
        );
    }
    
    
    function EIEP1_HDR($lineDetails){
	if ($lineDetails[2] =='10'){    
		$this->filetype              = $lineDetails[1];
		$this->eiepversion              = $lineDetails[2];
		$this->sender                = $lineDetails[3];
		$this->onbehalfsender        = $lineDetails[4];
		$this->recipient             = $lineDetails[5];
		$this->reportRunDate         = $lineDetails[6];
		$this->reportRunTime         = $lineDetails[7];
		$this->fileid                = $lineDetails[8];
		$this->numberOfDetailRecords = $lineDetails[9];
		$this->reportPeriodStartDate = $lineDetails[10];
		$this->reportPeriodEndDate   = $lineDetails[11];
		$this->reportMonth           = $lineDetails[12];
		$this->utilityType           = $lineDetails[13];
		$this->fileStatus            = $lineDetails[14];        
	}else{
		$this->filetype              = $lineDetails[1];	
		$this->sender                = $lineDetails[2];
		$this->onbehalfsender        = $lineDetails[3];
		$this->recipient             = $lineDetails[4];
		$this->reportRunDate         = $lineDetails[5];
		$this->reportRunTime         = $lineDetails[6];
		$this->fileid                = $lineDetails[7];
		$this->numberOfDetailRecords = $lineDetails[8];
		$this->reportPeriodStartDate = $lineDetails[9];
		$this->reportPeriodEndDate   = $lineDetails[10];
		$this->reportMonth           = $lineDetails[11];
		$this->utilityType           = $lineDetails[12];
		$this->fileStatus            = $lineDetails[13];
	}
	
	switch ($this->fileStatus ){
		case "I":
			$this->database_action = "C";
		break;
		case "X":
			$this->database_action = "D";
		break;
		case "R":
			$this->database_action = "U";
		break;	
	
	}
    
    }
    
}

class VALIDATE_EIEP1_DET
{
    public    $filters =  null;
    public $validators = null;
    
    function __construct(){
    
	$MyUnits = new Zend_Filter();
	$MyUnits->addFilter(new MyUnitsFilter());
	$myStatus = new Zend_filter();
	$myStatus->addFilter(new MyStatusFilter());
	$myTariffCode = new Zend_filter();
	$myTariffCode->addFilter(new MyTariffCodeFilter());
	$myTariffRate = new Zend_filter();
	$myTariffRate->addFilter(new MyTariffRateFilter());
	$my85Decimal = new Zend_Filter();
	$my85Decimal->addFilter(new My85DecimalFilter()); 
	
        $this->filters = array(
	'units' => $MyUnits,
	'status' => array($myStatus,'StringToUpper'),
	'fixedVariable' => 'StringToUpper',
	'tariffCode' => $myTariffCode,
	'tariffRate' => $myTariffRate,
	'networkcharge' => $my85Decimal	) ;
        
        $date        = new Zend_Validate_Date(array(
            'format' => 'd/m/Y'
        ));
        $time        = new Zend_Validate_Date(array(
            'format' => 'G:i:s'
        ));
        $reportmonth = new Zend_Validate_Date(array(
            'format' => 'ym'
        ));
        
        $this->validators = array(
            'ICP' => array(
                'Alnum',
                new Zend_Validate_StringLength(array(
                    'max' => 15,
                    'min' => 15
                ))
            ),
            'reportPeriodStartDate' => $date,
            'reportPeriodEndDate' => $date,
            'tariffDesc' => array(
                new Zend_Validate_StringLength(array(
                    'max' => 50,
                    'min' => 0
                )),
                'allowEmpty' => true
            ),
            'unitType' => array(
                'Alnum',
                new Zend_Validate_StringLength(array(
                    'max' => 25
                )),
                'allowEmpty' => true
            ),
            'units' => array(
                'Float',
                new Zend_Validate_StringLength(array(
                    'max' => 15
                ))
            ),
            'status' => new Zend_Validate_InArray(array(
                'ES',
                'RD',
                'RV',
                'FL',
                'VA',
		'Q1'
            )),
            'busName' => array(
                'Alnum',
                'allowEmpty' => true
            ),
            'distId' => 'Alpha',
            'tariffCode' => new Zend_Validate_StringLength(array( 'max' => 25 )),
            'tariffRate' => array(
                'Float',
                'allowEmpty' => true
            ),
            'fixedVariable' => new Zend_Validate_InArray(array(
                'V',
                'F',
				'S'
            )),
            'chargeableDays' => array(
                'Int',
                'allowEmpty' => true
            ),
            'networkCharge' => array(
               // 'Float',
                'allowEmpty' => true
            ),
            'reportMonth' => $reportmonth,
            'customerNo' => array(                
                new Zend_Validate_StringLength(array(
                    'max' => 15
                )),
                'allowEmpty' => true
            ),
            'consumerNo' => array(                
                new Zend_Validate_StringLength(array(
                    'max' => 15
                )),
                'allowEmpty' => true
            ),
            'invoiceDate' => array(
                $date,
                'allowEmpty' => true
            ),
            'invoiceNumber' => array(
                'Alnum',
                new Zend_Validate_StringLength(array(
                    'max' => 20
                )),
                'allowEmpty' => true
            )            
        );
    }
}
class EIEP1_DET
{
    var $ICP;
    var $reportPeriodStartDate;
    var $reportPeriodEndDate;
    var $tariffDesc;
    var $unitType;
    var $units;
    var $status;
    var $busName;    
    var $distId;
    var $spare;
    var $tariffCode;
    var $tariffRate;
    var $fixedVariable;
    var $chargeableDays;
    var $networkCharge;
    var $registerContentCode;
    var $periodOfAvailability;
    var $reportMonth;
    var $customerNo;
    var $consumerNo;
    var $invoiceDate;
    var $invoiceNumber;
    

    
    function __construct($lineDetails,$version)
    {
	if($version){
		$this->ICP                   = $lineDetails[1];
		$this->reportPeriodStartDate = $lineDetails[2];
		$this->reportPeriodEndDate   = $lineDetails[3];
		$this->tariffDesc            = $lineDetails[4];
		$this->unitType              = $lineDetails[5];
		$this->units                 = $lineDetails[6];
		$this->status                = $lineDetails[7];
		$this->busName               = $lineDetails[8];
		$this->distId                = $lineDetails[9];
		$this->spare                 = $lineDetails[10];
		$this->tariffCode            = $lineDetails[11];
		$this->tariffRate            = $lineDetails[12];
		$this->fixedVariable         = $lineDetails[13];
		$this->chargeableDays        = $lineDetails[14];
		$this->networkCharge         = $lineDetails[15];
	
		$this->registerContentCode = $lineDetails[16];
		$this->periodOfAvailability = $lineDetails[17];
    
		$this->reportMonth           = $lineDetails[18];
		$this->customerNo            = $lineDetails[19];
		$this->consumerNo            = $lineDetails[20];
		$this->invoiceDate           = $lineDetails[21];
		$this->invoiceNumber         = $lineDetails[22];
        }else{
		$this->ICP                   = $lineDetails[1];
		$this->reportPeriodStartDate = $lineDetails[2];
		$this->reportPeriodEndDate   = $lineDetails[3];
		$this->tariffDesc            = $lineDetails[4];
		$this->unitType              = $lineDetails[5];
		$this->units                 = $lineDetails[6];
		$this->status                = $lineDetails[7];
		$this->busName               = $lineDetails[8];
		$this->distId                = $lineDetails[9];
		$this->spare                 = $lineDetails[10];
		$this->tariffCode            = $lineDetails[11];
		$this->tariffRate            = $lineDetails[12];
		$this->fixedVariable         = $lineDetails[13];
		$this->chargeableDays        = $lineDetails[14];
		$this->networkCharge         = $lineDetails[15];	
    
		$this->reportMonth           = $lineDetails[16];
		$this->customerNo            = $lineDetails[17];
		$this->consumerNo            = $lineDetails[18];
		$this->invoiceDate           = $lineDetails[19];
		$this->invoiceNumber         = $lineDetails[20];
		
		$this->registerContentCode = 0;
		$this->periodOfAvailability = 0;
	
	}
	
    }
    function write($filehandler)
    {
	$string = "$this->ICP,$this->reportPeriodStartDate,$this->reportPeriodEndDate,$this->tariffDesc,$this->unitType,$this->units,$this->status,$this->busName,$this->distId,$this->spare,$this->tariffCode,$this->tariffRate,$this->fixedVariable,$this->chargeableDays,$this->networkCharge,$this->reportMonth,$this->customerNo,$this->consumerNo,$this->invoiceDate,$this->invoiceNumber\n";
	fwrite($filehandler,$string);
    }
    
       
    function isUBRecord()
    {
        if ($this->status == 'UB'){
	 
            return TRUE;
	    }
        else
            return FALSE;
    }
    
    function build_array()
    {
        return $assoc_Array = array(
            "ICP" => $this->ICP,
            "reportPeriodStartDate" => $this->reportPeriodStartDate,
            "reportPeriodEndDate" => $this->reportPeriodEndDate,
            "tariffDesc" => $this->tariffDesc,
            "unitType" => $this->unitType,
            "units" => $this->units,
            "status" => $this->status,
            "busName" => $this->busName,
            "distId" => $this->distId,
            "spare" => $this->spare,
            "tariffCode" => $this->tariffCode,
            "tariffRate" => $this->tariffRate,
            "fixedVariable" => $this->fixedVariable,
            "chargeableDays" => $this->chargeableDays,
            "networkCharge" => $this->networkCharge,
            "reportMonth" => $this->reportMonth,
            "customerNo" => $this->customerNo,
            "consumerNo" => $this->consumerNo,
            "invoiceDate" => $this->invoiceDate,
            "invoiceNumber" => $this->invoiceNumber
        );
    }
}

class EIEP3_HDR
{
    var $filetype;
    var $eiepversion;
    var $sender;
    var $onbehalfsender;
    var $recipient;
    var $reportRunDate;
    var $reportRunTime;
    var $fileid;
    var $numberOfDetailRecords;
    var $reportPeriodStartDate;
    var $reportPeriodEndDate;
    var $reportMonth;
    var $utilityType;
    var $fileStatus;
    
    
    
    function EIEP3_HDR($lineDetails)
    {
        $this->filetype              = $lineDetails[1];
	$this->eiepversion              = $lineDetails[2];
        $this->sender                = $lineDetails[3];
        $this->onbehalfsender        = $lineDetails[4];
        $this->recipient             = $lineDetails[5];
        $this->reportRunDate         = $lineDetails[6];
        $this->reportRunTime         = $lineDetails[7];
        $this->fileid                = $lineDetails[8];
        $this->numberOfDetailRecords = $lineDetails[9];
        $this->reportMonth           = $lineDetails[10];
        $this->utilityType           = $lineDetails[11];
        $this->fileStatus            = $lineDetails[12];
    }
    
     function build_array()
    {
        return $assoc_Array = array(
            "filetype" => $this->filetype,
            "sender" => $this->sender,
            "onbehalfsender" => $this->onbehalfsender,
            "recipient" => $this->recipient,
            "reportRunDate" => $this->reportRunDate,
            "reportRunTime" => $this->reportRunTime,
            "fileid" => $this->fileid,
            "numberOfDetailRecords" => $this->numberOfDetailRecords,
            "reportMonth" => $this->reportMonth,
            "utilityType" => $this->utilityType,
            "fileStatus" => $this->fileStatus
        );
    }
    
    
     function validate()
    {
    global $errors;
    global $processing_status;
        $filters     = array();
        $date        = new Zend_Validate_Date(array(
            'format' => 'd/m/Y'
        ));
        $time        = new Zend_Validate_Date(array(
            'format' => 'G:i:s'
        ));
        $reportmonth = new Zend_Validate_Date(array(
            'format' => 'ym'
        ));
        
        $validators = array(
            'filetype' => array(
                'Alpha',
                new Zend_Validate_StringLength(array(
                    'max' => 7
                ))
            ),
            'sender' => array(
                'Alpha',
                new Zend_Validate_StringLength(array(
                    'max' => 4
                ))
            ),
            'onbehalfsender' => array(
                'Alpha',
                new Zend_Validate_StringLength(array(
                    'max' => 4
                ))
            ),
            'recipient' => array(
                'Alpha',
                new Zend_Validate_StringLength(array(
                    'max' => 4
                ))
            ),
            'reportRunDate' => $date,
            'reportRunTime' => $time,
            'fileid' => array(
                'Digits',
                new Zend_Validate_StringLength(array(
                    'max' => 12
                ))
            ),
            'numberOfDetailRecords' => array(
                'Digits',
                new Zend_Validate_StringLength(array(
                    'max' => 8
                ))
            ),            
            'reportMonth' => $reportmonth,
            'utilityType' => new Zend_Validate_InArray(array(
                'E',
                'G'
            )),
            'fileStatus' => new Zend_Validate_InArray(array(
                'I',
                'X',
                'R'
            ))
        );
        
        $input = new Zend_Filter_Input($filters, $validators, $this->build_array());
        if ($input->isValid()) {
         //   echo "EIEP6 HDR OK\n";
	 fwrite($processing_status,"EIEP3 HDR OK \n");
        } else {
            echo "Something is invalid in here\n";
	    fwrite($errors,"Something is invalid in here HDR 3 \n");
        }
        
    }    
}


class VALIDATE_EIEP3_DET
{
    public    $filters =  null;
    public $validators = null;
    
    function __construct(){
	$MyDirection = new Zend_Filter();
	$MyDirection->addFilter(new MyDirectionFilter());	
        $this->filters = array(
		'direction'  => $MyDirection		
		);
        
        $date = new Zend_Validate_Date(array(
            'format' => 'd/m/Y'
        ));
        
        $this->validators = array(
            'ICP' => array(
                'Alnum',
                new Zend_Validate_StringLength(array(
                    'max' => 15,
                    'min' => 15
                ))
            ),
            'dataStreamIdentifier' =>  new Zend_Validate_StringLength(array('max' => 15 )),
            'status' => new Zend_Validate_InArray(array(
                'F',
                'E'
            )),
            'date' => $date,
            'tradingPeriod' => 'Int',
            'consumption' => 'Float',
            'reactiveEnergy' => array(
                'Float',
                'allowEmpty' => true
            ),
            'apparentEnergy' => array(
                'Float',
                'allowEmpty' => true
            ),
            'direction' => 
		new Zend_Validate_InArray(array( 'L', 'G'))
		,
            'dataStreamType' => array(
                'Alnum',
                'allowEmpty' => true
            )
        );
    }
    
}
class EIEP3_DET
{
    var $ICP;
    var $dataStreamIdentifier;
    var $status;
    var $date;
    var $tradingPeriod;
    var $consumption;
    var $reactiveEnergy;
    var $apparentEnergy;
    var $direction;
    var $dataStreamType;
    
    function EIEP3_DET($lineDetails)
    {
        $this->ICP                  = $lineDetails[1];
        $this->dataStreamIdentifier = $lineDetails[2];
        $this->status               = $lineDetails[3];
        $this->date                 = $lineDetails[4];
        $this->tradingPeriod        = $lineDetails[5];
        $this->consumption          = $lineDetails[6];
        $this->reactiveEnergy       = $lineDetails[7];
        $this->apparentEnergy       = $lineDetails[8];
        $this->direction            = $lineDetails[9];
        $this->dataStreamType       = $lineDetails[10];
    }
    
    function build_array()
    {
        return $assoc_Array = array(
            "ICP" => $this->ICP,
            "dataStreamIdentifier" => $this->dataStreamIdentifier,
            "status" => $this->status,
            "date" => $this->date,
            "tradingPeriod" => $this->tradingPeriod,
            "consumption" => $this->consumption,
            "reactiveEnergy" => $this->reactiveEnergy,
            "apparentEnergy" => $this->apparentEnergy,
            "direction" => $this->direction,
            "dataStreamType" => $this->dataStreamType
        );
    }
    
    function isUBRecord()
    {
        return FALSE;
    }   
    
}

class MyDirectionFilter implements Zend_Filter_Interface
{
    public function filter($value)
    {
        // perform some transformation upon $value to arrive on $valueFiltered
		if($value == ''){
			return 'L';}
		else{
			return $value;        
			}
    }
}

class MyUnitsFilter implements Zend_Filter_Interface
{
    public function filter($value)
    {
        // perform some transformation upon $value to arrive on $valueFiltered
		if($value == ''){
			return '0';}
		else{
			return $value;        
			}
    }
}

class MyStatusFilter implements Zend_Filter_Interface
{
    public function filter($value)
    {
        // perform some transformation upon $value to arrive on $valueFiltered
		if($value == ''){
			return 'ES';}
		else{
			return $value;        
			}
    }
}

class MyTariffCodeFilter implements Zend_Filter_Interface
{
    public function filter($value)
    {
        // perform some transformation upon $value to arrive on $valueFiltered
		if($value == ''){
			return 'F-SCM';}
		else{
			return $value;        
			}
    }
}


class MyTariffRateFilter implements Zend_Filter_Interface
{
    public function filter($value)
    {
        // perform some transformation upon $value to arrive on $valueFiltered
		if($value == ''){
			return '0.00';
			}
		else{
			$english_format_number = number_format($value, 5, '.', '');
			return $english_format_number;        
			}
    }
}
class MyDateFilter implements Zend_Filter_Interface
{
    public function filter($value)
    {
        // perform some transformation upon $value to arrive on $valueFiltered
		$date = new Zend_Date($value,'d/m/Y');
		return $date->toString('Y-m-d');
			
    }
}
class My85DecimalFilter implements Zend_Filter_Interface
{
    public function filter($value)
    {
	$english_format_number = number_format($value, 5, '.', '');
	return $english_format_number;
}
}
class EIEP_Filename{

	var $from;
	var $utilitytype;
	var $to;
	var $filetype;
	var $reportmonth;
	var $rundate;
	var $fileid;

	function EIEP_Filename($filename){
		$filename_parts = explode('_',strstr($filename,'.',true));
		$this->from = $filename_parts[0];
		$this->utilitytype = $filename_parts[1];
		$this->to = $filename_parts[2];
		$this->filetype = $filename_parts[3];
		$this->reportmonth = $filename_parts[4];
		$this->rundate = $filename_parts[5];
		$this->fileid = $filename_parts[6];	
	}
}

?>