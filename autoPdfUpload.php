<?php
//© 2020 Partners HealthCare System, Inc. / Mass General Brigham
//All Rights Reserved.
namespace Partners\autoPdfUpload;
use \REDCap as REDCap;
use \Files as Files;
include_once dirname(__FILE__)."/classes/common.php";
//include "debugLog/logFunction.php";

class autoPdfUpload extends \ExternalModules\AbstractExternalModule
{
//    function redcap_data_entry_form($project_id, $record, $instrument, $event_id, $group_id, $repeat_instance)
    function redcap_save_record($project_id, $record, $instrument, $event_id, $group_id, $survey_hash, $response_id, $repeat_instance)
    {
        $main_ulr = __DIR__ . '/auto_pdf_upload_main.php';
        if (!@include($main_ulr)) ;
    }

//    CSS changes to upload field
    function redcap_survey_page($project_id, $record, $instrument, $event_id, $group_id, $survey_hash, $response_id, $repeat_instance)
    {
        $css_ulr = __DIR__ . '/ui/hide_upload_labels.php';
        if (!@include($css_ulr)) ;
    }

    function server_side_pdf_generator_paper_trail()
    {
        $main_ulr =  __DIR__.'/use_cases/server_side_pdf_gen.php';
        if(!@include($main_ulr));
    }
    // testing
    function redcap_data_entry_form($project_id, $record, $instrument, $event_id, $group_id, $repeat_instance)
    {
        $css_ulr = __DIR__ . '/ui/hide_upload_labels.php';
        if (!@include($css_ulr)) ;
//        $econsentComplete = 1;
    }
    
//    function hook_every_page_top($project_id)
//    {
//        if(PAGE == 'FileRepository/index.php' && array_search(TRUE,$this->getProjectSetting('enable_survey_archive')) >= 0) {
//            $main_ulr =  __DIR__.'/ui/ui_enhancements.php';
//            if(!@include($main_ulr));
//        }
//    }
}
