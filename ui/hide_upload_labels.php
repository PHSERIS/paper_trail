<?php
//© 2020 Partners HealthCare System, Inc. / Mass General Brigham
//All Rights Reserved.
namespace Partners\autoPdfUpload;
global $Proj;

$paper_trail_type = $this->getProjectSetting('paper_trail_type');

if ($paper_trail_type == 'ppt_1') {
    $hide_css = $this->getProjectSetting('hide_css');
    $target_field = $this->getProjectSetting('target_field');
    $upload_form = $Proj->metadata[$target_field]['form_name'];
    $instrument_found = ($instrument == $upload_form) ? 1 : 0;

    if ($hide_css == 'Y' and $instrument_found == 1){
        $script = <<<SCRIPT
	<style type='text/css'>
		 .edoc-link, #$target_field-linknew {
		 visibility: hidden;
		 }
	</style>
SCRIPT;
    }
}

if ($paper_trail_type == 'ppt_2') {
    $target_field_array = $this->getProjectSetting('multi_target_field');
    $hide_css_array = $this->getProjectSetting('multi_hide_css');

    foreach($target_field_array as $k => $v){
        if ($instrument == $Proj->metadata[$v]['form_name']){
            if ($hide_css_array[$k] == 'Y'){
                $script .= <<<SCRIPT
	<style type='text/css'>
		 .fileuploadlink, .edoc-link, #$target_field_array[$k]-linknew {
		 visibility: hidden;
		 }
	</style>
SCRIPT;
            }
        }
    }
}

print $script;
?>