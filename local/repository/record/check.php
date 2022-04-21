<?php
require_once("../../../config.php");
require_once($CFG->libdir.'/authlib.php');

echo 1;

$auth_key = optional_param('auth_key', "", PARAM_RAW);
$id_key = optional_param('id_key', 0, PARAM_RAW);
$state = optional_param('state', 0, PARAM_RAW);
$coursename = optional_param('coursename', 0, PARAM_RAW);
$filepath = optional_param('filepath', 0, PARAM_RAW);
$filesize = optional_param('filesize', 0, PARAM_RAW);
$filename = optional_param('filename', 0, PARAM_RAW);
$duration = optional_param('duration', 0, PARAM_RAW); 
$error_descriptrion = optional_param('error_descriptrion', "", PARAM_RAW);
echo "!"; 
$data = $DB->get_record_sql('select * from {lcms_contents} where auth_key = :auth_key', array('auth_key' => $auth_key));

echo 2;

header('Content-Type: application/json; charset=UTF-8');

echo 3;