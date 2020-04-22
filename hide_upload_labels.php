<?php
//© 2020 Partners HealthCare System, Inc. / Mass General Brigham
//All Rights Reserved.
namespace Partners\autoPdfUpload;
global $Proj;

$hide_css = $this->getProjectSetting('hide_css');
$target_field = $this->getProjectSetting('target_field');
$upload_form = $Proj->metadata[$target_field]['form_name'];

if ($hide_css == 'Y' and $instrument == $upload_form){
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