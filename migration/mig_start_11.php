<?php
//© 2020 Partners HealthCare System, Inc. / Mass General Brigham
//All Rights Reserved.
namespace Partners\autoPdfUpload;

use ExternalModules\ExternalModules as EM;
use \REDCap as REDCap;
use \Files as Files;
use \Survey as Survey;
use \Logging as Logging;

global $Proj;

$secret = $_POST["secret"];
$migs = $_POST["migs"];
$em_id = $_POST["em_id"];

if ($secret != "3232323") {
    die('Wrong passthrough code');
}

$mig_these = json_decode($_POST['migs']);

function config_exists($project_id, $config_name){
    $sql = "select `key` as 'setting'
from redcap_external_module_settings
where `key` = '$config_name'
and project_id = $project_id
and external_module_id = (
	select external_module_id from redcap_external_modules
	where directory_prefix = 'paper_trail')";

    $count = 0;
    $q = db_query($sql);
    while ($row = db_fetch_assoc($q)) {
//        print "here" . $row['test123'] . "\n";
//        print "setting: " . $row['setting'] . " --- " . "count: " . $row['count'] ."\n";
        if ($row['setting'] == $config_name){
            $count = $count+ 1;
        }
    }
    print "count: " . $count ."\n";

    if($count == 0){
        return 'nothing_found';
    } elseif ($count > 0) {
        return 'config_exists';
    }

}

//if(is_null(config_exists("",""))){
//        print "is null \n";
//}
//
//if(config_exists(14066, 'multi_upload_type') == ""){
//    print " is \"\" \n";
//}

