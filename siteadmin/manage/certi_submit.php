<?php

require(dirname(dirname(dirname(__FILE__))) . '/config.php');

$background = $_FILES['background_img'];
$dojang = $_FILES['dojang'];

$mode = optional_param('mode','',PARAM_RAW);
if($mode == 'edit'){
    $id = required_param('id', PARAM_INT);
    $data = $DB->get_record('lmsdata_certificate',array('id'=>$id));
    $data->timemodified = time();
}else{
    $data = new stdClass();
    $data->timecreated = time();
}

$data->userid = optional_param('userid', 0, PARAM_INT);
$data->name =  optional_param('name', '', PARAM_RAW);
if($background['name']) $data->background = $background['name'];
//if($dojang['name']) $data->dojang = 'd'.$dojang['name'];
$data->author = optional_param('author', '', PARAM_RAW);
$data->prefix = optional_param('prefix', '', PARAM_RAW);
$data->description = optional_param('description', '', PARAM_RAW);
$data->codeid = optional_param('codeid', 0, PARAM_INT);
$data->periodstart = strtotime(optional_param('starttime', '', PARAM_RAW));
$data->periodend = strtotime(optional_param('endtime', '', PARAM_RAW));
$data->lang = optional_param('lang', 'ko', PARAM_RAW);

if($mode == 'edit'){
    $DB->update_record('lmsdata_certificate',$data);
}else{
    $id = $DB->insert_record('lmsdata_certificate',$data);
}

if($data->background || $data->dojang){
    mkdir($CFG->dirroot.'/siteadmin/manage/certi_imgs/'.$id);
}
if($data->background){
    move_uploaded_file($background['tmp_name'],$CFG->dirroot.'/siteadmin/manage/certi_imgs/'.$id.'/'.$data->background);
}
//if($data->dojang){
//    move_uploaded_file($dojang['tmp_name'],$CFG->dirroot.'/siteadmin/manage/certi_imgs/'.$id.'/'.$data->dojang);
//}

redirect('certi.php');