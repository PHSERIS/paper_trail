<?php
//© 2020 Partners HealthCare System, Inc. / Mass General Brigham
//All Rights Reserved.
namespace Partners\autoPdfUpload;

use ExternalModules\ExternalModules as EM;
use \REDCap as REDCap;
use \Files as Files;
use \Survey as Survey;
use \Logging as Logging;

$HtmlPage = new \HtmlPage();
$HtmlPage->PrintHeaderExt();

$URI = explode("?",$_SERVER['REQUEST_URI'])[0];

function isProjectDeleted($project_id){
    $sql = "select project_id, project_name, date_deleted
from redcap_projects
where project_id = $project_id";

    $q = db_query($sql);
    while ($row = db_fetch_assoc($q)) {
        if (is_null($row['date_deleted'])){
            return 0;
        }
        else {
            return 1;
        }
    }

}

// Get all project that have had Paper Trail enabled with their current status
$sql_all_status = "select distinct t3.project_id, t3.key, t3.value as 'ppType', t4.key, t4.value as 'enabled'
from 
(
SELECT distinct t1.project_id, t2.`key`, t2.value
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
	ON (t1.project_id = t2.project_id)) t3
Left JOIN
	(select project_id, `key`, value 
	FROM redcap_external_module_settings 
	WHERE `key` = \"enabled\"
	and external_module_id = (
	select external_module_id from redcap_external_modules
	where directory_prefix = \"paper_trail\") ) t4
	ON (t3.project_id = t4.project_id)
order by t3.project_id";
$q = db_query($sql_all_status);

$count = 0;
$mig_count = 0;
while ($row = db_fetch_assoc($q)) {

    $em_id = $row['external_module_id'];
    $key = $row['key'];
    $type = $row['type'];
    $value = $row['ppType'];
    $status = $row['enabled'];
    $pid_mod = db_real_escape_string($row['project_id']);

    $t=isProjectDeleted($row['project_id']);
//    print $t;

    if (strlen($pid_mod) > 0 and isProjectDeleted($row['project_id']) == 0){
        $count = $count + 1;
        $pre_v1333 = $status == "true" ? "" : "*";
        $v1333 = $value == "ppt_2" ? pillColor("green") : pillColor("yellow");
        $mig_count = $value == "ppt_2" ? $mig_count + 1 : $mig_count;

        if ($value != "ppt_2" or sizeof($value) == 0){
            $mig_these[] =  $pid_mod;
        }
        $data[] = array($count, "<td>".$pid_mod . $pre_v1333, pillColor("green"), $v1333);
    }

}

$sql_em_id = "select external_module_id from redcap_external_modules
	where directory_prefix = \"paper_trail\"";
$q = db_query($sql_em_id);
while ($row = db_fetch_assoc($q)) {
    $em_id = $row["external_module_id"];
}

function pillColor($color){
    switch ($color){
        case "green":
            return "<td class='greenPill'>&check;";
        case "yellow":
            return "<td class='yellowPill'>&#10060;";
        case "NCE":
            return "<td class='greenPill'>NCE";
    }
}

?>

<link rel="stylesheet" type="text/css" href="<?php print htmlspecialchars($URI."?prefix=paper_trail&page=migration/style.css")?>">

<div class="h2">
    <h2>Migrating Projects to Paper Trail v.1.3.3+</h2>
    <p>Version 1.3.3+ of the Paper Trail external module contains substantial changes that break the Paper Trail functionality in project using and early version.
        In order to facility the migration of these projects into v1.3.3+ of the Paper Trail we developed a mechanism of migrating the old settings into the new settings.
        <br><b> You do not have to migrate projects if you're using the Paper Trail for the first time.
        <br>* see footnote for additional information<b></p>
</div>
<?php
$table_headers = array("#", "Project ID", "< v1.3.3", "v1.3.3");

if (count($data) > 0): ?>
    <div class="bg"></div>
    <div class="bg bg2"></div>
    <div class="bg bg3"></div>
    <div class="loading-gif"  id="loadingDiv" style ="display:none"></div>
    <div class="nothing2do" id="nothing2do" style ="display:none"> There are no pending jobs.</div>
    <center>
        <div>
            Projects currently using v1.3.3 = <?php echo $mig_count."/".$count?>
            <br> Projects pending migration: <?php echo count($mig_these);?>
            <br> Project ID* = Module was, but is not currently enabled in project
        </div>
        <button id="mig_start">Migrate Pending Projects</button>
        <table class="pp">
        <thead>
        <tr>
            <th><?php echo implode('</th><th class="rotate">', $table_headers); ?></th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($data as $row): array_map('htmlentities', $row); ?>
            <tr>
                <td><a href="<?php print htmlspecialchars($URI)?>manager/project.php?pid=<?php print explode("*",explode("<td>",$row[1])[1])[0]?>">
                    <?php echo implode('</a></td>', $row); ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
        <tfoot>
        <tr>
            <th><?php echo implode('</th><th>', $table_headers); ?></th>
        </tr>
        </tfoot>
    </table>
    </center>
    <div class="footer">
        <p>* Projects using and earlier version of the Paper Trail module will conserve their settings in REDCap's backend;
        allowing them to remain functional if the Paper Trail's version is rolled back. </p>
    </div>
<?php endif;

$migs = json_encode($mig_these);
$data = array('secret' => 3232323, 'em_id' => $em_id, "migs" => $migs);
?>

<script type="text/javascript">
    $(document).ready(function() {
        $('#mig_start').click( function() {

            if( <?php echo $migs;?>!= null){
                console.log('array ready');
                $('#loadingDiv').toggle();
                $.ajax({type: "POST",
                    data: <?php echo json_encode($data)?>
                    ,
                    url:'/..<?php print htmlspecialchars($URI)?>?prefix=paper_trail&page=migration/mig_start_11',
                    success: function (result){
                        $('#loadingDiv').hide();
                        $('#successDiv').fadeIn('slow');
                        $('#successDiv').delay(250).fadeOut('slow');
                        location.reload();
                    }, error: function(XMLHttpRequest, textStatus, errorThrown) {
                        $('#loadingDiv').hide();
                        alert("some error \n"  +
                            "XMLHttpRequest: "  + XMLHttpRequest +
                            "\ntextStatus: " + textStatus +
                            "\nerrorThrown: " + errorThrown);
                    }
                });
                return false;
            } else {
                $('#nothing2do').toggle();
                $('#nothing2do').delay(600).fadeOut('slow');
            }
        } );
    } );
</script>
