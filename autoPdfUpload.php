<?php
namespace Partners\autoPdfUpload;
use \REDCap as REDCap;
include_once dirname(__FILE__)."/classes/common.php";

class autoPdfUpload extends \ExternalModules\AbstractExternalModule
{
    function redcap_save_record($project_id, $record, $instrument, $event_id, $group_id, $survey_hash, $response_id, $repeat_instance)
    {
        $main_ulr = __DIR__ . '/auto_pdf_upload_main.php';
        if (!@include($main_ulr)) ;
    }

//    CSS changes to upload field
    function redcap_survey_page($project_id, $record, $instrument, $event_id, $group_id, $survey_hash, $response_id, $repeat_instance)
    {
        $css_ulr = __DIR__ . '/hide_upload_labels.php';
        if (!@include($css_ulr)) ;
    }
    function redcap_data_entry_form($project_id, $record, $instrument, $event_id, $group_id, $repeat_instance)
    {
        $css_ulr = __DIR__ . '/hide_upload_labels.php';
        if (!@include($css_ulr)) ;
    }
}
