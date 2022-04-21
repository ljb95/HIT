<?php

require_once("../../../config.php");

$gname = optional_param('gname', "", PARAM_CLEAN);
$gname = trim($gname);
if (!empty($gname)) {
    $data = new stdClass();
    $data->name = $gname;
    $data->filecnt = 0;
    $data->userid = $USER->id;
    $data->timecreated = time();
    $data->timemodified = time();
    if ($newid = $DB->insert_record('lcms_repository_groups', $data)) {
        echo "<tr id='table_row" . $newid . "'>";
        echo "<td style='text-align:left;' class='group_" . $newid . "'>" . $gname . "</td>";
        echo "<td>0</td>";
        echo "<td>"
        . "<button id='modifybtn_".$newid."' onclick='group_modify(" . $newid . ")'>".get_string('namechange','local_repository')."</button>&nbsp;"
        . "<button onclick='group_delete(" . $newid . ")'>".get_string('delete','local_repository')."</button>"
        . "</td> ";
        echo "</tr>";
    }
}
