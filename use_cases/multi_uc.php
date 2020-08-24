<?php
//© 2020 Partners HealthCare System, Inc. / Mass General Brigham
//All Rights Reserved.
namespace Partners\autoPdfUpload;
use \REDCap as REDCap;
use \Files as Files;
use \Survey as Survey;
use ExternalModules\ExternalModules as EM;
global $Proj;


//logThis2('Multi use case reached',$project_id);

// ** Load Settings:
$pdf_this_form = $this->getProjectSetting('instance_pdf_form');
$target_field = $this->getProjectSetting('instance_target_field');
$upload_type = $this->getProjectSetting('instance_upload_type');
// $not_null_fields is an array
$not_null_fields = $this->getProjectSetting('instance_not_null_fields');
$trigger_field = $this->getProjectSetting('instance_trigger_field');
$file_prefix = $this->getProjectSetting('instance_file_prefix');
$complete_stat = $this->getProjectSetting('instance_complete_stat');
$field_array = $this->getProjectSetting('header_fields');
$label_array = $this->getProjectSetting('pdf_header_label');
$enable_cron = $this->getProjectSetting('enable_cron');
//$enable_survey_archive = $this->getProjectSetting('enable_survey_archive');

$pk = $Proj->table_pk;


foreach($pdf_this_form as $k => $current_form){

    $target_form = $Proj->metadata[$target_field[$k]]['form_name'];

    $instrument_list = [];
    foreach ($not_null_fields[$k] as $k_x => $v){
        $inx = $Proj->metadata[$v]['form_name'];
        $instrument_list[] = $inx;
    }
    $instrument_list = array_unique($instrument_list);
// "C1" automatic upload type
//    strlen(array_search($instrument, $instrument_list)) >= 1): is checking to see if all fields have been completed
//    when any of the forms containing the fields in the condition is being saved
    if ($upload_type[$k] == "C1" and strlen(array_search($instrument, $instrument_list)) >= 1) {

        $nnf_content = REDCap::getData('array', array($record), $not_null_fields[$k]);
        $num_of_null_fields = sizeof(array_filter($nnf_content[$record][$event_id], function($x) { return empty($x); }));

        if($num_of_null_fields == 0) {

            if($enable_cron == "1"){
                // ** Use External Module table for setting Cron Flag(s) and pending job list
                // ** Check for existing pending jobs and append to them
                $pending_jobs = json_decode($this->getProjectSetting("pending_jobs"), TRUE); // decode json list of pending jobs

                // ** Add/append current form to pending jobs Array
                // ** if pending_jobs are empty then start index at 0, if not then add one to the last index

                $index = end(array_keys($pending_jobs)) + 1;

                // ** generate PDF header
                if (sizeof($field_array) > 0) {
                    $pdf_custom_header_text = build_pdf_header($field_array, $label_array, $project_id, $record, $event_id);
                } else {
                    $pdf_custom_header_text = "";
                }

//                $archive_this = $enable_survey_archive[$k] == TRUE ? "true" : "false";
                $archive_this = "false";
                // ** store record's details
                $pending_jobs[$index] =  array($record, $_GET['event_id'],$current_form,$_GET['instance'],
                    $pdf_custom_header_text, $target_field[$k], $target_form, $file_prefix[$k],$complete_stat[$k], $pk,
                    $archive_this,$repeat_instance);
                // encode pending jobs array and save it in module settings
                $pending_jobs_json = json_encode($pending_jobs, true);
                // ** Select where to save the new jobs:
                // 1. is Var 1 busy? Then save it to Var 2
                // 2. is Var 1 available? Then save it to Var 1
                if ($this->getProjectSetting('var_status') == 'busy'){
                    $this->setProjectSetting('buffer',$pending_jobs_json);
                } elseif ($this->getProjectSetting('var_status') == NULL
                    || $this->getProjectSetting('var_status') == 'ready'){
                    $this->setProjectSetting('pending_jobs',$pending_jobs_json);
                }
            } else {
                if (sizeof($field_array) > 0) {
                    $pdf_custom_header_text = build_pdf_header($field_array, $label_array, $project_id, $record, $event_id);
                } else {
                    $pdf_custom_header_text = "";
                }

                generate_and_upload_pdf($project_id, $record, $pdf_this_form[$k], $target_field[$k], $event_id, $target_form, $pk, $file_prefix[$k], $complete_stat[$k], $pdf_custom_header_text,$archive_this,$repeat_instance);
            }
        }
    }

// Get instrument hosting the trigger field
    $trigger_instrument = $Proj->metadata[$trigger_field[$k]]['form_name'];

    if ($upload_type[$k] == "C2" and $instrument == $trigger_instrument) {

        $trigger_field_content = REDCap::getData('array', array($record), $trigger_field[$k]);
        $num_of_null_fields = sizeof(array_filter($trigger_field_content[$record][$event_id], function($x) { return empty($x); }));

        if($num_of_null_fields == 0) {

            if ($enable_cron == 1){
                // ** Use External Module table for setting Cron Flag(s) and pending job list
                // ** Check for existing pending jobs and append to them
                $pending_jobs = json_decode($this->getProjectSetting("pending_jobs"), TRUE); // decode json list of pending jobs

                // ** Add/append current form to pending jobs Array
                // ** if pending_jobs are empty then start index at 0, if not then add one to the last index
                $index = end(array_keys($pending_jobs)) + 1;

                // ** generate PDF header
                if (sizeof($field_array) > 0) {
                    $pdf_custom_header_text = build_pdf_header($field_array, $label_array, $project_id, $record, $event_id);
                } else {
                    $pdf_custom_header_text = "";
                }

//                $archive_this = $enable_survey_archive[$k] == TRUE ? TRUE : FALSE;
                $archive_this = "false";
                // ** store record's details
                $pending_jobs[$index] =  array($record, $_GET['event_id'],$current_form,$_GET['instance'],
                    $pdf_custom_header_text, $target_field[$k], $target_form, $file_prefix[$k],$complete_stat[$k], $pk,
                    $archive_this,$repeat_instance);
                // encode pending jobs array and save it in module settings
                $pending_jobs_json = json_encode($pending_jobs, true);
                // ** Select where to save the new jobs:
                // 1. is Var 1 busy? Then save it to Var 2
                // 2. is Var 1 available? Then save it to Var 1
                if ($this->getProjectSetting('var_status') == 'busy'){
                    $this->setProjectSetting('buffer',$pending_jobs_json);
                } elseif ($this->getProjectSetting('var_status') == NULL
                    || $this->getProjectSetting('var_status') == 'ready'){
                    $this->setProjectSetting('pending_jobs',$pending_jobs_json);
                }
            } else {
                // Build custom PDF header
                if (sizeof($field_array) > 0) {
                    $pdf_custom_header_text = build_pdf_header($field_array, $label_array, $project_id, $record, $event_id);
                } else {
                    $pdf_custom_header_text = "";
                }
                generate_and_upload_pdf($project_id, $record, $pdf_this_form[$k], $target_field[$k], $event_id, $target_form, $pk, $file_prefix[$k], $complete_stat[$k], $pdf_custom_header_text,$archive_this, $repeat_instance);
            }
        }
    }

}

