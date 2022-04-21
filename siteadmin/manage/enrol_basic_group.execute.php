<?php 
require(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once($CFG->dirroot.'/siteadmin/lib.php');
require_once($CFG->dirroot.'/group/lib.php');
require_once($CFG->libdir.'/formslib.php');
require_once($CFG->libdir.'/phpexcel/PHPExcel.php');
require_once($CFG->libdir.'/phpexcel/PHPExcel/IOFactory.php');

global $DB, $PAGE;

$courseid = required_param('course', PARAM_INT);

$context = context_course::instance($courseid);
$course = $DB->get_record('course', array('id'=>$courseid));
$editoroptions = array('maxfiles'=>EDITOR_UNLIMITED_FILES, 'maxbytes'=>$course->maxbytes, 'trust'=>true, 'context'=>$context, 'noclean'=>true);

$year = get_config('moodle', 'haxa_year');
include_once (dirname(dirname (__FILE__)).'/inc/header.php'); 

?>

<div id="contents">
    <?php include_once  (dirname(dirname (__FILE__)).'/inc/sidebar_manage.php');?>
    <div id="content">
        
<?php       
$filename = $_FILES['group_excel']['name'];
$filepath = $_FILES['group_excel']['tmp_name'];

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
$student_arr = array();

for ($i = 2; $i <= $maxRow; $i++) {
    $student = new Stdclass();
    $student->groupnum = $objWorksheet->getCell('A' . $i)->getValue();   //조
    $student->fullname   = $objWorksheet->getCell('B' . $i)->getValue(); //이름
    $student->username   = $objWorksheet->getCell('C' . $i)->getValue(); //학번
    
    if(!empty($student->groupnum)) {
        $group_arr[$student->groupnum][] = $student;
    }
    
}


$member_arr = array();
$group_num_string = '조별 등록 사용자 수 : ';

foreach($group_arr as $num =>$group_members) {
    $create_group = new stdClass();
    $create_group->name = $num.'조';
    $create_group->idnumber = ' ';
    $create_group->courseid = $course->id;
    $create_group = file_prepare_standard_editor($create_group, 'description', $editoroptions, $context, 'group', 'description', null);
    $groupid = groups_create_group($create_group);
    
    $group_num = 0;
    foreach($group_members as $student) {
        if($userid = $DB->get_field_sql(' SELECT id FROM {user} WHERE username = :username ', array('username'=>$student->username))) {
            if(!isset($member_arr[$userid])) {
                groups_add_member($groupid, $userid);
                $member_arr[$userid] = $student->groupnum;
                siteadmin_println($student->fullname.'(학번:'.$student->username.') 사용자를 '.$student->groupnum.'조에 편성하였습니다.'); 
                $group_num++;
            } else {
                siteadmin_println($student->fullname.'(학번:'.$student->username.') 사용자는 이미 '.$member_arr[$userid].'조에 편성 되었습니다.'); 
            }
        } else {
            siteadmin_println($student->fullname.'(학번:'.$student->username.') 사용자가 존재 하지 않습니다.'); 
        }
    }
    
    $group_num_string .= $create_group->name.'('.$group_num.'명) ';
}
siteadmin_println($group_num_string);

//siteadmin_scroll_down();
?>
    <input type="button" class="blue_btn" style="float:right;margin-right: 10px;" value="<?php echo get_string('okay','local_lmsdata'); ?>" onclick="location.href='<?php echo $CFG->wwwroot.'/siteadmin/manage/enrol_basic_group.php?course='.$courseid?>'"/>    
    </div><!--Content End-->
</div> <!--Contents End-->
<?php include_once ('../inc/footer.php');?>