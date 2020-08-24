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
}

if ($paper_trail_type == 'ppt_2') {

    $target_field_array = $this->getProjectSetting('instance_target_field');

    $upload_form = [];
    foreach($target_field_array as $k => $v){
        $upload_form[$k] = $Proj->metadata[$v]['form_name'];
    }

    if (array_search($instrument, $upload_form) >= 0){
        $instrument_found = 1;
        $hide_css_array = $this->getProjectSetting('instance_hide_css');
        $target_field_array = $this->getProjectSetting('instance_target_field');
        $hide_css = $hide_css_array[array_search($instrument, $upload_form)];
        $target_field = $target_field_array[array_search($instrument, $upload_form)];
    }

}

if ($hide_css == 'Y' and $instrument_found == 1){
    $script = <<<SCRIPT
	<style type='text/css'>
		 .edoc-link, #$target_field-linknew {
		 visibility: hidden;
		 }
	</style>
SCRIPT;
}
print $script;
?>