<?php
require(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once($CFG->dirroot.'/calendar/lib.php');
$context = context_system::instance();

require_login();

$PAGE->set_context($context);
$PAGE->set_pagelayout('standard');
$PAGE->set_title('강좌별 성적 조회');
$PAGE->navbar->add("강좌별 총 성적");
$PAGE->set_url(new moodle_url('/local/mypage/gradeview.php'));

$sql = 'select c.id as cid,c.fullname from {course} c 
                        join {lmsdata_class} lc on lc.course = c.id
                        join {context} ct on ct.contextlevel = 50 and ct.instanceid = c.id 
                        join {role_assignments} ra on ra.contextid = ct.id and  ra.userid = :userid ';
$courses_my = $DB->get_records_sql($sql, array('userid'=>$USER->id));
echo $OUTPUT->header();
?>
<h2>강좌별 성적 조회</h2>
<?php 
foreach($courses_my as $courses){
    
?>
<h5><?php echo $courses->fullname;?></h5>
    <table class="table table-condensed generaltable">
    <thead>
        <tr>
        <th>학습활동</th>
        <th>이름</th>
        <th>점수</th>
        </tr>
    </thead>
    <tbody>
        <?php 
            $sql2 = "select gg.id , gg.* , gi.* from {course} c
join {lmsdata_class} lc on c.id = lc.course
join {grade_items} gi on lc.id = gi.courseid
join {grade_grades} gg on gi.id = gg.itemid  where c.id =:courseid  and gg.userid =:userid and gi.itemname is not null";
            $grades = $DB->get_records_sql($sql2,array('courseid'=>$courses->cid, 'userid'=>$USER->id));
        foreach($grades as $grade){
            
            ?>
        <tr>
            <td><?php echo $grade->itemmodule; ?></td>
            <td><?php echo $grade->itemname; ?></td>
            <td><?php echo $grade->finalgrade / $grade->grademax; ?></td>
        </tr>
        
        <?php 
        }
        
        if(!$grades){
            ?>
        <tr align="center"><td colspan="3">조회된 성적이 없습니다.</td></tr>
        <?php } ?>
        
    </tbody>
</table>
<br>
<br>
<br>
    <?php 
}

echo $OUTPUT->footer();