function build_pdf_header($field_array, $label_array, $project_id, $record, $event_id){

    $pdf_custom_header_text = "";
    foreach ($field_array as $k => $field){
        if ($label_array[$k] != ""){
            // Field label
            $pdf_custom_header_text .= $label_array[$k];
            // Field value
            $pdf_custom_header_text .= REDCap::getData($project_id, 'array',$record, $field)[$record][$event_id][$field];
        } else {
            $pdf_custom_header_text .= REDCap::getData($project_id, 'array',$record, $field)[$record][$event_id][$field];
        }
    }
    return $pdf_custom_header_text;
}

function generate_and_upload_pdf($project_id, $record,$pdf_this_form,$target_field, $event_id, $target_form, $pk, $file_prefix,$complete_stat, $pdf_custom_header_text, $archive_this, $repeat_instance)
{
    // Get the content of the PDF for one record for one event for one instrument

    $pdf_content = REDCap::getPDF($record, $pdf_this_form, $event_id, $all_records = false, $repeat_instance = $repeat_instance,
        $compact_display = false, $appendToHeader = $pdf_custom_header_text, $appendToFooter = "", $hideSurveyTimestamp = false);
    // full path and filename of the file to upload
    $filename = $file_prefix . "_" . $project_id . "_" . $record . "_" . date("_Y-m-d_Hi") . ".pdf";
//    $filename_with_path = "/tmp/" . $filename;
    $filename_with_path = APP_PATH_TEMP . $filename; // Consider creating a ternary operation and allow user to determine their temp folder location

    // Save the PDF to a local web server directory
    $pdf_file = file_put_contents($filename_with_path, $pdf_content);

    $pdf_file_details = array(
        'name' => $filename,
        'size' => filesize($filename_with_path),
        'tmp_name' => $filename_with_path,
    );

    // Now we have to upload the file to the desired instrument
    $docId = Files::uploadFile($pdf_file_details);

    if ($docId != 0) {
        $data_to_save = array(
            $record => array(
                $target_form => array(
                    $pk => $record,
                    $target_field => $docId,
                    $target_form . "_complete" => $complete_stat)
            ));

        // Import the data with REDCap::saveData
        $response = REDCap::saveData(
            $project_id,
            'array', // The format of the data
            $data_to_save, // The Data
            'overwrite', // Overwrite behavior
            'YMD', // date format
            'flat', // type of the data
            null, // Group ID
            null, // data logging
            true, // perform auto calculations
            true, // commit data
            false, // log as auto calc
            true, // skip calc fields
            array(), // change reasons
            false, // return data comparison array
            false, // skip file upload fields - this is what we are actually updating
            false // remove locked fields
        );
    }

    if($archive_this == TRUE) {
        // ** Archive PDF Response
        // Note: Survey::archiveResponseAsPDF(record, event, instrument name, instance);
        Survey::archiveResponseAsPDF($record, $event_id, $pdf_this_form, $repeat_instance);
    }
}
?>