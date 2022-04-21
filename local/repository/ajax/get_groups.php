<?php

require_once("../../../config.php");
if(!is_siteadmin()){
$datas = $DB->get_records('lcms_repository_groups', array('userid'=>$USER->id));
} else {
    $datas = $DB->get_records('lcms_repository_groups');
}
?>
<table class="generaltable">
    <thead><tr>
<?php
echo "<th style='width:55%'>".get_string('list:groupname','local_repository')."</th>"
        . "<th style='width:20%'>".get_string('list:contentscount','local_repository')."</th>"
        . "<th style='width:35%'>".' '."</th>";

echo "</tr></thead>";
echo "<tbody>";

foreach ($datas as $data) {
    
    $file_cnt = $DB->count_records('lcms_repository',array('groupid'=>$data->id));
    
    echo "<tr id='table_row" . $data->id . "'>";
    echo "<td style='text-align:left;' class='group_" . $data->id . "'>" . $data->name . "</td> ";
    echo "<td>" . $file_cnt . "</td> ";
    echo "<td>"
        . "<button id='modifybtn_".$data->id."' onclick='group_modify(" . $data->id . ")'>".get_string('namechange','local_repository')."</button>&nbsp;"
        . "<button onclick='group_delete(" . $data->id . ")'>".get_string('delete','local_repository')."</button>"
        . "</td> ";
    echo "</tr>";
}

if(!$datas){
    echo '<tr><td colspan="3">'.get_string('nogroups','local_repository').'</td></tr>';
}
echo "</tbody></table>";
