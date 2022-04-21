<?php

require_once("../../../config.php");
require_once($CFG->libdir . '/authlib.php');

$uid = optional_param('uid', 0, PARAM_RAW);
$uname = optional_param('uname', 0, PARAM_RAW);

header('Content-Type: application/json; charset=UTF-8');

$data = new stdClass();

if (!$uid || !$uname) {
    $data->status = "101";
    $string = 'ERROR : ' . $error->status . 'uid = ' . $uid . 'uname = ' . $uname . "REQUEST => " . $_REQUEST['uid'] . "///" . $_REQUEST['uname'] . "///" . time();
    $data->massage = "아이디 혹은 이메일이 입력되지 않았습니다." . $string;
    error_log($string, 3, './error.log');
    echo json_encode($data);
    die();
}
$ORG_USER = $USER;
$unlogin_user = $DB->get_record('user', array('username' => $uid));

// $upw = crypt($upw, $unlogin_user->password);

$sitesalt = isset($CFG->passwordsaltmain) ? $CFG->passwordsaltmain : '';

if (strtolower($unlogin_user->email) === strtolower($uname)) {
    $user = $unlogin_user;
} else {
    $data->status = "102";
    $string = 'ERROR : ' . $error->status . 'uid = ' . $uid . 'uname = ' . $uname . "REQUEST => " . $_REQUEST['uid'] . "///" . $_REQUEST['uname'] . "///" . time();
    $data->massage = "아이디와 이메일이 유효하지 않습니다." . $string;
    error_log($string, 3, './error.txt');
    echo json_encode($data);
    die();
}

$data->id_key = $user->id;
$data->auth_key = md5(time() . "YS" . mt_rand(0, 999) . $user->id);
$data->username = $user->username;
$data->name = (!empty($user->lastname)) ? $user->firstname . "(" . $user->lastname . ")" : $user->firstname;
$data->course_list = new stdClass();
if (!empty($CFG->navsortmycoursessort)) {
    // sort courses the same as in navigation menu
    $sortorder = 'visible DESC,' . $CFG->navsortmycoursessort . ' ASC';
} else {
    $sortorder = 'visible DESC,sortorder ASC';
}
$USER = $user;
$courses = enrol_get_my_courses('summary, summaryformat', $sortorder);
$i = 1;
$data->course_list = (array)$data->course_list;
foreach ($courses as $course) {
    $data->course_list['course'.$i] = $course->fullname;
    $i++;
}
$data->course_list = (object)$data->course_list;
if(empty($courses)){
    $data->course_list->course1 = '빈 강의명';
}
$data->status = "ok";

$lcms_data = new stdClass();
$lcms_data->area_cd = 1;
$lcms_data->major_cd = 1;
$lcms_data->course_cd = $user->id;
$lcms_data->teacher = (!empty($user->firstname)) ? $user->firstname : $user->lastname;
$lcms_data->share_yn = "N";
$lcms_data->con_name = " ";
$lcms_data->con_type = "video";
$lcms_data->con_total_time = 0;
$lcms_data->auth_key = $data->auth_key;
$lcms_data->author = " ";
$lcms_data->cc_type = 1;
$lcms_data->user_no = $user->id;
$lcms_data->con_hit = 0;
$lcms_data->reg_dt = time();
$lcms_data->update_dt = time();
$lcmsid = $DB->insert_record('lcms_contents', $lcms_data);

$USER = $ORG_USER;

echo json_encode($data);

