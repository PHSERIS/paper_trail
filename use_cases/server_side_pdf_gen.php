<?php

namespace Partners\autoPdfUpload;
use \REDCap as REDCap;
use \Project as Project;
//use ExternalModules\FrameworkVersion\Project as Project;
use ExternalModules\ExternalModules as EM;
use \Survey as Survey;
use \Files as Files;

global $Proj;
global $pdf_custom_header_text;

foreach($this->framework->getProjectsWithModuleEnabled() as $localProjectId)
{
    // ** Check on each project CRON setting is still enabled
    if($enable_cron = $this->getProjectSetting('enable_cron',$localProjectId) == 1) {

        $_GET['pid'] = $localProjectId;
        $this->setProjectSetting('var_status', 'busy', $localProjectId);
        // ** $pending_jobs = [record, event, instrument name, instance, pdf header,
        // $target_field, $target_form]
        $pending_jobs = json_decode($this->getProjectSetting("pending_jobs", $localProjectId), TRUE); // decode json list of pending jobs

        // Preface settings
        $pdf_econsent_system_enabled = TRUE;
        $pdf_auto_archive = 2;
        define("PROJECT_ID", $localProjectId);
        $Proj = new Project($localProjectId);
        // ** Begin processing of pending jobs for current project ($localProjectId)
        foreach ($pending_jobs as $jobs => $configs) {
            $instrument = $configs[2];
            $pdf_custom_header_text = $configs[4];

//            // ** Archive PDF Response
//            // Note: Survey::archiveResponseAsPDF(record, event, instrument name, instance);
//            Survey::archiveResponseAsPDF($configs[0], $configs[1], $configs[2], $configs[3]);

            // ** Upload PDF to target field
            $project_id = $localProjectId;
            $record = $configs[0];
            $pdf_this_form = $configs[2];
            $target_field = $configs[5];
            $event_id = $configs[1];
            $target_form = $configs[6];
            $file_prefix = $configs[7];
            $complete_stat = $configs[8];
            $pk = $configs[9];
            $archive_this = $configs[10];
            $repeat_instance = $configs[11];

            // ** Archive PDF Response
            // Note: Survey::archiveResponseAsPDF(record, event, instrument name, instance);
            if ($archive_this == "true") {
                Survey::archiveResponseAsPDF($configs[0], $configs[1], $configs[2], $configs[3]);
            }

//            $pdf_custom_header_text = $pdf_custom_header_text;

//            generate_and_upload_pdf_ss($localProjectId, $record,$configs[2],$configs[5],
//                $configs[1], $configs[6], $configs[7],$configs[8], $pdf_custom_header_text);
            // Get the content of the PDF for one record for one event for one instrument

            $pdf_content = REDCap::getPDF($record, $pdf_this_form, $event_id, $all_records = false, $repeat_instance = $repeat_instance,
                $compact_display = false, $appendToHeader = $pdf_custom_header_text, $appendToFooter = "", $hideSurveyTimestamp = false);
            // full path and filename of the file to upload
            $filename = $file_prefix . "_" . $localProjectId . "_" . $record . "_" . date("_Y-m-d_Hi") . ".pdf";
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

            $fileLocation =  APP_PATH_TEMP . "/PaperTrail_SS.txt";
            $now = date('m.d.y h:i:s A');
            $logThis = "[$now] >>> This is doc_id: $docId \n";
            file_put_contents($fileLocation, $logThis,FILE_APPEND | LOCK_EX);

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
                    $localProjectId,
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

        // ** Get buffer and assign it to pending jobs
        $buffer = json_decode($this->getProjectSetting("buffer", $localProjectId), TRUE);
        if (isset($buffer) and sizeof($buffer) > 0) {
            $this->setProjectSetting('pending_jobs', $buffer, $localProjectId);
            $this->removeProjectSetting('$buffer', $localProjectId);
        } else {
            $this->removeProjectSetting('pending_jobs', $localProjectId);
        }
        $this->setProjectSetting('var_status', 'ready', $localProjectId);
    }
}

// Use this for confirming PDF archive was successful.
//getPdfAutoArchiveFiles(&$Proj, $group_id=null, $doc_id=null)

?>