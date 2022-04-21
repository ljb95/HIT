<?php

require(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once($CFG->dirroot . '/course/lib.php');
require_once($CFG->dirroot . '/lib/coursecatlib.php');
require_once($CFG->dirroot . '/lib/accesslib.php');
require_once($CFG->dirroot . '/lib/sessionlib.php');
require_once($CFG->dirroot . '/lib/enrollib.php');
require_once($CFG->dirroot . '/enrol/locallib.php');
require_once($CFG->dirroot . '/mod/jinotechboard/lib.php');
require_once $CFG->dirroot . '/local/haksa/lib.php';

global $DB, $PAGE;

$courseid = optional_param('courseid', 0, PARAM_INT);                   //courseid(course->id) 값이 0이 아니면 edit
$subject_id = optional_param('subject_id', '', PARAM_RAW);
$year = optional_param('year', 0, PARAM_INT);
$term = optional_param('term', '0', PARAM_INT);
$category1 = optional_param('cata1', 0, PARAM_INT);                         //과정id(course_categories->id)
$category2 = optional_param('cata2', 0, PARAM_INT);                         //과정id(course_categories->id)
$category3 = optional_param('cata3', 0, PARAM_INT);                         //과정id(course_categories->id)
$kor_lec_name = optional_param('kor_lec_name', '', PARAM_RAW);
$eng_lec_name = optional_param('eng_lec_name', '', PARAM_RAW);
$gubun = optional_param('gubun', 0, PARAM_INT);                             //한(1)/영(2)강의 언어, 0-언어지정하지 않음. 
$prof_userid = optional_param('prof_userid', 0, PARAM_INT);                 //교수id(user->id)
$isnonformal = optional_param('isnonformal', 0, PARAM_INT);                 //교과(0) 비교과(1) 이러닝(2)
$section = optional_param('section', 15, PARAM_INT);                        //강의 주차
$isreged = optional_param('isreged', 1, PARAM_INT);                         //수강신청여부 (1:수강신청받지않음)
$timeregstart = optional_param('timeregstart', 0, PARAM_ALPHANUMEXT);       //수강신청시작
$timeregend = optional_param('timeregend', 0, PARAM_ALPHANUMEXT);           //수강신청종료
$timestart = optional_param('timestart', 0, PARAM_ALPHANUMEXT);             //강의시작
if ($isnonformal) {
    $timeend = optional_param('timeend', 0, PARAM_ALPHANUMEXT);                 //강의종료
    $course->enddate = strtotime($timeend) + 86399;
}
$certificate = optional_param('formid', 0, PARAM_INT);                      //이수증 아이디
$certiform = optional_param('certiform', 0, PARAM_INT);                     //이수증 양식 선택
$certiform_en = optional_param('certiform_en', 0, PARAM_INT);               //이수증 양식 선택(영어)
$objective = optional_param('objective', '', PARAM_RAW);                    //학습목표
$bunban = optional_param('bunban', '', PARAM_RAW);                          //분반코드
$tag = optional_param('tag_hidden', '', PARAM_RAW);                         //태그
$univ_type = optional_param('univ_type', 0, PARAM_INT);
$learningtime = optional_param('learningtime', 0, PARAM_INT);                  //학습시간

if (!empty($category3)) {
    $category = $category3;
} else if (!empty($category2)) {
    $category = $category2;
} else {
    $category = $category1;
}

$LMSUSER = $DB->get_record('lmsdata_user', array('userid' => $USER->id));
if ($LMSUSER->usergroup == 'de' || $LMSUSER->usergroup == 'pr') {
    $cate = $DB->get_record('course_categories', array('name' => $LMSUSER->domain));
    if (!$cate) {
        $cate = array();
        local_haksa_get_course_categories($cate);
        $path = array();
        $path[] = $DB->get_field_sql('SELECT name FROM {course_categories} WHERE idnumber = :idnumber', array('idnumber' => 'oklass_regular'));
        $path[] = $DB->get_field_sql('SELECT name FROM {course_categories} WHERE idnumber = :idnumber', array('idnumber' => 'HIT'));
        $path[] = $LMSUSER->domain;
        $haksa_class->category = local_haksa_find_or_create_category($path, $cate);
    } else {
        $category = $cate->id;
    }
}


// Insert course table
$course = new stdClass();

$course->category = $category;

$course->format = optional_param('format', 'topics', PARAM_RAW);
$course->startdate = strtotime($timestart);
$course->timecreated = time();
$course->timemodified = $course->timecreated;
$course->visible = 1;
$course->summary = $objective;
$course->summaryformat = FORMAT_HTML;

$course->numsections = $section;
$course->hiddensections = 0;
$course->coursedisplay = 0;
$course->theme = '';
$course->calendartype = '';
$course->newsitems = 5;
$course->showgrades = 1;
$course->showreports = 0;
$course->maxbytes = 0;
$course->enablecompletion = 1;
$course->enrol_guest_status_0 = 1;
$course->groupmode = 0;
$course->groupmodeforce = 0;
$course->defaultgroupingid = 0;
$course->role_1 = ' ';
$course->role_2 = ' ';
$course->role_3 = ' ';
$course->role_4 = ' ';
$course->role_5 = ' ';
$course->role_6 = ' ';
$course->role_7 = ' ';
$course->role_8 = ' ';
$course->category = $category;



if ($gubun == 1) {
    $course->fullname = $kor_lec_name;
    $course->lang = 'ko';
} else if ($gubun == 2) {
    $course->lang = 'en';
    $course->fullname = $eng_lec_name;
} else {
    $course->lang = '';
    $course->fullname = $kor_lec_name;
}

$course->sortorder = 0;
$course->visibleold = $course->visible;

// Insert lmsdata_class table

$lmsdata_class = new stdClass();
$lmsdata_class->category = $course->category;
$lmsdata_class->year = $year;
$lmsdata_class->term = $term;
// 예과(1)/본과(2)
// 과목코드, 학과코드, 도메인 추후
$lmsdata_class->domain = '';
$lmsdata_class->subject_id = $subject_id;
$lmsdata_class->kor_lec_name = $kor_lec_name;
$lmsdata_class->univ_type = $univ_type;
$lmsdata_class->eng_lec_name = $eng_lec_name;
$lmsdata_class->prof_userid = $prof_userid;
// $lmsdata_class->required = $required;
$lmsdata_class->timestart = strtotime($timestart);
$lmsdata_class->timeend = strtotime($timeend) + 86399;
$lmsdata_class->timeregstart = strtotime($timeregstart);
$lmsdata_class->learningtime = $learningtime;

if (!empty($timeregend)) {
    $lmsdata_class->timeregend = strtotime($timeregend) + 86399;
} else {
    $lmsdata_class->timeregend = 0;
}
$lmsdata_class->isreged = 1;

$lmsdata_class->isnonformal = $isnonformal;
$lmsdata_class->gubun = $gubun;
$lmsdata_class->timemodified = time();
//수강방법
$lmsdata_class->certificateid = $certificate;
$lmsdata_class->certiform = $certiform;
$lmsdata_class->certiform_en = $certiform_en;

//분반코드
$lmsdata_class->bunban = $bunban;
//태그
$lmsdata_class->tag = $tag;

if (empty($courseid)) {
    // course shortname 동기화 이후
    $course->shortname = time();
    $newcourseid = $DB->insert_record('course', $course);

    $lmsdata_class->course = $newcourseid;
    $lmsdata_class->ohakkwa = '';
    $DB->insert_record('lmsdata_class', $lmsdata_class);

    $parentcontext = context_coursecat::instance($course->category);
    $record = new stdClass();
    $record->contextlevel = CONTEXT_COURSE;
    $record->instanceid = $newcourseid;
    $record->depth = 0;
    $record->path = null; //not known before insert

    $record->id = $DB->insert_record('context', $record);

    if (!is_null($parentcontext->path)) {
        $record->path = $parentcontext->path . '/' . $record->id;
        $record->depth = substr_count($record->path, '/');
        $DB->update_record('context', $record);
    }
    course_get_format($newcourseid)->update_course_format_options($course);

    $newcourse = course_get_format($newcourseid)->get_course();

    blocks_add_default_course_blocks($newcourse);

    course_create_sections_if_missing($newcourse, range(0, $section));

    new_course_create_activity_jinotechboard($newcourse, 1);
    new_course_create_activity_jinotechboard($newcourse, 2);
    new_course_create_activity_jinotechboard($newcourse, 3);

    enrol_course_updated(true, $newcourse, $course);
} else {
    $newcourseid = $courseid;
    $course->id = $courseid;
    $DB->update_record('course', $course);

    course_create_sections_if_missing($course->id, range(0, $section));

    $numsections = $DB->get_field_sql('SELECT max(section) from {course_sections}
                            WHERE course = ?', array($courseid));


    if ($numsections > $section) {
        $delete_section = range($numsections, $section);
        foreach ($delete_section as $ds => $sid) {
            course_delete_section($courseid, $sid);
        }
    }

    course_get_format($newcourseid)->update_course_format_options($course);
    $lmsdata_class->course = $courseid;
    $lmsdata_class->id = $DB->get_field('lmsdata_class', 'id', array('course' => $newcourseid));
    $DB->update_record('lmsdata_class', $lmsdata_class);
}

$context = context_course::instance($newcourseid, MUST_EXIST);

$PAGE->set_context($context);

$filename = $_FILES['overviewfiles']['name'];
$filepath = $_FILES['overviewfiles']['tmp_name'];

if (!empty($filename)) {
    $course = $DB->get_record('course', array('id' => $newcourseid));
    $courseimage = new course_in_list($course);
    foreach ($courseimage->get_course_overviewfiles() as $file) {
        $old_filename = $file->get_filename();
        if (!empty($old_filename)) {
            $file->delete();
        }
    }

    $filerecord = array(
        'contextid' => $context->id,
        'component' => 'course',
        'filearea' => 'overviewfiles',
        'itemid' => 0,
        'filepath' => '/',
        'filename' => $filename
    );

    $fs = get_file_storage();
    $file = $fs->create_file_from_pathname($filerecord, $filepath);
}

fix_course_sortorder();

$role = $DB->get_record('role', array('shortname' => 'editingteacher'));
$enrol_course = $DB->get_record('course', array('id' => $newcourseid));
$manager = new course_enrolment_manager($PAGE, $enrol_course);

if ($prof_userid != 0) {
    if (!$manager->get_user_enrolments($prof_userid)) {
        $enrol = $DB->get_record('enrol', array('enrol' => 'manual', 'courseid' => $enrol_course->id));
        // 강의생성 후 바로 /my 페이지에 안보이는 문제 때문에 0 으로 바꿈
        // 교수이기 때문에 문제 없음.
        $timestart = $enrol_course->timecreated;
        $timeend = 0;

        $instances = $manager->get_enrolment_instances();
        $plugins = $manager->get_enrolment_plugins();

        $instance = $instances[$enrol->id];
        $plugin = $plugins[$instance->enrol];


        $plugin->enrol_user($instance, $prof_userid, $role->id, $timestart, $timeend);
    }
}

if (empty($courseid)) {
// Trigger a course created event.
    $event = \core\event\course_created::create(array(
                'objectid' => $newcourseid,
                'context' => context_course::instance($newcourseid),
                'other' => array('shortname' => $course->shortname,
                    'fullname' => $course->fullname)
    ));
    $event->trigger();
}

echo '<script>alert("등록되었습니다.");location.href="' . $CFG->wwwroot . '/siteadmin/manage/course_list.php?coursetype=' . $isnonformal . '"</script>';




