<?php
ini_set('memory_limit', '1024M');
ini_set('max_execution_time', 0);

require(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once dirname(dirname(__FILE__)) . '/lib.php';
require_once $CFG->dirroot . '/local/haksa/config.php';
require_once $CFG->dirroot . '/local/haksa/lib.php'; 
require_once($CFG->dirroot . '/course/lib.php');
require_once($CFG->dirroot . '/lib/accesslib.php');
require_once($CFG->dirroot . '/lib/sessionlib.php');
require_once($CFG->dirroot . '/enrol/locallib.php');
require_once $CFG->dirroot . '/siteadmin/manage/synclib.php';

$i = date('G', time()); 

$year = get_config('moodle', 'haxa_year');
$term = get_config('moodle', 'haxa_term');

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

$hour_date = $DB->get_record('haksa_auto_sync', array('year' => $year, 'term' => $term, 'hour' => $i));
if (!$hour_date) {
    // 설정 동기화 시간에만 돌아야함
    die();
}

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



$conn = siteadmin_sync_db_connect();


$param = array('year' => $year, 'term' => $term);

$sql_existingprofs = "SELECT p.id,
                    CONCAT(CONCAT(p.LEC_CD, '-'), p.prof_cd) AS prof_key
            FROM {haksa_class_professor} p
            WHERE p.year = :year
              AND p.term = :term";

// 이전에 가져온 교수 데이터
$profs = $DB->get_records_sql_menu($sql_existingprofs, $param);

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

    // 삭제된 학생 처리
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
$haksa->timesyncparticipant = time();
$timeassignstart = time();

$count_participant = 0;
$count_participant_deleted = 0;

$roles = array();
// Professor, 교수, pr
$roles['pr'] = $DB->get_record('role', array('shortname' => 'editingteacher'));
// Student, 수강생, rs
$roles['rs'] = $DB->get_record('role', array('shortname' => 'student'));
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
    $mcourse = $DB->get_record('course',array('id'=>$courseid));

    // 학생 등록
    $students = $DB->get_records_sql("SELECT hs.hakbun,hs.deleted
                                FROM {haksa_class_student} hs
                                JOIN {haksa_class} hc ON hc.year  = hs.year AND hc.term  = hs.term AND hc.hakno = hs.hakno
                                WHERE hs.year = :year AND hs.term = :term AND hc.shortname = :shortname"
            , array('year' => $year, 'term' => $term, 'shortname' => $course->shortname));

    $assigned_students = $DB->get_records_menu('role_assignments', array('roleid' => $roles['rs']->id, 'contextid' => $contextid), '', 'userid, id');

    $count_student = 0;
    $count_student_deleted = 0;
    foreach ($students AS $student) {
        $username = clean_param($student->hakbun, PARAM_USERNAME);
        if (isset($existingusers[$username])) {
            // 학생은 강의시작일 부터 접근할 수 있도록 한다.
            if ($student->deleted) { // 삭제된 학생인 경우
                // 등록된 목록에 있는 경우 등록 해제
                    $enrol = $DB->get_field_sql("SELECT en.enrol FROM {user_enrolments} ue JOIN {enrol} en ON ue.enrolid = en.id WHERE en.courseid = :courseid AND ue.userid = :userid", array('courseid' => $course->id, 'userid' => $existingusers[$username]));
                    if ($enrol == 'manual') {
                        local_haksa_unassign_user($mcourse, $existingusers[$username], $roles['rs']->id);

                        $count_participant_deleted++;
                        $count_student_deleted++;
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
                                                           hp.prof_cd,
                                                           hp.deleted
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
            if ($professor->deleted) {
                if (isset($assigned_professors[$existingusers[$username]])) {
                    local_haksa_unassign_user($course, $existingusers[$username], $roles['pr']->id);

                    $count_participant_deleted++;
                    $count_professor_deleted++;
                }
            } else {
                if (!isset($assigned_professors[$existingusers[$username]])) {
                    local_haksa_assign_user($courseid, $existingusers[$username], $roles['pr'], 0, 0, $timeassignstart);
                    $count_participant++;
                    $count_professor++;
                }
            }    
        }
    }
}

$timeassignend = time();

$haksa->timeassignparticipant = $timeassignstart;
$DB->update_record('haksa', $haksa);


siteadmin_sync_db_close($conn);
