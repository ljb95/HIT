<?php
require_once("../../../config.php");
require_once($CFG->libdir.'/authlib.php');


$auth_key = optional_param('auth_key', 0, PARAM_RAW);
$id_key = optional_param('id_key', 0, PARAM_RAW);
$state = optional_param('state', 0, PARAM_RAW);
$coursename = optional_param('coursename', 0, PARAM_RAW);
$filepath = optional_param('filepath', "", PARAM_RAW);
$filesize = optional_param('filesize', "", PARAM_RAW);
$filename = optional_param('filename', "", PARAM_RAW);
$duration = optional_param('duration', 0, PARAM_RAW);
$error_descriptrion = optional_param('error_descriptrion', "", PARAM_RAW);

sscanf($duration, "%d:%d:%d", $hours, $minutes, $seconds);

$time_seconds = isset($seconds) ? $hours * 3600 + $minutes * 60 + $seconds : $hours * 60 + $minutes;

$data = $DB->get_record_sql('select * from {lcms_contents} where auth_key = :auth_key', array('auth_key' => $auth_key));


header('Content-Type: application/json; charset=UTF-8');


if (!$data) {
    if ($error_descriptrion != "") {
        $error = new stdClass();
        $error->status = $state;
        $string = 'ERROR : ' . $state . 'filepath = ' . $filepath . 'filename = ' . $filepath . " filesize = " . $filesize . $_REQUEST['filepath'] . "///" . $_REQUEST['filename'] . "///" . $_REQUEST['filesize'] . "///" . time();
        $error->massage = $error_descriptrion . $string;
        error_log($string, 3, './error.log');
        echo json_encode($error);
        die();
    } else {
        $error = new stdClass();
        $error->status = 101;
        $string = 'ERROR : ' . $state . 'filepath = ' . $filepath . 'filename = ' . $filepath . " filesize = " . $filesize . $_REQUEST['filepath'] . "///" . $_REQUEST['filename'] . "///" . $_REQUEST['filesize'] . "///" . time();
        $error->massage = "유효 하지 않은 인증키 입니다." . $string;
        error_log($string, 3, './error.log');
        echo json_encode($error);
        die();
    }
} else {
    if (empty($filepath) || empty($filename) || empty($filesize)) {
        $error = new stdClass();
        $error->status = 102;
        $string = 'ERROR : ' . $state . 'filepath = ' . $filepath . 'filename = ' . $filepath . " filesize = " . $filesize . $_REQUEST['filepath'] . "///" . $_REQUEST['filename'] . "///" . $_REQUEST['filesize'] . "///" . time();
        $error->massage = "필수적인  파라미터가 존재하지 않습니다." . $string;
        error_log($string, 3, './error.log');
        echo json_encode($error);
        die();
    }
    $update_data = new stdClass();
    $update_data->id = $data->id;
    $update_data->data_dir = $filepath;
    $update_data->con_name = $coursename;
    $update_data->update_dt = time();
    $DB->update_record('lcms_contents', $update_data);

    $new_data_file = new stdClass();
    $new_data_file->con_seq = $data->id;
    $new_data_file->duration = $time_seconds;
    $new_data_file->user_no = $data->user_no;
    $new_data_file->filepath = $filepath;
    $new_data_file->filename = $filename;
    $new_data_file->fileoname = $filename;
    $new_data_file->filesize = $filesize;
    $new_data_file->con_type = 'video';
    $new_data_file->bitrate = ' '; 
    $DB->insert_record('lcms_contents_file', $new_data_file);
    
    $lcms_repository = new stdClass();
    $lcms_repository->lcmsid = $data->id;
    $lcms_repository->userid = $data->user_no;
    $lcms_repository->groupid = 0;
    $lcms_repository->referencecnt = 0;
    $DB->insert_record('lcms_repository', $lcms_repository);

    $status = new stdClass();
    $status->status = "ok";
    echo json_encode($status);
}
     
    