function insertOrUpdateScript($case, $value, $key, $type, $project_id, $em_id){
    switch ($case) {
        case 'nothing_found':
            return $script = "INSERT INTO redcap_external_module_settings
                                          ( external_module_id, project_id, `key`, type, value)
                                          VALUES ($em_id, $project_id, '$key', '$type', \"$value\" )";
            break;
        case 'config_exists':
            return $script = "update redcap_external_module_settings
                                set `value` = \"$value\"
                                where project_id = $project_id
                                and `key` = \"$key\"
                                and external_module_id = (
	                                select external_module_id from redcap_external_modules
	                                where directory_prefix = \"paper_trail\")";
            break;
    }
}

$mapping = array("paper_trail_type" =>"paper_trail_type",
    "version" => "version",
    "multi_use_case_name"=> "not map 123",
    "multi_enable_cron" => "enable_cron",
    "multi_pdf_form" => "pdf_form",
    "multi_target_field" => "target_field",
    "multi_complete_stat" => "complete_stat",
    "multi_file_prefix" => "file_prefix",
    "multi_enable_survey_archive" => "enable_survey_archive",
    "multi_upload_type" => "upload_type",
    "multi_not_null_fields" => "not_null_fields",
    "multi_hide_css" => "hide_css");

foreach ($mig_these as $pid_v){
    print "$pid_v \n";
    // Get all em settings (for paper trail only, per project), except the one named enabled
    $sql = "SELECT * FROM redcap_external_module_settings
        WHERE project_id= $pid_v
        AND `key` != \"enabled\"
        AND external_module_id = (
	    select external_module_id from redcap_external_modules
	    where directory_prefix = \"paper_trail\")";
    $q = db_query($sql);
    while ($row = db_fetch_assoc($q)) { // config query

        // Mapping old keys ($row['key']) to new keys (keys of $mapping)
        if(array_search($row['key'], $mapping)) {
            if($row['key'] == "paper_trail_type"){
                $key = array_search($row['key'], $mapping);
                $type = "string";
//                $value = "ppt_2";
//
                if(config_exists($pid_v, $key) == 'config_exists'){
                    $insert_sql = "update redcap_external_module_settings
                                set `value` = \"ppt_2\"
                                where project_id = $pid_v
                                and `key` = \"paper_trail_type\"
                                and external_module_id = (
	                                select external_module_id from redcap_external_modules
	                                where directory_prefix = \"paper_trail\")";
                } else {
                    $insert_sql = insertOrUpdateScript('nothing_found', "\"ppt_2\"", $key, $type, $pid_v, $em_id); // insert
                }

            } elseif ($row['key'] == "version"){ // don't do anything

            }
            elseif ($row['key'] == "not_null_fields"){
                $key = array_search($row['key'], $mapping);
                $type = "json-array";
                if (strlen(strpos($row['value'], "\"")) < 1) {
                    $value = db_real_escape_string("[\"" . $row['value'] . "\"]");
                } else {
                    $value = db_real_escape_string("[" . $row['value'] . "]");
                }
                if(config_exists($pid_v, $key) == 'config_exists'){
                    $insert_sql = insertOrUpdateScript('config_exists', $value, $key, $type, $pid_v, $em_id); // update
                } else {
                    $insert_sql = "INSERT INTO redcap_external_module_settings
                                          ( external_module_id, project_id, `key`, type, value)
                                          VALUES ($em_id, $pid_v, '$key', '$type', '$value' )";
                }
            }
            elseif ($row['key'] == "pdf_form"){
                $key = array_search($row['key'], $mapping);
                $type = "json-array";
                // if pdf_form value does not contain double quotes in the second character
                // then add them to it.
                if (strlen(strpos($row['value'], "\"")) < 1){
                    $value = db_real_escape_string("[\"" . $row['value'] . "\"]");
                } else {
                    $value = db_real_escape_string("[" . $row['value'] . "]");
                }
                if(config_exists($pid_v, $key)  == 'config_exists'){
                    $insert_sql = insertOrUpdateScript('config_exists', $value, $key, $type, $pid_v, $em_id); // update
                } else {
                    $insert_sql = "INSERT INTO redcap_external_module_settings
                                          ( external_module_id, project_id, `key`, type, value)
                                          VALUES ($em_id, $pid_v, '$key', '$type', '$value' )";
                }
            }
            elseif ($row['key'] == "enable_survey_archive"){
                $key = array_search($row['key'], $mapping);
                $type = "json-array";
                $value = db_real_escape_string("[" . $row['value'] . "]");
                if(config_exists($pid_v, $key) == 'config_exists'){
                    $insert_sql = insertOrUpdateScript('config_exists', $value, $key, $type, $pid_v, $em_id); // update
                } else {
                    $insert_sql = "INSERT INTO redcap_external_module_settings
                                          ( external_module_id, project_id, `key`, type, value)
                                          VALUES ($em_id, $pid_v, '$key', '$type', '$value' )";
                }
            }
            else {
                $key = array_search($row['key'], $mapping);
                $type = "json-array";
                $value = db_real_escape_string("[\"" . $row['value'] . "\"]");
//                print "config exits: (0 or 1)" . config_exists($pid_v, $key) . "\n";
                if(config_exists($pid_v, $key) == 'config_exists'){
                    $insert_sql = insertOrUpdateScript('config_exists', $value, $key, $type, $pid_v, $em_id); // update
                } else {
                    $insert_sql = "INSERT INTO redcap_external_module_settings
                                          ( external_module_id, project_id, `key`, type, value)
                                          VALUES ($em_id, $pid_v, '$key', '$type', '$value' )";
                }
            }
//            print $insert_sql . "\n";
            $result = db_query($insert_sql);

            $insert_sql= "";
        }

        $old_key = $row['key'];

    }
    // insert Use Case name into Multi use-case settings
    $key = "multi_use_case_name";
    $type = "json-array";
    $value = db_real_escape_string("[\"Single Use-Case\"]");
    if(config_exists($pid_v, $key) == 'config_exists'){
        $insert_sql = insertOrUpdateScript('config_exists', $value, $key, $type, $pid_v, $em_id); // update
    } else {
        $insert_sql = "INSERT INTO redcap_external_module_settings
                                          ( external_module_id, project_id, `key`, type, value)
                                          VALUES ($em_id, $pid_v, '$key', '$type', '$value' )";
    }
    $result = db_query($insert_sql);

    $insert_sql = "";
}

// migrate those projects that have no paper trail type into option ppt_2
$no_ppt_seetings = "SELECT distinct t1.project_id, t2.`key`, t2.value
FROM
    (select project_id
	FROM redcap_external_module_settings
	where external_module_id = (
	select external_module_id from redcap_external_modules
	where directory_prefix = \"paper_trail\")) t1
LEFT JOIN
    (select project_id, `key`, value
	FROM redcap_external_module_settings
	WHERE `key` = \"paper_trail_type\") t2
	ON (t1.project_id = t2.project_id)
where t2.value is null
and t1.project_id is not null;";
$q = db_query($no_ppt_seetings);
while ($row = db_fetch_assoc($q)) {
    // Mapping old keys ($row['key']) to new keys (keys of $mapping)
    $pid_v = $row['project_id'];
    $key = "paper_trail_type";
    $type = "string";
    $value = db_real_escape_string("\"ppt_2\"");

    if(config_exists($pid_v, $key)  == 'config_exists'){
        $insert_sql = insertOrUpdateScript('config_exists', $value, $key, $type, $pid_v, $em_id); // update
    } else {
        $insert_sql = "INSERT INTO redcap_external_module_settings
                                          ( external_module_id, project_id, `key`, type, value)
                                          VALUES ($em_id, $pid_v, '$key', '$type', '$value' )";
    }

    $result = db_query($insert_sql);

    $insert_sql= "";
}
?>