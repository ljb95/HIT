<?php

ini_set('display_errors', '1');
ini_set('memory_limit', '2048M');
ini_set('max_execution_time', 0);

require(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once dirname(dirname(__FILE__)) . '/lib.php';
require_once $CFG->dirroot . '/local/haksa/lib.php';
require_once $CFG->dirroot . '/siteadmin/manage/synclib.php';

$year = get_config('moodle', 'haxa_year');
$term = get_config('moodle', 'haxa_term');

$i = date('G',time());

$hour_date = $DB->get_record('haksa_auto_sync',array('year'=>$year,'term'=>$term,'hour'=>$i));
if(!$hour_date){
    // 설정 동기화 시간에만 돌아야함
    die();
}

$count = 0;
$timesync = time();

$existingusers = $DB->get_records_menu('user', array('deleted' => 0), '', 'username, id');

$count_created = 0;

$sql_st1 = "SELECT 
                lu.USERNAME AS username, 
                lu.BIRTH_DT AS password, 
                lu.USERNAME_KOR AS user_nm,
                lu.USERNAME_ENG AS eng_name,
                lu.EMAIL AS email,
                lu.TEL_NO AS phone1, 
                lu.HP_NO AS phone2,
                lu.USERNAME AS idnumber,
                lu.USERGROUP_CD,
                lu.USERGROUP,
                lu.UNIV_CD AS univ_cd, 
                lu.UNIV AS univ,
                lu.MAJOR_CD AS major_cd, 
                lu.DEPT_CD as dept_cd, 
                lu.DEPT_NM as dept,
                lu.DEPT_NM as department,
                lu.USERGROUP as institution,
                lu.MAJOR_NM as major,
                lu.DAY_TM_CD,
                lu.NATION_CD, 
                0 AS b_temp, 
                0 AS b_mobile, 
                0 AS b_email,  
                0 AS b_tel, 
                0 AS b_univ, 
                0 AS b_major, 
                ' ' AS ehks, 
                ' ' AS edhs, 
                ' ' AS domain, 
                ' ' AS hyhg, 
                ' ' AS persg, 
                lu.DEPT_NM AS psosok, 
                'manual' AS auth,
                0 AS suspended,
                0 AS maildisplay,
                1 AS mailformat,
                0 AS maildigest,
                1 AS autosubscribe,
                1 AS trackforums,
                99 AS timezone,
                ' ' AS calendartype,
                0 AS descriptiontrust,
                ' ' AS description,
                1 AS descriptionformat,
                1 AS mnethostid,
                1 AS confirmed,
                0 AS timemodified,
                ' ' AS address,
                lu.SEX AS sex,	
                lu.HYEAR AS hyear, 
                lu.EXAM_ID AS ex_num,
                vh.STAUTS AS status
            FROM LMS_USER lu
            LEFT JOIN VHSC_HAKSA vh ON vh.USER_ID = lu.USERNAME";

// sex 10 = 남자 20 = 여자
//  EXAN_NUMB   EMAIL 추가 되어야함 아직 없음  NATION_CD 관련 테이블도 없음

$conn = siteadmin_sync_db_connect();
$rs = odbc_exec($conn, $sql_st1);
// text가 잘려나오지 않도록 1 MB로 설정
odbc_longreadlen($rs, 1048576);
while ($row = odbc_fetch_array($rs)) {
    $usernew = (object) array_change_key_case($row, CASE_LOWER);


    if (strtolower(trim($usernew->usergroup_cd)) == 'sl') {
        $usernew->usergroup = 'rs';
    } else {
        $usernew->usergroup = 'pr';
    }
    $usernew->univ = siteadmin_sync_encode($usernew->univ);

    $usernew->firstname = $usernew->user_nm;
    $usernew->lastname = '　';
    $usernew->timemodified = $timesync;
    $usernew->timecreated = $usernew->timemodified;

    $usernew->email = siteadmin_sync_validate_email($usernew->email);

    if (empty(trim($usernew->email))) {
        $usernew->email = $usernew->username . '@hit.ac.kr';
    }

    $usernew->phone1 = siteadmin_sync_validate_phonenumber($usernew->phone1);
    $usernew->phone2 = siteadmin_sync_validate_phonenumber($usernew->phone2);
    if (empty($usernew->eng_name)) {
        $usernew->eng_name = $usernew->user_nm;
    }

    if (empty($usernew->hyear)) {
        $usernew->hyear = 0;
    }

    if ($usernew->nation_cd == 'KO') {
        $usernew->nation_cd = '410';
    } else {
        $usernew->nation_cd = '';
    }
    // nation_cd
    $usernew->lang = 'ko';

    if ($usernew->username != $usernew->ex_num && $usernew->usergroup == 'rs') {
        $ex_user = $DB->get_record('user', array('username' => $usernew->ex_num, 'firstname' => $usernew->firstname, 'lastname' => $usernew->lastname));
        if ($ex_user) {
            $DB->update_record('user', array('id' => $ex_user->id, 'username' => $usernew->username));
            $existingusers[$usernew->username] = $ex_user->id;
        }
    }
    //수험번호
    $userid = local_haksa_create_user($usernew, $existingusers);
    if ($userid > 0) {
        $usernew->userid = $userid;
        siteadmin_insert_or_update_lmsuserdata($usernew);

        $count_created += 1;

        $DB->update_record('user', array('id' => $userid, 'idnumber' => $usernew->idnumber
            , 'firstname' => $usernew->firstname, 'lastname' => $usernew->lastname, 'email' => $usernew->email, 'department' => $usernew->department
            , 'institution' => $usernew->institution));

        $allow = array(11, 12, 13, 16, 19, 'SL');
        if (!in_array($usernew->usergroup_cd, $allow)) {

            $cate = array();
            local_haksa_get_course_categories($cate);
            $path = array();
            $path[] = $DB->get_field_sql('SELECT name FROM {course_categories} WHERE idnumber = :idnumber', array('idnumber' => 'oklass_regular'));
            $path[] = $DB->get_field_sql('SELECT name FROM {course_categories} WHERE idnumber = :idnumber', array('idnumber' => 'HIT'));
            $path[] = $usernew->dept;
            $haksa_class->category = local_haksa_find_or_create_category($path, $cate);
        }
    }
}
odbc_free_result($rs);
siteadmin_sync_db_close($conn);

$history = new stdClass();
$history->timestart = $timesync;
$history->timeend = time();
$history->usercount = $count_created;
$DB->insert_record('haksa_user_history', $history);


