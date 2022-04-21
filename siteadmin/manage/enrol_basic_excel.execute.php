<?php 
require(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once($CFG->dirroot.'/siteadmin/lib.php');
require_once($CFG->dirroot.'/course/lib.php');
require_once($CFG->dirroot.'/lib/coursecatlib.php');
require_once($CFG->dirroot.'/lib/accesslib.php');
require_once($CFG->dirroot.'/lib/enrollib.php');
require_once($CFG->dirroot.'/enrol/locallib.php');
require_once($CFG->libdir.'/phpexcel/PHPExcel.php');
require_once($CFG->libdir.'/phpexcel/PHPExcel/IOFactory.php');

global $DB, $PAGE;

$year = required_param('year', PARAM_INT);
$term = required_param('term', PARAM_RAW);

include_once (dirname(dirname (__FILE__)).'/inc/header.php'); 

?>

<div id="contents">
    <?php include_once  (dirname(dirname (__FILE__)).'/inc/sidebar_manage.php');?>
    <div id="content">
        
<?php       
$filename = $_FILES['enrol_excel']['name'];
$filepath = $_FILES['enrol_excel']['tmp_name'];

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

$subject_arr = array();
$member_arr = array();

for ($i = 2; $i <= $maxRow; $i++) {
    $member = new Stdclass();
    $member->subid_sbb = trim($objWorksheet->getCell('A' . $i)->getValue());   //강의코드
    $member->fullname   = $objWorksheet->getCell('B' . $i)->getValue(); //이름
    $member->username   = trim(strtolower($objWorksheet->getCell('C' . $i)->getValue())); //학번
    $member->role_shortname   = trim($objWorksheet->getCell('D' . $i)->getValue()); //역할
    
    if(!empty($member->subid_sbb)) {
        if(preg_match('/^[A-Z0-9]*-[A-Z0-9]*-[A-Z0-9]*/i', $member->subid_sbb)) {
            $member_arr[$member->subid_sbb][] = $member;
            $subject_arr[$member->subid_sbb] = $member->subid_sbb;
        }
    }
    
}

// subid_sbb 값과 맞는 강의 목록
$sql_select = "SELECT co.*, lc.subject_id, lc.timeend, lc.timestart ";
$sql_from = " FROM {course} co
              JOIN {lmsdata_class} lc ON lc.course = co.id";
list($sql, $params) = $DB->get_in_or_equal($subject_arr, SQL_PARAMS_NAMED, 'subject_id');
$sql_in = ' WHERE lc.subject_id '. $sql.' and lc.year = :year ';
$sql_order = " ORDER BY lc.subject_id";

if($term == '00') {
    $sql_in .= ' and lc.term = :term ';
    $params['term'] = $term;
} else if($term == '10') {
    $sql_in .= ' and lc.term IN ( :term1, :term2, :term3 )';
    $params['term1'] = '10';
    $params['term2'] = '11';
    $params['term3'] = '12';
    
} else if($term == '20') {
    $sql_in .= ' and lc.term IN ( :term1, :term2, :term3 )';
    $params['term1'] = '20';
    $params['term2'] = '23';
    $params['term3'] = '24';
}

$params['year'] = $year;
$lmsdata_courses = $DB->get_records_sql($sql_select.$sql_from.$sql_in.$sql_order, $params);

$role_arr = $DB->get_records('role', null, '', 'shortname, id, name');

$exist_courses = array();
foreach($lmsdata_courses as $lcourse) {
    
    $exist_courses[$lcourse->subject_id] = $lcourse->subject_id; 
    $enrol = $DB->get_record('enrol', array('enrol'=>'manual', 'courseid'=>$lcourse->id));
    
    $manager = new course_enrolment_manager($PAGE, $lcourse);
    
    $instances = $manager->get_enrolment_instances();
    $instance = $instances[$enrol->id];
    
    $plugins = $manager->get_enrolment_plugins();
    $plugin = $plugins[$instance->enrol];
    
    $context = context_course::instance($lcourse->id, MUST_EXIST);
    
    $role_type = array('1'=>'editingteacher', '2'=>'editingteacher01', '3'=>'assistant', '4'=>'student');
    
    foreach($member_arr[$lcourse->subject_id] as $member) {
        if($user = $DB->get_record_sql(' SELECT id FROM {user} WHERE username =:username ', array('username'=>$member->username))) {
            $role_shortname = $role_type[$member->role_shortname];
            $roleid = $role_arr[$role_shortname]->id;
            if(!empty($roleid)) {
                if(!$DB->record_exists('role_assignments', array('contextid'=>$context->id, 'userid'=>$user->id, 'roleid'=>$roleid))) {
                    if($member->role_shortname != 'student') {
                        $startdate = 0;
                        $enddate   = 0;
                    } else {
                        $startdate = $lcourse->timestart - 60*60*24*2;
                        $enddate   = $lcourse->timeend;
                    }
                    $plugin->enrol_user($instance, $user->id, $roleid, $startdate, $enddate, null);
                    siteadmin_println($member->fullname.'(학번:'.$member->username.') 사용자를 강의(['.$lcourse->subject_id.']'.$lcourse->fullname.')에 등록('.$role_arr[$member->role_shortname]->name.') 완료하였습니다.'); 
                } else {
                    siteadmin_println($member->fullname.'(학번:'.$member->username.') 사용자가 '.$role_arr[$member->role_shortname]->name.'으로 이미 등록되어 있습니다.'); 
                } 
            }
        } else {
            siteadmin_println($member->fullname.'(학번:'.$member->username.') 사용자가 존재 하지 않습니다.'); 
        }
    }
}

$term_arr =lmsdata_get_terms();

foreach($member_arr as $subval => $subcheck) {
    if(!isset($exist_courses[$subval])){
        foreach($subcheck as $notstudent){
             siteadmin_println($year.'년도 '.$term_arr[$term].' 강의코드 ['.$notstudent->subid_sbb.'] 강의가 존재하지 않아 '.$notstudent->fullname.'(학번:'.$notstudent->username.') 등록에 실패했습니다.'); 
        }
    }
}

siteadmin_scroll_down();
?>
    <input type="button" class="blue_btn" style="float:right;margin-right: 10px;" value="<?php echo get_string('okay','local_lmsdata'); ?>" onclick="location.href='<?php echo $CFG->wwwroot.'/siteadmin/manage/enrol_basic_course.php'?>'"/>    
    </div><!--Content End-->
</div> <!--Contents End-->
<?php include_once ('../inc/footer.php');?>
