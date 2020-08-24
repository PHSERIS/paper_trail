<?php
//© 2020 Partners HealthCare System, Inc. / Mass General Brigham
//All Rights Reserved.
namespace Partners\autoPdfUpload;
//use function PartnersMGB\DataRollup\logThis;
use \REDCap as REDCap;
use \Files as Files;
use ExternalModules\ExternalModules as EM;

//include "debugLog/logFunction.php";

global $Proj;

$paper_trail_type = $this->getProjectSetting('paper_trail_type');
//logThis2($paper_trail_type,$project_id);

if ($paper_trail_type == 'ppt_1') {
    $main_ulr = __DIR__ . '/use_cases/single_uc.php';
    if (!@include($main_ulr)) ;
}

if ($paper_trail_type == 'ppt_2') {
  /*
   * NOT IMPLEMENTED as of version 1.3.2
   */
    //$main_ulr = __DIR__ . '/use_cases/multi_uc.php';
    //if (!@include($main_ulr)) ;
}

?>