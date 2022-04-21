<?php

require(dirname(dirname(dirname(__FILE__))) . '/config.php');
//require_once $CFG->dirroot.'/lib/coursecatlib.php';
require_once $CFG->dirroot . "/course/lib.php";
require_once $CFG->dirroot . "/lib/filelib.php";
require_once $CFG->dirroot . "/local/oklearning/lib.php";
require_once $CFG->dirroot . '/local/oklearning/write_form.php';

require_login();

$type = optional_param('type', 1, PARAM_INT);
$mode = optional_param('mode', 'add', PARAM_RAW);
$courseid = optional_param('id', 0, PARAM_INT);
$parent = $DB->get_record('course_categories', array('idnumber' => 'oklass_selfcourse'));

$enrol = $DB->get_record('enrol', array('enrol'=>'self', 'courseid'=>$courseid));
$password = $enrol->password;
if (!empty($courseid)) {
    $course_sql = " SELECT co.*,
                           lc.year, lc.term, lc.subject_id, lc.kor_lec_name, lc.eng_lec_name,cc.path,
                           lc.timeregstart, lc.timeregend, lc.timestart, lc.timeend,lc.prof_userid, lc.isnonformal, lc.certificate, lc.isreged,
                           ur.firstname as prof_name
                    FROM {course} co
                    JOIN {lmsdata_class} lc ON co.id = lc.course 
                    JOIN {course_categories} cc on cc.id = co.category 
                    LEFT JOIN {user} ur ON lc.prof_userid = ur.id
                    WHERE co.id = :courseid ";
    $params = array('courseid' => $courseid);
    $course = $DB->get_record_sql($course_sql, $params);
    $category = $DB->get_record('course_categories', array('id' => $course->category), '*', MUST_EXIST);
    $coursecontext = context_course::instance($course->id, MUST_EXIST);
    $path_arr = explode('/', $course->path);
} else {
    $course = null;
    $category = $DB->get_record('course_categories', array('idnumber' => 'oklass_selfcourse'));
    $catcontext = context_coursecat::instance($category->id);
}

$context = context_system::instance();
$PAGE->set_context($context);

$PAGE->set_url('/local/oklearning/course_add.php');
$PAGE->set_pagelayout('standard');

if (empty($courseid)) {
    $courseacttext = get_string('course:add', 'local_oklearning');
} else {
    $courseacttext = get_string('course:edit', 'local_oklearning');
}

$strplural = get_string('pluginnameplural', 'local_oklearning');
$PAGE->navbar->ignore_active();
$PAGE->navbar->add($strplural, new moodle_url($CFG->wwwroot.'/local/oklearning/my.php'));
$PAGE->navbar->add($courseacttext);
$PAGE->set_title($courseacttext);
$PAGE->set_heading($courseacttext);
$PAGE->requires->css('/local/oklearning/style.css');
$PAGE->requires->jquery();
$PAGE->requires->jquery_plugin('ui');
$PAGE->requires->jquery_plugin('ui-css');
$PAGE->requires->jquery_plugin('migrate');
$PAGE->requires->js('/siteadmin/manage/course_list.js');
$PAGE->requires->js('/siteadmin/js/lib/jquery.ui.datepicker-ko.js');

$course_start = date('Y-m-d', time());
$course_end = date('Y-m-d', time() + (60 * 60 * 24 * 30));

/* Form 설정 */
$editoroptions = array('maxfiles' => EDITOR_UNLIMITED_FILES, 'maxbytes' => $CFG->maxbytes, 'trusttext' => false, 'noclean' => true);
$overviewfilesoptions = course_overviewfiles_options($course);
if (!empty($course)) {
    // Add context for editor.
    $editoroptions['context'] = $coursecontext;
    $editoroptions['subdirs'] = file_area_contains_subdirs($coursecontext, 'course', 'summary', 0);
    $course = file_prepare_standard_editor($course, 'summary', $editoroptions, $coursecontext, 'course', 'summary', 0);
    if ($overviewfilesoptions) {
        file_prepare_standard_filemanager($course, 'overviewfiles', $overviewfilesoptions, $coursecontext, 'course', 'overviewfiles', 0);
    }

    // Inject current aliases.
    $aliases = $DB->get_records('role_names', array('contextid' => $coursecontext->id));
    foreach ($aliases as $alias) {
        $course->{'role_' . $alias->roleid} = $alias->name;
    }

    // Populate course tags.
    $course->tags = core_tag_tag::get_item_tags_array('core', 'course', $course->id);
} else {
    // Editor should respect category context if course context is not set.
    $editoroptions['context'] = $catcontext;
    $editoroptions['subdirs'] = 0;
    $course = file_prepare_standard_editor($course, 'summary', $editoroptions, null, 'course', 'summary', null);
    if ($overviewfilesoptions) {
        $course1 = file_prepare_standard_filemanager($course, 'overviewfiles', $overviewfilesoptions, null, 'course', 'overviewfiles', 0);
    }
}
$args = array(
    'course' => $course,
    'mode' => $mode,
    'category' => $category,
    'editoroptions' => $editoroptions
);

