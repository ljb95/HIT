<?php

require(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once 'config.php';
require_once 'lib.php';

$con_name = required_param('con_name', PARAM_RAW);

$context = context_system::instance();
require_login();
$PAGE->set_context($context);

$contents = new stdClass();
$contents->data_dir = 'lms/files/' . $USER->id . '/' . date('YmdHis');
$extarr = $LCFG->allowextword;
$n = 0;

    $con_db = new stdClass();
    $con_db->area_cd = 1;
    $con_db->major_cd = 1;
    $con_db->course_cd = $USER->id;
    $con_db->teacher = fullname($USER);
    $con_db->share_yn = 'N';
    $con_db->con_name = htmlspecialchars($con_name, ENT_QUOTES);
    $con_db->con_type = 'word';

    $con_db->con_des = '';

    $con_db->con_tag = '';
    $con_db->con_total_time = 0;

    $con_db->author = "";
    $con_db->cc_type = 1;
    $con_db->cc_mark = "";
    $con_db->embed_type = "";
    $con_db->embed_code = "";

    $con_db->data_dir = $contents->data_dir;
    $con_db->user_no = $USER->id;
    $con_db->con_hit = 0;
    $con_db->reg_dt = time();
    $con_db->update_dt = time();

    $new_conid = $DB->insert_record('lcms_contents', $con_db);

    $rep_con_db = new stdClass();
    $rep_con_db->lcmsid = $new_conid;
    $rep_con_db->userid = $USER->id;
    $rep_con_db->iscdms = 1;
    $rep_con_db->status = 1;
    $rep_con_db->groupid = 0;
    $rep_con_db->referencecnt = 0;

    $new_rep_conid = $DB->insert_record('lcms_repository', $rep_con_db);
