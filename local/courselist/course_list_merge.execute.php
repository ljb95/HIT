<?php 
require_once dirname(dirname(dirname (__FILE__))).'/config.php';
require_once $CFG->dirroot.'/siteadmin/lib/paging.php';
require_once $CFG->dirroot.'/siteadmin/lib.php';
require_once($CFG->dirroot.'/course/lib.php');
require_once($CFG->dirroot.'/lib/coursecatlib.php');
require_once($CFG->dirroot.'/lib/accesslib.php');
require_once($CFG->dirroot.'/lib/sessionlib.php');
require_once($CFG->dirroot.'/lib/enrollib.php');
require_once($CFG->dirroot.'/enrol/locallib.php');

global $DB, $PAGE, $SESSION;

$courseid = optional_param('courseid', 0, PARAM_INT);
$category1 = optional_param('cata1', 0, PARAM_INT);
$category2 = optional_param('cata2', 0, PARAM_INT);
$category3 = optional_param('cata3', 0, PARAM_INT);
$visible = optional_param('visible', 0, PARAM_INT);

$course_list = optional_param_array('course', array(), PARAM_INT);
$year = optional_param('year', 0, PARAM_INT);
$term = optional_param('term', '00', PARAM_RAW);
$category1 = optional_param('cata1', 0, PARAM_INT);                         //과정id(course_categories->id)
$category2 = optional_param('cata2', 0, PARAM_INT);                         //과정id(course_categories->id)
$category3 = optional_param('cata3', 0, PARAM_INT);                         //과정id(course_categories->id)
$kor_lec_name = optional_param('kor_lec_name', '', PARAM_RAW);
$eng_lec_name = optional_param('eng_lec_name', '', PARAM_RAW);
$gubun = optional_param('gubun', 1, PARAM_INT);                             //한(1)/영(2)강의 언어 
$prof_userid = optional_param('prof_userid', 0, PARAM_INT);                 //교수id(user->id)
$isnonformal = optional_param('isnonformal', 0, PARAM_INT);                 //정규(0) 비정규(1)
$section = optional_param('section', 15, PARAM_INT);                        //강의 주차
$isreged = optional_param('isreged', 1, PARAM_INT);                         //수강신청여부
$timeregstart = optional_param('timeregstart', 0, PARAM_ALPHANUMEXT);       //수강신청시작
$timeregend = optional_param('timeregend', 0, PARAM_ALPHANUMEXT);           //수강신청종료
$timestart = optional_param('timestart', 0, PARAM_ALPHANUMEXT);             //강의시작
$timeend = optional_param('timeend', 0, PARAM_ALPHANUMEXT);                 //강의종료
$certificate = optional_param('certificate', 1, PARAM_INT);                 //이수증 발급여부 예(1) 아니오(0)
$objective = optional_param('objective', '', PARAM_RAW);                    //학습목표

if(!empty($category3)) {
    $category = $category3;
} else if(!empty($category2)) {
    $category = $category2;
} else {
    $category = $category1;
}

     // Insert course table
    $course = new stdClass();

    $course->category = $category;
    $course->format = 'weeks';
    $course->summary = $objective;
    $course->startdate = strtotime($timestart);
    $course->timecreated = time();
    $course->timemodified = $course->timecreated;
    $course->visible = 1;
    $course->summaryformat = 1;
    
    $course->numsections = $section;
    $course->hiddensections = 0;
    $course->coursedisplay = 0;
    $course->theme = 'creativeband'; 
    $course->lang = 'ko';
    $course->calendartype = 'yonsei';
    $course->newsitems = 5;
    $course->showgrades = 1;
    $course->showreports = 0;
    $course->maxbytes = 0;
    $course->enablecompletion = 0;
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
    
    $course->fullname = $kor_lec_name;
    $course->shortname = time();
    $course->sortorder = 0;

    $course->visibleold = $course->visible;
    
    // Insert lmsdata_class table
    $lmsdata_class = new stdClass();
    $lmsdata_class->category = $category;
    $lmsdata_class->kor_lec_name = $kor_lec_name;
    $lmsdata_class->eng_lec_name = $eng_lec_name;
    $lmsdata_class->prof_userid = $prof_userid;
    $lmsdata_class->year = $year;
    $lmsdata_class->term = $term;
    $lmsdata_class->timestart = strtotime($timestart);
    $lmsdata_class->timeend = strtotime($timeend);
    $lmsdata_class->timeregstart = strtotime($timeregstart);
    $lmsdata_class->timeregend = strtotime($timeregend);
    $lmsdata_class->isreged = $isreged == 1 ? 0 : 1;
    $lmsdata_class->isnonformal = $isnonformal;
    $lmsdata_class->gubun = $gubun;
    $lmsdata_class->timemodified = time();
    
    $newcourseid = $DB->insert_record('course', $course);

    $lmsdata_class->course = $newcourseid;
    $DB->insert_record('lmsdata_class', $lmsdata_class);   

    $parentcontext = context_coursecat::instance($course->category);
    $record = new stdClass();
    $record->contextlevel = CONTEXT_COURSE;
    $record->instanceid   = $newcourseid;
    $record->depth        = 0;
    $record->path         = null; //not known before insert

    $record->id = $DB->insert_record('context', $record);

    if (!is_null($parentcontext->path)) {
        $record->path = $parentcontext->path.'/'.$record->id;
        $record->depth = substr_count($record->path, '/');
        $DB->update_record('context', $record);
    }
    course_get_format($newcourseid)->update_course_format_options($course);

    $newcourse = course_get_format($newcourseid)->get_course();

    blocks_add_default_course_blocks($newcourse);

    course_create_sections_if_missing($newcourse, 0);

    enrol_course_updated(true, $newcourse, $course);

    // Trigger a course created event.
    $event = \core\event\course_created::create(array(
        'objectid' => $newcourseid,
        'context' => context_course::instance($newcourseid),
        'other' => array('shortname' => $course->shortname,
                         'fullname' => $course->fullname)
    ));
    $event->trigger();
    
    $context = context_course::instance($newcourseid, MUST_EXIST);
    
    $PAGE->set_context($context);
    
    $filename = $_FILES['overviewfiles']['name'];
    $filepath = $_FILES['overviewfiles']['tmp_name'];
    
    if(!empty($filename)){
        $filerecord = array(
            'contextid' => $context->id,
            'component' => 'course',
            'filearea'  => 'overviewfiles',
            'itemid'    => 0,
            'filepath'  => '/',
            'filename'  => $filename
        );

        $fs = get_file_storage();
        $file = $fs->create_file_from_pathname($filerecord, $filepath);
    }
    
fix_course_sortorder();
    
// 등록할 users
$assign_users = get_courses_role_assignments($course_list);

$new_course = $DB->get_record('course', array('id' => $newcourseid));

foreach ($assign_users as $assign_user) {
    set_assign_user($new_course, $assign_user);
}

foreach ($course_list as $courseid) {
    course_change_visibility($courseid, false);
    add_class_drive_log($newcourseid, $courseid, LMSDATA_CLASS_MERGE_EXECUTE, $visible);
}

echo '<script type="text/javascript">'
    .'  document.location.href="course_manage.php";'
    .'</script>';
    
