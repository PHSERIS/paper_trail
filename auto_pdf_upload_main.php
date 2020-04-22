<?php
//© 2020 Partners HealthCare System, Inc. / Mass General Brigham
//All Rights Reserved.
namespace Partners\autoPdfUpload;
use \REDCap as REDCap;
use \Files as Files;
use ExternalModules\ExternalModules as EM;

global $Proj;

$pdf_this_form = $this->getProjectSetting('pdf_form');
$target_field = $this->getProjectSetting('target_field');
$upload_type = $this->getProjectSetting('upload_type');

$not_null_fields = $this->getProjectSetting('not_null_fields');

$trigger_field = $this->getProjectSetting('trigger_field');
$file_prefix = $this->getProjectSetting('file_prefix');

$pk = $Proj->table_pk;
$target_form = $Proj->metadata[$target_field]['form_name'];
//$Proj->project['pdf_show_logo_url'] = '0'; // Auto-remove the REDCap Logo from the pdf-file

$instrument_list = [];
foreach ($not_null_fields as $k => $v){
    $inx = $Proj->metadata[$v]['form_name'];
    $instrument_list[] = $inx;
}
$instrument_list = array_unique($instrument_list);

if ($upload_type == "C1" and strlen(array_search($instrument, $instrument_list)) >= 1) {

    $nnf_content = REDCap::getData('array', array($record), $not_null_fields);
    $num_of_null_fields = sizeof(array_filter($nnf_content[$record][$event_id], function($x) { return empty($x); }));

    if($num_of_null_fields == 0) {
        generate_and_upload_pdf($project_id, $record, $pdf_this_form, $target_field, $event_id, $target_form, $pk, $file_prefix);
    }
}

// Get instrument hosting the trigger field
$trigger_instrument = $Proj->metadata[$trigger_field]['form_name'];

if ($upload_type == "C2" and $instrument == $trigger_instrument) {

    $trigger_field_content = REDCap::getData('array', array($record), $trigger_field);
    $num_of_null_fields = sizeof(array_filter($trigger_field_content[$record][$event_id], function($x) { return empty($x); }));

    if($num_of_null_fields == 0) {
        generate_and_upload_pdf($project_id, $record, $pdf_this_form, $target_field, $event_id, $target_form, $pk, $file_prefix);
    }
}

function generate_and_upload_pdf($project_id, $record,$pdf_this_form,$target_field, $event_id, $target_form, $pk, $file_prefix)
{
    // Get the content of the PDF for one record for one event for one instrument

    $pdf_content = REDCap::getPDF($record, $pdf_this_form, $event_id, $all_records = false, $repeat_instance = 1,
        $compact_display = false, $appendToHeader = "", $appendToFooter = "", $hideSurveyTimestamp = false);
    // full path and filename of the file to upload
    $filename = $file_prefix . "_" . $project_id . "_" . $record . "_" . date("_Y-m-d_Hi") . ".pdf";
    $filename_with_path = "/tmp/" . $filename;

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
                    $target_form . "_complete" => 2)
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
}
?>