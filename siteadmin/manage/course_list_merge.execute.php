<?php 
require(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once($CFG->dirroot.'/course/lib.php');
require_once($CFG->dirroot.'/lib/coursecatlib.php');
require_once($CFG->dirroot.'/lib/accesslib.php');
require_once($CFG->dirroot.'/lib/sessionlib.php');
require_once($CFG->dirroot.'/lib/enrollib.php');
require_once($CFG->dirroot.'/enrol/locallib.php');
require_once($CFG->dirroot.'/siteadmin/lib.php');

global $DB, $PAGE;

$courseid = optional_param('courseid', 0, PARAM_INT);                   //courseid(course->id) 값이 0이 아니면 edit
$year = optional_param('year', 0, PARAM_INT);
$term = optional_param('term', 0, PARAM_INT);
$category = optional_param('category', 0, PARAM_INT);                   //과정id(course_categories->id)
$subject_id = optional_param('categoryname', 0, PARAM_RAW);             //강의코드(lmsdata_categories->subject_id)
$kor_lec_name = optional_param('kor_lec_name', '', PARAM_RAW);
$eng_lec_name = optional_param('eng_lec_name', '', PARAM_RAW);
$gubun = optional_param('gubun', 1, PARAM_INT);                         //한(1)/영(2)강의 언어 
$prof_userid = optional_param('prof_userid', 0, PARAM_INT);             //교수id(user->id)
$sbb = optional_param('sbb', '', PARAM_RAW);                            //실습분반
$required = optional_param('required', 1, PARAM_INT);                   //종별
$grade = optional_param('grade', 0, PARAM_INT);                         //학점
$hyears = required_param_array('hyear', PARAM_RAW);                 //학년
$isreged = optional_param('isreged', 1, PARAM_INT);                     //수강신청여부
$timeregstart = optional_param('timeregstart', 0, PARAM_ALPHANUMEXT);	//수강신청시작
$timeregend = optional_param('timeregend', 0, PARAM_ALPHANUMEXT);	//수강신청종료
$timestart = optional_param('timestart', 0, PARAM_ALPHANUMEXT);		//강의시작
$timeend = optional_param('timeend', 0, PARAM_ALPHANUMEXT);		//강의종료
$lectime1 = optional_param('lectime1', '', PARAM_RAW);			//강의시간 - 값
$lectime2 = optional_param('lectime2', 0, PARAM_INT);			//강의시간 - 단위(주or시간)
$lectype = optional_param('lectype', 1, PARAM_INT);			//강의유형(1:강의형,2:실습형,3:기타)
$objective = optional_param('objective', '', PARAM_RAW);                //학습목표
$student_num = optional_param('student_num', 0, PARAM_INT);             //수강인원
$visible = optional_param('visible', 0, PARAM_INT);                     //기존강의숨김(1), 기존강의삭제(0)
$competence = optional_param('competence_string', '', PARAM_RAW);             //역량맵핑

$course_list = optional_param_array('course', array(), PARAM_INT);      //통합분반 리스트
 // Insert course table
$course = new stdClass();

$course->format = 'topics';
$course->startdate = strtotime($timestart);
$course->timecreated = time();
$course->timemodified = $course->timecreated;
$course->visible = 1;
$course->summary = $objective;
$course->summaryformat = FORMAT_HTML;

$course->numsections = 10;
$course->hiddensections = 0;
$course->coursedisplay = 0;
$course->theme = 'creativeband'; 
$course->calendartype = '';
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
$course->category = $category;
    
if($gubun == 1) {
    $course->fullname = $kor_lec_name;
    $course->lang = 'ko';
}else if($gubun == 2){
    $course->lang = 'en';
    $course->fullname = $eng_lec_name;
}

$course->sortorder = 0;
$course->visibleold = $course->visible;

// Insert lmsdata_course table

$hyear = implode('', $hyears);
$lmsdata_course = new stdClass();
$lmsdata_course->category = $course->category;
$lmsdata_course->year = $year;
$lmsdata_course->term = $term;
// 예과(1)/본과(2)
$lmsdata_course->univ = strpos($hyear, 'p') !== false ? 1 : 2;

// 과목코드, 학과코드, 도메인 추후
$lmsdata_course->domain = time();

$lmsdata_course->sbb = $sbb;
$lmsdata_course->grade = $grade;
//학년
$lmsdata_course->hyear = str_replace('p', '', $hyear);
$lmsdata_course->kor_lec_name = $kor_lec_name;
$lmsdata_course->eng_lec_name = $eng_lec_name;
$lmsdata_course->prof_userid = $prof_userid;
$lmsdata_course->required = $required;
$lmsdata_course->timestart = strtotime($timestart);
$lmsdata_course->timeend = strtotime($timeend);
$lmsdata_course->timeregstart = strtotime($timeregstart);
$lmsdata_course->timeregend = strtotime($timeregend);
$lmsdata_course->isreged = $isreged;
$lmsdata_course->lectime = $lectime2.'-'.$lectime1;
$lmsdata_course->lectype = $lectype;

//역량맵핑 추후 결정되면
$lmsdata_course->competence = $competence;
$lmsdata_course->objective = $objective;
$lmsdata_course->isnonformal = 0;
$lmsdata_course->gubun = $gubun;
$lmsdata_course->timemodified = time();
$lmsdata_course->student_num = $student_num;
//수강방법
$lmsdata_course->request = 1;
$lmsdata_course->certificate = 0;
$lmsdata_course->courseend_flag = 0;

    // course shortname 동기화 이후
    $course->shortname = time();
    $newcourseid = $DB->insert_record('course', $course);

    $lmsdata_course->course = $newcourseid;
    $lmsdata_course->ohakkwa = '';
    $lmsdata_course->subject_id = $subject_id.'-'.$sbb;
    $DB->insert_record('lmsdata_class', $lmsdata_course);   

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

$context = context_course::instance($newcourseid, MUST_EXIST);

$PAGE->set_context($context);

$filename = $_FILES['overviewfiles']['name'];
$filepath = $_FILES['overviewfiles']['tmp_name'];

if(!empty($filename)){
    $course = $DB->get_record('course', array('id' => $newcourseid));
    $courseimage = new course_in_list($course);
    foreach ($courseimage->get_course_overviewfiles() as $file) {
        $old_filename = $file->get_filename();
        if(!empty($old_filename)) {
            $file->delete();
        }
    }

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

$role = $DB->get_record('role', array('shortname'=>'editingteacher'));
$enrol_course = $DB->get_record('course', array('id' => $newcourseid));
$manager = new course_enrolment_manager($PAGE, $enrol_course);

if(!$manager->get_user_enrolments($prof_userid)) { 
    $enrol = $DB->get_record('enrol', array('enrol'=>'manual', 'courseid'=>$enrol_course->id));
    // 강의생성 후 바로 /my 페이지에 안보이는 문제 때문에 0 으로 바꿈
    // 교수이기 때문에 문제 없음.
    //$timestart = $enrol_course->timecreated;
    $timestart = 0;
    $timeend = 0;

    $instances = $manager->get_enrolment_instances();
    $plugins = $manager->get_enrolment_plugins();

    $instance = $instances[$enrol->id];
    $plugin = $plugins[$instance->enrol];


    $plugin->enrol_user($instance, $prof_userid, $role->id, $timestart, $timeend);

}

if(empty($courseid)) {
// Trigger a course created event.
    $event = \core\event\course_created::create(array(
        'objectid' => $newcourseid,
        'context' => context_course::instance($newcourseid),
        'other' => array('shortname' => $course->shortname,
                         'fullname' => $course->fullname)
    ));
    $event->trigger();
}

// 등록할 users
$assign_users = get_courses_role_assignments($course_list);

$new_course = $DB->get_record('course', array('id' => $newcourseid));

foreach ($assign_users as $assign_user) {
    set_assign_user($new_course, $assign_user);
}

foreach ($course_list as $id) {
    if(($key = array_search($id, $SESSION->split_course)) !== false) {
        unset($SESSION->split_course[$key]);
    }
    if($visible) {
        delete_course($id);
        add_class_drive_log($newcourseid, $id, LMSDATA_CLASS_DELETE_EXECUTE, 0);
    } else {
        course_change_visibility($id, false);
        add_class_drive_log($newcourseid, $id, LMSDATA_CLASS_MERGE_EXECUTE, 0);
    }
}

echo '<script type="text/javascript">'
    .'  document.location.href="course_list.php";'
    .'</script>';
    
    
    
    