$mform = new local_oklearning_write_form(null, $args);

if ($mode == "delete") {
    redirect("apply.php");
}
// 취소 버튼 클릭시
if ($mform->is_cancelled()) {
    redirect("apply.php");

// 폼으로 부터 받은 데이터 가있으면 = Submit 되었을 때
} else if ($data = $mform->get_data()) {
    
    if (!$data->id) {

        $course = new stdClass();
        $course->startdate = $data->timestart;
        $course->timecreated = time();
        $course->timemodified = $course->timecreated;
        $course->visible = 1;
        $course->summary = $data->summary_editor[text];
        $course->summaryformat = FORMAT_HTML;

        $course->numsections = 10;
        $course->hiddensections = 0;
        $course->coursedisplay = 0;
        $course->theme = '';
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
        $catego = $DB->get_record('course_categories',array('idnumber'=>'community'));
        $course->category = $catego->id;
        $course->fullname = $data->kor_lec_name;
        $course->lang = '';

        $course->sortorder = 0;
        $course->visibleold = $course->visible;

        // Insert lmsdata_class table

        $lmsdata_class = new stdClass();
        $lmsdata_class->category = $course->category;
        $lmsdata_class->year = date('Y');
        $lmsdata_class->term = 0;

        $lmsdata_class->domain = time();

        $lmsdata_class->kor_lec_name = $data->kor_lec_name;
        $lmsdata_class->eng_lec_name = $data->eng_lec_name;
        $lmsdata_class->prof_userid = $USER->id;
        $lmsdata_class->timestart = $data->timestart;
        $lmsdata_class->timeend = $data->timeend;
        $lmsdata_class->timeregstart = time();

        if (!empty($data->timeregend)) {
            $lmsdata_class->timeregend = $data->timeregend + 86399;
        } else {
            $lmsdata_class->timeregend = 0;
        }
        $lmsdata_class->isreged = 1;
        $lmsdata_class->isnonformal = 2;
        $lmsdata_class->gubun = '';
        $lmsdata_class->timemodified = time();
        $lmsdata_class->isopened = $data->isopened;
        $lmsdata_class->purpose = $data->purpose;

        // course shortname 동기화 이후
        $course->shortname = time();
        $newcourseid = $DB->insert_record('course', $course);

        $lmsdata_class->course = $newcourseid;
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

        course_create_sections_if_missing($newcourse, 0);
        
        $course = $DB->get_record('course', array('id' => $newcourseid));

        enrol_course_updated(true, $newcourse, $course);
        
        $role = $DB->get_record('role', array('shortname' => 'editingteacher'));
        enrol_try_internal_enrol($newcourseid, $USER->id, $role->id);
        
        $event = \core\event\course_created::create(array(
                'objectid' => $newcourseid,
                'context' => context_course::instance($newcourseid),
                'other' => array('shortname' => $course->shortname,
                    'fullname' => $course->fullname)
        ));
        $event->trigger();
        
    } else {
        $newcourseid = $data->id;
        $course->startdate = $data->timestart;
        $course->timemodified = time();
        $course->summary = $data->summary_editor[text];
        $course->summaryformat = FORMAT_HTML;
        $course->category = $data->category;
        $course->fullname = $data->kor_lec_name;

        $lmsdata_class = $DB->get_record('lmsdata_class', array('course' => $course->id));
        $lmsdata_class->category = $course->category;
        $lmsdata_class->kor_lec_name = $data->kor_lec_name;
        $lmsdata_class->eng_lec_name = $data->eng_lec_name;
        $lmsdata_class->timestart = $data->timestart;
        $lmsdata_class->timeend = $data->timeend;
        $lmsdata_class->timemodified = time();
        $lmsdata_class->isopened = $data->isopened;
        $lmsdata_class->purpose = $data->purpose;

        $DB->update_record('course', $course);
        course_get_format($newcourseid)->update_course_format_options($course);
        $DB->update_record('lmsdata_class', $lmsdata_class);
    }
    
    $context = context_course::instance($newcourseid, MUST_EXIST);

    $PAGE->set_context($context);
    if(!$data->id){ 
        $data->id = $newcourseid;
    }
    update_course_overviewfile($data);

    fix_course_sortorder();
    
    //스스로 등록 설정
    
    if ($instance = $DB->get_record('enrol', array('enrol'=>'self', 'courseid'=>$newcourseid))) {
        //승인방법 (1: 자동 승인, 2: 비밀번호, 3: 개설자 승인)
        $approved = $data->approved;
        $enroldata = new stdClass();
        $enroldata->status = $approved;
        if($approved == 1){
           $enroldata->password = $data->approvedpass;
        }
        $enrol = enrol_get_plugin('self');
        $enrol->update_instance($instance, $enroldata);
    }

    redirect("my.php");
    die();
    
} else {
    if (!empty($course)) {
        if(!$course->approved){
            $course->approved = $enrol->status;
        }
        $mform->set_data($course);
    }

    echo $OUTPUT->header();

    /* form 출력 */
    $mform->display();

    echo $OUTPUT->footer();
}
?>
