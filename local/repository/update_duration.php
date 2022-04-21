<?php

require_once("../../config.php");

$id = required_param('id', PARAM_INT); // repoid
$duration = required_param('duration', PARAM_INT); // repoid

$sql = "select "
        . "rep.id , "
        . "con.id as con_id, con.con_total_time, "
        . "cf.id as file_id, cf.duration "
        . "from {lcms_repository} rep "
        . "join {lcms_contents} con on con.id= rep.lcmsid "
        . "join {lcms_contents_file} cf on cf.con_seq = rep.lcmsid "
        . "where rep.id= :id";


$data = $DB->get_record_sql($sql, array('id' => $id));

if($data->con_total_time == 0){
    $lc = new stdClass();
    $lc->id = $data->con_id;
    $lc->con_total_time = $duration;
    $DB->update_record('lcms_contents',$lc);
}
if($data->duration){
    $lf = new stdClass();
    $lf->id = $data->file_id;
    $lf->duration = $duration;
    $DB->update_record('lcms_contents_file',$lf);
}
print_object($data);



