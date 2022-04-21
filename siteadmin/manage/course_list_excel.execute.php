<?php 
require(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once($CFG->dirroot.'/siteadmin/lib.php');
require_once($CFG->dirroot.'/course/lib.php');
require_once($CFG->dirroot.'/lib/coursecatlib.php');
require_once($CFG->dirroot.'/lib/accesslib.php');
require_once($CFG->dirroot.'/lib/sessionlib.php');
require_once($CFG->dirroot.'/lib/enrollib.php');
require_once($CFG->dirroot.'/enrol/locallib.php');
require_once($CFG->libdir.'/phpexcel/PHPExcel.php');
require_once($CFG->libdir.'/phpexcel/PHPExcel/IOFactory.php');

global $DB, $PAGE;

include_once (dirname(dirname (__FILE__)).'/inc/header.php'); 

?>

<div id="contents">
    <?php include_once  (dirname(dirname (__FILE__)).'/inc/sidebar_manage.php');?>
    <div id="content">
        
<?php       
$filename = $_FILES['course_excel']['name'];
$filepath = $_FILES['course_excel']['tmp_name'];

$objReader = PHPExcel_IOFactory::createReaderForFile($filepath);
$objReader->setReadDataOnly(true);
$objExcel = $objReader->load($filepath);

$objExcel->setActiveSheetIndex(0);
$objWorksheet = $objExcel->getActiveSheet();
$rowIterator = $objWorksheet->getRowIterator();

foreach ($rowIterator as $row) { // 모든 행에 대해서
    $cellIterator = $row->getCellIterator();
    $cellIterator->setIterateOnlyExistingCells(false);
}

$maxRow = $objWorksheet->getHighestRow();

for ($i = 2; $i <= $maxRow; $i++) {
    $courseid = $objWorksheet->getCell('A' . $i)->getValue();
    $year_term  = $objWorksheet->getCell('B' . $i)->getValue();
    $univ       = $objWorksheet->getCell('C' . $i)->getValue();
    $subid_sbb  = $objWorksheet->getCell('D' . $i)->getValue();
    $grade      = $objWorksheet->getCell('E' . $i)->getValue();
    $hyear    = $objWorksheet->getCell('F' . $i)->getValue();
    $kor_lec_name = $objWorksheet->getCell('G' . $i)->getValue();
    $eng_lec_name = $objWorksheet->getCell('H' . $i)->getValue();
//  $prof_name  = $objWorksheet->getCell('I' . $i)->getValue();      //교수명 - 사용하지 않음
    $prof_no    = trim(strtolower($objWorksheet->getCell('J' . $i)->getValue()));        //교번 - user 테이블 username
    $required   = $objWorksheet->getCell('K' . $i)->getValue();
    $timestart  = $objWorksheet->getCell('L' . $i)->getValue();
    $timeend    =  $objWorksheet->getCell('M' . $i)->getValue();
    $lectime2   = $objWorksheet->getCell('N' . $i)->getValue();
    $lectime1   = $objWorksheet->getCell('O' . $i)->getValue();
    $lectype    = $objWorksheet->getCell('P' . $i)->getValue();
    $competence = $objWorksheet->getCell('Q' . $i)->getValue();
    $objective  = $objWorksheet->getCell('R' . $i)->getValue();
    
    if(empty($subid_sbb)) {
        continue;
    }
    
    if(empty($year_term)) {
        continue;
    }
    
    $year = substr($year_term, 0, 4);
    $term = substr($year_term, 4, 2);
    
    $term_group = substr($year_term, 4, 1);
    
    if($term_group == '0') {
        $term_arr['00'] = '00';
    } else if($term_group == '1') {
        $term_arr['10'] = '10';
        $term_arr['11'] = '11';
        $term_arr['12'] = '12';
    } else if($term_group == '2') {
        $term_arr['20'] = '20';
        $term_arr['23'] = '23';
        $term_arr['24'] = '24';
    }
    
    list($sql_in, $params) = $DB->get_in_or_equal($term_arr, SQL_PARAMS_NAMED, 'term');
    
    $params['year'] = $year;
    $params['subject_id'] = $subject_id;
    
    if(!empty($courseid)) {
        $params['courseid'] = $courseid;
    }
    
    //subid_sbb = 강의코드-분반-실습분반
    $subid_sbb_arr = explode("-",$subid_sbb);
    if(count($subid_sbb_arr) == 3) {
        $sbb        = $subid_sbb_arr[2];
        $category   = $DB->get_field('lmsdata_categories', 'category', array('subject_id'=>$subid_sbb_arr[0]));
        if(empty($category)) {
            siteadmin_println($kor_lec_name.' 강의('.$i.'줄)의 강의코드가 맞지 않습니다.'); 
            continue;
        }
        
    } else {
        siteadmin_println($kor_lec_name.' 강의('.$i.'줄)의 강의코드가 비어있습니다.'); 
        continue;
    }
    
    if(empty($courseid) && $DB->record_exists_sql('SELECT * FROM {lmsdata_class} WHERE year = :year and subject_id = :subject_id and term '.$sql_in, $params)) {
        siteadmin_println($kor_lec_name.' 강의('.$i.'줄)의 강의코드_분반_실습분반 이 이미 존재합니다.'); 
        continue;
    }
    
    if(!empty($courseid) && $DB->record_exists_sql(' SELECT * FROM {lmsdata_class} WHERE course != :courseid and year = :year and subject_id = :subject_id and term '.$sql_in, $params)) {
        siteadmin_println($kor_lec_name.' 강의('.$i.'줄)의 강의코드_분반_실습분반 이 이미 존재합니다.'); 
        continue;
    }
    
    
    
    
    if(empty($eng_lec_name)){
        $eng_lec_name = '';
    }
    $gubun = 1;
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
    $course->fullname = $kor_lec_name;
    $course->lang = 'ko';

    $course->sortorder = 0;
    $course->visibleold = $course->visible;

    // Insert lmsdata_course table
    $prof_userid = ''; 
    if(!empty($prof_no)) {
        $prof_userid = $DB->get_field('user', 'id', array('username'=>$prof_no));
        if(empty($prof_userid)){
            $prof_userid = '';
        }        
    }
    $lmsdata_course = new stdClass();
    $lmsdata_course->category = $course->category;
    $lmsdata_course->year = $year;
    $lmsdata_course->term = $term;
    // 예과(1)/본과(2)
    $lmsdata_course->univ = $univ;

    // 도메인 추후
    $lmsdata_course->domain = time();

    $lmsdata_course->sbb = $sbb;
    $lmsdata_course->grade = $grade;
    //학년
    $lmsdata_course->hyear = $hyear;
    $lmsdata_course->kor_lec_name = $kor_lec_name;
    $lmsdata_course->eng_lec_name = $eng_lec_name;
    $lmsdata_course->prof_userid = $prof_userid;
    $lmsdata_course->required = $required;
    $lmsdata_course->timestart = strtotime($timestart);
    $lmsdata_course->timeend = strtotime($timeend)+86399;
    $lmsdata_course->timeregstart = 0;
    $lmsdata_course->timeregend = 0;
    $lmsdata_course->isreged = 1;
    $lmsdata_course->lectime = $lectime2.'-'.$lectime1;
    $lmsdata_course->lectype = $lectype;

    //역량맵핑 추후 결정되면
    $lmsdata_course->competence = $competence;

    $lmsdata_course->objective = $objective;
    $lmsdata_course->isnonformal = 0;
    $lmsdata_course->gubun = $gubun;
    $lmsdata_course->timemodified = time();
    $lmsdata_course->student_num = 0;
    //수강방법
    $lmsdata_course->request = 1;
    $lmsdata_course->certificate = 0;
    $lmsdata_course->courseend_flag = 0;

    if(empty($courseid)) {
        // course shortname 동기화 이후
        $course->shortname = time();
        $newcourseid = $DB->insert_record('course', $course);

        $lmsdata_course->course = $newcourseid;
        $lmsdata_course->ohakkwa = '';
        $lmsdata_course->subject_id = $subid_sbb;
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
        
        siteadmin_println($kor_lec_name.' 강의('.$i.'줄)가 생성되었습니다.'); 
        
    } else {
        $newcourseid = $courseid;
        $course->id = $courseid;
        $DB->update_record('course', $course);
        course_get_format($newcourseid)->update_course_format_options($course);
        $lmsdata_course->course = $courseid;
        $lmsdata_course->id = $DB->get_field('lmsdata_class', 'id', array('course'=>$newcourseid));
        $DB->update_record('lmsdata_class', $lmsdata_course);
        siteadmin_println($kor_lec_name.' 강의('.$i.'줄)가 업데이트 되었습니다.');
    }

    fix_course_sortorder();
    
    if(!empty($prof_userid)) {
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
    } else {
    // Trigger a course updated event.
        $event = \core\event\course_updated::create(array(
            'objectid' => $courseid,
            'context' => context_course::instance($courseid),
            'other' => array('shortname' => time(),
                             'fullname' => $course->fullname)
        ));
        $event->trigger();
    }

}
siteadmin_scroll_down();
?>
    <input type="button" class="blue_btn" style="float:right;margin-right: 10px;" value="<?php echo get_string('okay','local_lmsdata'); ?>" onclick="location.href='<?php echo $CFG->wwwroot.'/siteadmin/manage/course_list.php'?>'"/>    
    </div><!--Content End-->
</div> <!--Contents End-->
<?php include_once ('../inc/footer.php');?>
