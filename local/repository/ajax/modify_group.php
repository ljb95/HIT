<?php

require_once("../../../config.php");

$id = optional_param('id', 0, PARAM_INT);
$gname = optional_param('gname', "", PARAM_CLEAN);
$gname =trim($gname);
if(!empty($gname)){
    $DB->update_record('lcms_repository_groups',array('id'=>$id,'name'=>$gname,'timemodified'=>time()));
}