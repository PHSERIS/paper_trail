<?php
//© 2020 Partners HealthCare System, Inc. / Mass General Brigham
//All Rights Reserved.
namespace Partners\autoPdfUpload;

use ExternalModules\ExternalModules as EM;
use \REDCap as REDCap;
use \Files as Files;
require_once 'paper_trail_common.php';

global $Proj;

/**
 * Get the settings of the EM
 * This is the multi-use-case configuration
 */

// See how many use case we have defined
$defined_use_cases = $this->getProjectSetting('instance_use_case');
if ( !is_array($defined_use_cases) )
  return; // nothing to do - we need use cases defined and, somehow we don't have any
if ( count($defined_use_cases) < 1 )
  return; // no use cases defined - nothing to do

print var_export($this->getProjectSettings(),true);
exit(0);

$pdf_these_forms = array(); //$this->getProjectSetting('pdf_form');
$target_field = $this->getProjectSetting('target_field');
$forms_in_project = array_keys($Proj->forms);
foreach ( $this->getProjectSetting('pdf_form') as $f_c => $f ) {
  if ( in_array($f, $forms_in_project) ) {
    $pdf_these_forms[$f] = $f; // form is valid in this project - put it in an associative array so we de-duplicate
  }
}

if ( count($pdf_these_forms)<=0 ) return; // nothing to do

// service URL
$url = $this->getUrl('auto_pdf_service.php')."&NOAUTH&pid=". $project_id;

/**
 * C1 -> Automatic -
 * C2 -> Controlled - Depends on a Yes/No field
 * 99 -> Disabled
 */
$upload_type = $this->getProjectSetting('upload_type');
$file_prefix = $this->getProjectSetting('file_prefix');
$server_side_processing = $this->getProjectSetting('enable_cron');
$form_status = $this->getProjectSetting('complete_stat');
$allowed_form_status = array ("0","1","2");
if ( !in_array($form_status, $allowed_form_status) )
  $form_status = "0";

$pk = $Proj->table_pk;
$target_form = $Proj->metadata[$target_field]['form_name'];

switch ($upload_type) {
  case 'C1':
    $not_null_fields = $this->getProjectSetting('not_null_fields');
    // Check to see if the PDF generating condition is true
    $ok_to_generate = check_triggering_condition( $Proj, $record, $event_id, $repeat_instance, $not_null_fields, $upload_type );
    if ( $ok_to_generate ) {
      trigger_pdf_generation($server_side_processing, $project_id, $record, $pdf_these_forms, $target_field, $event_id, $target_form, $pk, $repeat_instance, $file_prefix, $url, $form_status);
    }

    break;

  case 'C2':
    $trigger_field = $this->getProjectSetting('trigger_field');
    // Check to see if the PDF generating condition is true
    $ok_to_generate = check_triggering_condition( $Proj, $record, $event_id, $repeat_instance, $trigger_field, $upload_type );
    if ( $ok_to_generate ) {
      trigger_pdf_generation($server_side_processing, $project_id, $record, $pdf_these_forms, $target_field, $event_id, $target_form, $pk, $repeat_instance, $file_prefix, $url, $form_status);
    }
    break;

  default: break; // Captures case 99 - Disabled
}

?>