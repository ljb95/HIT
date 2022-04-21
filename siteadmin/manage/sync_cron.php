<?php

require(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once dirname(dirname(__FILE__)) . '/lib.php';
require_once $CFG->dirroot . '/local/haksa/lib.php';
require_once $CFG->dirroot . '/siteadmin/manage/synclib.php';

ini_set('display_errors', '1');
ini_set('memory_limit', '2048M');
ini_set('max_execution_time', 0);


if ($year == 0) {
    $year = get_config('moodle', 'haxa_year');
}
if ($term == 0) {
    $term = get_config('moodle', 'haxa_term');
}


/*

 * 
 * 
 * 
 * 
 * 유저 동기화
 * 
 * 
 * 
 * 
 * 
 *  */


$count = 0;
$timesync = time();

$existingusers = $DB->get_records_menu('user', array('deleted' => 0), '', 'username, id');

$count_created = 0;

$sql_st1 = "SELECT 
                USERNAME AS username, 
                BIRTH_DT AS password, 
                USERNAME_KOR AS user_nm,
                USERNAME_ENG AS eng_name,
                EMAIL AS email,
                TEL_NO AS phone1, 
                HP_NO AS phone2,
                USERNAME AS idnumber,
                USERGROUP_CD,
                USERGROUP,
                UNIV_CD AS univ_cd, 
                UNIV AS univ,
                MAJOR_CD AS major_cd, 
                DEPT_CD as dept_cd, 
                DEPT_NM as dept,
                DEPT_NM as department,
                USERGROUP as institution,
                MAJOR_NM as major,
                DAY_TM_CD,
                NATION_CD, 
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
                DEPT_NM AS psosok, 
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
                SEX AS sex,	
                HYEAR AS hyear, 
                EXAM_ID AS ex_num  
            FROM LMS_USER";

// sex 10 = 남자 20 = 여자
//  EXAN_NUMB   EMAIL 추가 되어야함 아직 없음  NATION_CD 관련 테이블도 없음

$conn = siteadmin_sync_db_connect();
$rs = odbc_exec($conn, $sql_st1);
// text가 잘려나오지 않도록 1 MB로 설정
odbc_longreadlen($rs, 1048576);
while ($row = odbc_fetch_array($rs)) {
    $usernew = (object) array_change_key_case($row, CASE_LOWER);     
                if(strtolower(trim($usernew->usergroup_cd)) == 'sl'){
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
                if(empty(trim($usernew->email))){
                    $usernew->email = $usernew->username.'@hit.ac.kr';
                }
                
                $usernew->phone1 = siteadmin_sync_validate_phonenumber($usernew->phone1);
                $usernew->phone2 = siteadmin_sync_validate_phonenumber($usernew->phone2);
                if(empty($usernew->eng_name)) {
                    $usernew->eng_name = $usernew->user_nm;
                }
                
                if(empty($usernew->hyear)) {
                    $usernew->hyear = 0;
                }
                if($usernew->nation_cd == 'KO'){
                    $usernew->nation_cd = '410';
                } else {
                    $usernew->nation_cd = '';
                }
                // nation_cd
                $usernew->lang = 'ko';
                
                if($usernew->username != $usernew->ex_num && $usernew->usergroup == 'rs'){
                    $ex_user = $DB->get_record('user',array('username'=>$usernew->ex_num,'email'=>$usernew->email,'firstname'=>$usernew->firstname ,'lastname'=>$usernew->lastname));
                        if($ex_user){
                                $DB->update_record('user',array('id'=>$ex_user->id,'username'=>$usernew->username));
                                $existingusers[$usernew->username] = $ex_user->id;
                            }
                }  
                    //수험번호
                    $userid = local_haksa_create_user($usernew, $existingusers);                
                    if ($userid > 0) {
                        $usernew->userid = $userid;
                        siteadmin_insert_or_update_lmsuserdata($usernew);
                        
                        $count_created += 1; 
                        
                        $DB->update_record('user',array('id'=>$userid,'idnumber'=>$usernew->idnumber
                                ,'firstname'=>$usernew->firstname,'lastname'=>$usernew->lastname,'email'=>$usernew->email,'department'=>$usernew->department
                                ,'institution'=>$usernew->institution));
                        
                       
                        $allow = array(11,12,13,16,19,'SL');
                            if(!in_array($usernew->usergroup_cd,$allow)){
                                
                               
                            $cate = array();
                            local_haksa_get_course_categories($cate);
                            $path = array();
                            $path[] = $DB->get_field_sql('SELECT name FROM {course_categories} WHERE idnumber = :idnumber', array('idnumber'=>'oklass_regular'));
                            $path[] = $DB->get_field_sql('SELECT name FROM {course_categories} WHERE idnumber = :idnumber', array('idnumber'=>'HIT'));
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

$haksa = $DB->get_record('haksa', array('year' => $year, 'term' => $term));
if ($haksa == false) {
    $haksa = new stdClass();
    $haksa->year = $year;
    $haksa->term = $term;
    $haksa->timesynccourse = 0;
    $haksa->timesyncparticipant = 0;
    $haksa->timecreatecourse = 0;
    $haksa->timeassignparticipant = 0;

    $haksa->id = $DB->insert_record('haksa', $haksa);
}

$sql_haksa = "SELECT 
                'HIT' as domain
                , so.YEAR AS year
                , so.TERM AS term
                , so.LEC_CD as hakno 
                , sodp.CLS_DIV AS BB
                , ' ' AS SBB 
                , so.KOR_LEC_NAME AS kor_lec_name
                , so.ENG_LEC_NAME AS eng_lec_name
                , ' ' AS summary
                , so.HAKJUM AS hakjum
                , 1 AS gubun  
                , deptnm.dept_nm AS cata2
                , so.LEC_CD AS lec_cd
                , so.LEC_CD AS ohakkwa 
                , 0 AS deleted
                , KOR_LEC_NAME AS shortname 
            FROM SMARTCAMPUS.dbo.LMS_SO so 
            JOIN SMARTCAMPUS.dbo.LMS_SO_DEPT sodp on sodp.LEC_CD = so.LEC_CD 
            JOIN DHDB.dbo.COT_DEPT deptnm  on deptnm.DEPT_CD = sodp.DEPT_CD 
            WHERE so.YEAR = $year and so.term = $term";
//$param = array('year'=>$year, 'term'=>$term);

$conn = siteadmin_sync_db_connect();
$rs = odbc_prepare($conn, $sql_haksa);
// article_text가 잘려나오지 않도록 1M로 설정
odbc_longreadlen($rs, 1048576);
$success = odbc_execute($rs);
if ($success) {
    $count = 0;
    while ($row = odbc_fetch_array($rs)) {
        $class = (object) array_change_key_case($row, CASE_LOWER);

        $class->haksa = $haksa->id;
//                        $class->univ = $class->univ;
//                        $class->major = $class->major;
        $class->kor_lec_name = $class->kor_lec_name;
        $class->eng_lec_name = $class->eng_lec_name;
        $class->cata1 = '대전보건대학교';
        $class->cata2 = $class->cata2;

        // 이미 가져왔으면 update, 없으면 insert
        $haksaclass = $DB->get_record('haksa_class', array(
            'year' => $class->year,
            'term' => $class->term,
            'lec_cd' => $class->lec_cd,
            'bb' => $class->bb));
        if (!empty($haksaclass)) {

            $haksaclass->kor_lec_name = $class->kor_lec_name;
            $haksaclass->eng_lec_name = $class->eng_lec_name;
            $haksaclass->gubun = $class->gubun;

            $DB->update_record('haksa_class', $haksaclass);
        } else {
            $DB->insert_record('haksa_class', $class);
            $count += 1;

        }
    }
    odbc_free_result($rs);
    siteadmin_sync_db_close($conn);

    $haksa->timesynccourse = time();
    $DB->update_record('haksa', $haksa);
} else {
    // echo 'Error: '.odbc_error($conn);
}

local_haksa_flushdata();

$strtimestart = time();
$strtimeend = time() + (60 * 60 * 24 * 31 * 4);
$strtimeregstart = time();
$strtimeregend = time() + (60 * 60 * 24 * 31 * 4);

$timestart = strtotime($strtimestart);
$timeregstart = strtotime($strtimeregstart);
$timeregend = strtotime($strtimeregend);
$timemodified = time();

$timecreatestart = time();
$count_created = 0;
$count_updated = 0;
$count_deleted = 0;

$userids = $DB->get_records_menu('user', array('deleted' => 0), '', 'username, id');

// 강의 업데이트 시작
// 강의 이름, 언어, 시작일 업데이트 한다.
$haksa_classes = $DB->get_records_sql("
SELECT cl.id,
       cl.kor_lec_name,
       cl.eng_lec_name,
       cl.shortname,
       cl.prof_cd,
       cl.gubun
FROM {haksa_class} cl
WHERE cl.shortname IS NOT NULL
  AND cl.YEAR = :year
  AND cl.TERM = :term
  AND cl.DELETED = :deleted", array('year' => $year, 'term' => $term, 'deleted' => 0));

foreach ($haksa_classes AS $haksa_class) {
    $mdl_class = $DB->get_record('course', array('shortname' => $haksa_class->shortname));
    if ($mdl_class !== false) {
        $mdl_class->fullname = $haksa_class->kor_lec_name;

        /*
         * 강의 시작/종료, 강의 등록 시작/종료는 업데이트 안하도록 변경
         * 2015. 9. 4
         */
        // 시작일
        //$mdl_class->startdate     = $timestart;

        $DB->update_record('course', $mdl_class);

        //// lmsdata_class 업데이트
        $lmsdata_class = $DB->get_record('lmsdata_class', array('course' => $mdl_class->id));
        if ($lmsdata_class) {
            $lmsdata_class->kor_lec_name = $haksa_class->kor_lec_name;
            $lmsdata_class->eng_lec_name = $haksa_class->eng_lec_name;
            $lmsdata_class->timemodified = $timemodified;
            // 2016. 3. 7. gubun 값이 이제 제대로 들어온다고 해서 업데이트 되도록 함.
            $lmsdata_class->gubun = $haksa_class->gubun;

            $prof_cd = clean_param($haksa_class->prof_cd, PARAM_USERNAME);
            if (isset($userids[$prof_cd])) {
                $lmsdata_class->prof_userid = $userids[$prof_cd];
            }

            $DB->update_record('lmsdata_class', $lmsdata_class);
        }

        $count_updated++;
    } else {
        // 2015. 9. 7. 교수가 강의를 삭제한 경우 다시 생성되지 않음. UIC1808-01, UIC1804-01, UIC1804-02
        // 다시 생성되도록 shortname를 null로 설정
        $DB->set_field('haksa_class', 'shortname', null, array('id' => $haksa_class->id));
    }
}
// 강의 업데이트 끝
// 새로운 강의 생성 시작
$categories = array();
local_haksa_get_course_categories($categories);

// 생성할 강의 가져오기
$haksa_classes_new = $DB->get_records_sql("
SELECT cl.id AS class_id,
       cl.KOR_LEC_NAME AS fullname,
       cl.SUMMARY,
       1 AS summaryformat,
       1 AS visible,
       'topics' AS FORMAT,
       10 AS numsections,
       0 AS hiddensections,
       0 AS coursedisplay,
       'creativeband' AS theme,
       ' ' AS lang,
       ' ' AS calendartype,
       5 AS newsitems,
       1 AS showgrades,
       0 AS showreports,
       0 AS maxbytes,
       0 AS enablecompletion,
       1 AS enrol_guest_status_0,
       0 AS groupmode,
       0 AS groupmodeforce,
       0 AS defaultgroupingid,
       ' ' AS role_1,
       ' ' AS role_2,
       ' ' AS role_3,
       ' ' AS role_4,
       ' ' AS role_5,
       ' ' AS role_6,
       ' ' AS role_7,
       ' ' AS role_8,
       0 AS id,
       cl.YEAR,
       cl.TERM,
       cl.HAKNO AS SUBJECT_ID,
       cl.PROF_CD,
       cl.KOR_LEC_NAME,
       cl.ENG_LEC_NAME,
       cl.OHAKKWA,
       cl.DOMAIN,
       cl.HAKNO,
       cl.BB,
       cl.SBB,
       cl.HAKJUM,
       cl.GUBUN,
       0 AS timeend,
       0 AS timeregstart,
       0 AS timeregend,
       '0' AS isnonformal,
       0 AS timemodified,
       cl.CATA1,
       cl.CATA2,
       cl.CATA3
FROM {haksa_class} cl
WHERE (cl.shortname IS NULL OR cl.shortname = '')
  AND cl.YEAR = :year
  AND cl.TERM = :term
  AND cl.DELETED = :deleted
ORDER BY cl.CATA3, cl.KOR_LEC_NAME", array('year' => $year, 'term' => $term, 'deleted' => 0));

foreach ($haksa_classes_new AS $haksa_class) {
    $haksa_class->shortname = $leccdstart++;
    $haksa_class->startdate = $timestart;
    $haksa_class->timemodified = $timemodified;

    if (empty($haksa_class->summary)) {
        $haksa_class->summary = '';
    }

    $path = local_haksa_get_category_path($haksa_class);
    $haksa_class->category = local_haksa_find_or_create_category($path, $categories);

    // Create Course
    $course = local_haksa_create_course($haksa_class);

    // Update haksa_class->shortname
    $DB->set_field('haksa_class', 'shortname', $haksa_class->shortname, array('id' => $haksa_class->class_id));

    // Insert lmsdata_class table
    $lmsdata_class = new stdClass();
    $lmsdata_class->course = $course->id;
    $lmsdata_class->subject_id = $haksa_class->subject_id;
    $lmsdata_class->category = $haksa_class->category;
    $lmsdata_class->kor_lec_name = $haksa_class->kor_lec_name;
    $lmsdata_class->eng_lec_name = $haksa_class->eng_lec_name;
    $lmsdata_class->prof_userid = 0;
    $lmsdata_class->year = $haksa_class->year;
    $lmsdata_class->term = $haksa_class->term;
    $lmsdata_class->timestart = $timestart;
    $lmsdata_class->timeend = $timeend;
    $lmsdata_class->timeregstart = $timeregstart;
    $lmsdata_class->timeregend = $timeregend;
    $lmsdata_class->isnonformal = $haksa_class->isnonformal;
    $lmsdata_class->gubun = $haksa_class->gubun;
    $lmsdata_class->timemodified = $haksa_class->timemodified;
    $lmsdata_class->ohakkwa = $haksa_class->ohakkwa;
    $lmsdata_class->domain = $haksa_class->domain;

    $prof_cd = clean_param($haksa_class->prof_cd, PARAM_USERNAME);
    if (isset($userids[$prof_cd])) {
        $lmsdata_class->prof_userid = $userids[$prof_cd];
    } 

    $DB->insert_record('lmsdata_class', $lmsdata_class);

    // local/courselis/classes/observer.php의 course_created 함수에서 lmsdata_class 테이블에 넣는 것을 막기위해
    // lmsdata_class 에 넣은 후 이벤트를 발생시킨다.
    // Trigger a course created event.
    $event = \core\event\course_created::create(array(
                'objectid' => $course->id,
                'context' => context_course::instance($course->id),
                'other' => array('shortname' => $course->shortname,
                    'fullname' => $course->fullname)
    ));
    $event->trigger();

    $count_created++;
}
// 새로운 강의 생성 끝
// 지워진 강의 삭제 시작
$deleted_classes = $DB->get_records_sql("SELECT id, shortname
                FROM {haksa_class} cl
WHERE cl.HAKNO IS NOT NULL
  AND cl.YEAR = :year
  AND cl.TERM = :term
  AND cl.DELETED = :deleted", array('year' => $year, 'term' => $term, 'deleted' => 1));

foreach ($deleted_classes as $deleted_class) {
    $course = $DB->get_record('course', array('shortname' => $deleted_class->shortname));
    if ($course !== false) {
        if (delete_course($course->id, false)) {
            $DB->set_field('haksa_class', 'shortname', NULL, array('id' => $deleted_class->id));
            $count_deleted++;
        }
    }
}
// 지워진 강의 삭제 끝


fix_course_sortorder();
cache_helper::purge_by_event('changesincourse');

$timecreateend = time();

$haksa->timecreatecourse = $timecreatestart;
$DB->update_record('haksa', $haksa);

local_haksa_flushdata();

$param = array('year' => $year, 'term' => $term);

$sql_existingprofs = "SELECT p.id,
                    CONCAT(CONCAT(p.LEC_CD, '-'), p.prof_cd) AS prof_key
            FROM {haksa_class_professor} p
            WHERE p.year = :year
              AND p.term = :term";

// 이전에 가져온 교수 데이터
$profs = $DB->get_records_sql_menu($sql_existingprofs, array('year' => $year, 'term' => $term));

$sql_prof = "SELECT 
                                YEAR,
                                TERM,
                                LEC_CD AS LEC_CD,
                                PROF_CD,
                                PROF_NM,
                                LEC_CD AS HAKNO 
                            FROM LMS_SO_PROF 
                            WHERE YEAR = $year 
                              AND TERM = $term";


// 교수
$conn = siteadmin_sync_db_connect();
$rs = odbc_prepare($conn, $sql_prof);
// article_text가 잘려나오지 않도록 1M로 설정
odbc_longreadlen($rs, 1048576);
$success = odbc_execute($rs, $param);
if ($success) {
    $count_prof = 0;
    $count_prof_deleted = 0;
    while ($row = odbc_fetch_array($rs)) {
        $prof = (object) array_change_key_case($row, CASE_LOWER);
        // 비어있는 것들은 무시
        if (empty($prof->lec_cd)) {
            continue;
        }

        $prof->prof_name = $prof->prof_nm;
        $prof->univ = ' ';
        $prof->major = ' ';

        // 없는 경우에만 넣어야 함.
        $prof_key = $prof->lec_cd . '-' . $prof->prof_cd;
        if (in_array($prof_key, $profs)) {
            $prof_id = array_search($prof_key, $profs);
            if ($record = $DB->get_record('haksa_class_professor', array('id' => $prof_id))) {
                $prof->id = $record->id;
                $prof->haksa = $haksa->id;
                $DB->update_record('haksa_class_professor', $prof);
            }
            unset($profs[$prof_id]);
        } else {
            $prof->haksa = $haksa->id;
            $DB->insert_record('haksa_class_professor', $prof);

            $count_prof += 1;

        }
    }
    odbc_free_result($rs);
    siteadmin_sync_db_close($conn);
    // 삭제된 교수 처리
    foreach ($profs as $deleted_prof_id => $prof_key) {
        if ($deleted_prof = $DB->get_record('haksa_class_professor', array('id' => $deleted_prof_id))) {
            // deleted = 1 로 만듬.
            if ($deleted_prof->deleted == 0) {
                $deleted_prof->deleted = 1;
                $DB->update_record('haksa_class_professor', $deleted_prof);

                $count_prof_deleted += 1;
            }
        }
    }
}

$sql_existingstuds = "SELECT s.id, 
                                 CONCAT(CONCAT(s.LEC_CD, '-'), s.hakbun) AS stud_key
                                        FROM {haksa_class_student} s
                                        WHERE s.year = :year
                                          AND s.term = :term ";

// 이전에 가져온 학생 데이터
$studs = $DB->get_records_sql_menu($sql_existingstuds, array('year' => $year, 'term' => $term));

$sql_stud = "SELECT 
                                YEAR,
                                TERM,
                                LEC_CD,
                                HAKBUN,
                                USER_NM AS NAME,
                                LEC_CD AS HAKNO, 
                                DELETED
                            FROM LMS_SU 
                            WHERE YEAR = $year  
                              AND TERM = $term";
// 학생 
$conn = siteadmin_sync_db_connect();
$rs = odbc_prepare($conn, $sql_stud);
// article_text가 잘려나오지 않도록 1M로 설정
odbc_longreadlen($rs, 1048576);
$success = odbc_execute($rs, $param);
if ($success) {
    $count_stud = 0;
    $count_stud_deleted = 0;
    while ($row = odbc_fetch_array($rs)) {
        $stud = (object) array_change_key_case($row, CASE_LOWER);
        // 비어있는 것들은 무시
        if (empty($stud->lec_cd)) {
            continue;
        }
        if ($stud->deleted == 'Y') {
            $stud->deleted = 0;
        }


        // 없는 경우에만 넣어야 함.
        $stud_key = $stud->lec_cd . '-' . $stud->hakbun;
        if (in_array($stud_key, $studs)) {
            $stud_id = array_search($stud_key, $studs);
            if ($record = $DB->get_record('haksa_class_student', array('id' => $stud_id))) {
                $stud->id = $record->id;
                $stud->haksa = $haksa->id;
                $DB->update_record('haksa_class_student', $stud);
            }
            if ($stud->deleted == 0) {
                unset($studs[$stud_id]);
            }
        } else {
            $stud->haksa = $haksa->id;
            $DB->insert_record('haksa_class_student', $stud);

            $count_stud += 1;
        }
    }
    odbc_free_result($rs);
    siteadmin_sync_db_close($conn);
    foreach ($studs as $deleted_stud_id => $stud_key) {
        if ($deleted_stud = $DB->get_record('haksa_class_student', array('id' => $deleted_stud_id))) {
            // deleted = 1 로 만듬.
            if ($deleted_stud->deleted == 0) {
                $deleted_stud->deleted = 1;
                $DB->update_record('haksa_class_student', $deleted_stud);

                $count_stud_deleted += 1;

            }
        }
    }
}

$timeassignstart = time();

                $count_participant = 0;
                $count_participant_deleted = 0; 

                $roles = array();
                // Professor, 교수, pr
                $roles['pr'] = $DB->get_record('role', array('shortname' => 'editingteacher'));
                // Teaching assistant, 조교, as
                //$roles['as'] = $DB->get_record('role', array('shortname'=>'teacher'));
                // Student, 수강생, rs
                $roles['rs'] = $DB->get_record('role', array('shortname' => 'student'));
                // Auditor, 청강생, au
                //$roles['au'] = $DB->get_record('role', array('shortname'=>'auditor'));
                $existingusers = $DB->get_records_menu('user', array('deleted' => 0), '', 'username, id');

                $courses = $DB->get_records_sql("SELECT co.id,
       co.shortname,
       co.fullname AS coursename,
       co.startdate,
       ctx.id AS contextid
FROM {haksa_class} hc
JOIN {course} co ON co.shortname = hc.shortname
                AND hc.deleted = 0
JOIN {context} ctx ON ctx.instanceid = co.id
                  AND ctx.contextlevel = :contextlevel
WHERE hc.year = :year
  AND hc.term = :term
ORDER BY co.sortorder", array('year' => $year, 'term' => $term, 'contextlevel' => CONTEXT_COURSE));

                foreach ($courses as $course) {
                    $courseid = $course->id;
                    $contextid = $course->contextid;

                    // 학생 등록
                    $students = $DB->get_records_sql("SELECT hs.hakbun,
                                                             hs.deleted
                                                    FROM {haksa_class_student} hs
                                                    JOIN {haksa_class} hc ON hc.year  = hs.year
                                                                         AND hc.term  = hs.term
                                                                         AND hc.hakno = hs.hakno
                                                    WHERE hs.year = :year
                                                      AND hs.term = :term 
                                                      AND hc.shortname = :shortname", array('year' => $year, 'term' => $term, 'shortname' => $course->shortname));

                    $assigned_students = $DB->get_records_menu('role_assignments', array('roleid' => $roles['rs']->id, 'contextid' => $contextid), '', 'userid, id');

                    $count_student = 0;
                    $count_student_deleted = 0;
                    foreach ($students AS $student) {
                        $username = clean_param($student->hakbun, PARAM_USERNAME);
                        if (isset($existingusers[$username])) {
                            // 학생은 강의시작일 부터 접근할 수 있도록 한다.
                            if ($student->deleted) { // 삭제된 학생인 경우
                                // 등록된 목록에 있는 경우 등록 해제
                                if (isset($assigned_students[$existingusers[$username]])) {
                                    // 수강취소한 학생이 같은 강의에 청강 등록한 경우는 삭제하지 않는다.
                                    $enrol = $DB->get_field_sql("SELECT en.enrol FROM {user_enrolments} ue JOIN {enrol} en ON ue.enrolid = en.id WHERE en.courseid = :courseid AND ue.userid = :userid", array('courseid' => $course->id, 'userid' => $existingusers[$username]));
                                    if ($enrol == 'manual') {
                                        local_haksa_unassign_user($course, $existingusers[$username], $roles['rs']->id);

                                        $count_participant_deleted++;
                                        $count_student_deleted++;
                                    }
                                }
                            } else { // 추가된 학생의 경우
                                // 등록된 목록에 없으면 등록
                                if (!isset($assigned_students[$existingusers[$username]])) {
                                    // 2015. 8. 17. "강좌 관리 > 참여자 > 등록된 사용자" 페이지에서 등록방법의 톱니바퀴 아이콘 선택,
                                    // "등록 편집" 페이지의 "재적 개시 "관리모드 활성"을 해제 요청에 의해 수정
                                    //local_haksa_assign_user($courseid, $existingusers[$username], $roles['rs'], $course->startdate, 0, $timeassignstart);
                                    local_haksa_assign_user($courseid, $existingusers[$username], $roles['rs'], 0, 0, $timeassignstart);

                                    $count_participant++;
                                    $count_student++;
                                }
                            }
                        }
                    }

                    // 교수 등록
                    $professors = $DB->get_records_sql("SELECT hp.id,
                                                           hp.prof_cd 
                                                    FROM {haksa_class_professor} hp
                                                    JOIN {haksa_class} hc ON hc.year  = hp.year
                                                                         AND hc.term  = hp.term
                                                                         AND hc.hakno = hp.hakno
                                                    WHERE hp.year = :year
                                                      AND hp.term = :term
                                                      AND hc.shortname = :shortname", array('year' => $year, 'term' => $term, 'shortname' => $course->shortname));

                    $assigned_professors = $DB->get_records_menu('role_assignments', array('roleid' => $roles['pr']->id, 'contextid' => $contextid), '', 'userid, id');

                    $count_professor = 0;
                    $count_professor_deleted = 0;

                    foreach ($professors AS $professor) {
                        $username = clean_param($professor->prof_cd, PARAM_USERNAME);
                        if (isset($existingusers[$username])) {

                                if (!isset($assigned_professors[$existingusers[$username]])) {
                                    local_haksa_assign_user($courseid, $existingusers[$username], $roles['pr'], 0, 0, $timeassignstart);
                                    $count_participant++;
                                    $count_professor++;
                                }

                        }
                    }

                }

                $timeassignend = time();

                $haksa->timeassignparticipant = $timeassignstart;
                $DB->update_record('haksa', $haksa);
