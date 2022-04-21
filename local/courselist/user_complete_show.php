<?php
require(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once $CFG->dirroot . '/ysadmin/lib.php';
require_once $CFG->dirroot . '/local/courselist/lib.php';
require_once $CFG->dirroot.'/local/haksa/config.php';

require_login();

$userid = required_param('id', PARAM_INT);     //userid

$context = context_system::instance();
$user = $DB->get_record('lmsdata_user', array('userid'=>$USER->id));
if(!is_siteadmin($USER) && ($user->usergroup != 'pr') && ($user->usergroup != 'sa')){
    redirect($CFG->wwwroot); 
}



$sql = " SELECT 
            ic.id
           ,ic.grade
           ,ic.department
           ,lc.timestart
           ,lc.timeend
           ,lc.kor_lec_name as coursename
           ,lc.subject_id
        FROM {course_irregular_complete} ic 
        JOIN {lmsdata_class} lc ON lc.course = ic.courseid
        WHERE ic.userid = :userid  order by department, lc.timestart asc";
$sql_param = array('userid' => $userid);

$user_complete_list = $DB->get_records_sql($sql, $sql_param);

$center_sum = 0;
$other_sum = 0;
$hakbu_sum = 0;

foreach($user_complete_list as $complete_course) {
    if($complete_course->department == SUNGKYUL_CENTER_TYPE) {
        $center_sum += $complete_course->grade;
    } else if($complete_course->department == SUNGKYUL_OTHERS_TYPE) {
        $other_sum += $complete_course->grade;
    } else if($complete_course->department == SUNGKYUL_HAKBU_TYPE) {
        $hakbu_sum += $complete_course->grade;
    }
}
$total_grade = $center_sum+$other_sum+$hakbu_sum;

$userinfo = $DB->get_record_sql('SELECT ur.id, ur.username, ur.firstname||ur.lastname as fullname, yu.major, ur.email  
                                 FROM {user} ur 
                                 JOIN {lmsdata_user} yu ON yu.userid = ur.id WHERE ur.id = :userid', array('userid'=>$userid));

$work_sum = 0;
// 이력서 클리닉
$sql_clinic = " SELECT pid, pername,  to_char(wdate, 'yyyy-mm-dd') as timecreated,  to_char(udate, 'yyyy-mm-dd') as timemodified FROM SKY_SMILE.V2RESUMECLINIC WHERE CSTATUS in('완료','심화') and pid = :pid order by timemodified asc";
$conn->beginTransaction();
$stmt_clinic = $conn->prepare($sql_clinic);
$stmt_clinic->bindValue("pid", $userinfo->username);
$stmt_clinic->execute();
while ($row = $stmt_clinic->fetch()) {
    $user_clinic = (object) array_change_key_case($row, CASE_LOWER);
    $user_clinic_arr[] = $user_clinic;
    $work_sum += 1;
}
$conn->commit();

// 진로개발시스템 이력서 등록
$sql_jinro = " SELECT pid, to_char(rdate, 'yyyy-mm-dd') as timecreated FROM SKY_SMILE.V2RESUME WHERE pid = :pid order by rdate asc";
$conn->beginTransaction();
$stmt_jinro = $conn->prepare($sql_jinro);
$stmt_jinro->bindValue("pid", $userinfo->username);
$stmt_jinro->execute();
while ($row = $stmt_jinro->fetch()) {
    $user_jinro = (object) array_change_key_case($row, CASE_LOWER);
    $user_jinro_arr[] = $user_jinro;
    $work_sum += 1;
}

$conn->commit();

// 취업지원과 프로그램
$sql_program = " SELECT pid, pername, title, to_char(esdate, 'yyyy-mm-dd') as timestart,  to_char(eedate, 'yyyy-mm-dd') as timeend FROM SKY_SMILE.V2PROGRAMREG WHERE  pid = :pid order by esdate asc";
$conn->beginTransaction();
$stmt_program = $conn->prepare($sql_program);
$stmt_program->bindValue("pid", $userinfo->username);
$stmt_program->execute();
while ($row = $stmt_program->fetch()) {
    $user_program = (object) array_change_key_case($row, CASE_LOWER);
    $user_program_arr[] = $user_program;
    $work_sum += 2;
}
$conn->commit();

// 취업지원과 상담
$sql_advice = " SELECT pid, pername,  to_char(pdate, 'yyyy-mm-dd') as timecreated FROM SKY_SMILE.V2RECCOUN WHERE pid = :pid order by pdate asc";
$conn->beginTransaction();
$stmt_advice = $conn->prepare($sql_advice);
$stmt_advice->bindValue("pid", $userinfo->username);
$stmt_advice->execute();
while ($row = $stmt_advice->fetch()) {
    $user_advice = (object) array_change_key_case($row, CASE_LOWER);
    $user_advice_arr[] = $user_advice;
    $work_sum += 1;
}
$conn->commit();

$total_grade += $work_sum;
$PAGE->set_context($context);
$PAGE->set_url('/local/courselist/user_complete_show.php', array('id'=>$userid));
$PAGE->set_pagelayout('standard');

$PAGE->requires->jquery();
$PAGE->requires->jquery_plugin('ui');
$PAGE->requires->jquery_plugin('ui-css');
$PAGE->requires->jquery_plugin('migrate');
$PAGE->requires->css('/ysadmin/css/loading.css');
$PAGE->requires->js('/ysadmin/js/loading.js');

$strplural = get_string("complete:history_detail", "local_courselist");
$PAGE->navbar->add(get_string("complete:history_list", "local_courselist"), new moodle_url($CFG->wwwroot.'/local/courselist/major_complete_list.php'));
$PAGE->navbar->add($strplural);
$PAGE->set_title($strplural);
$PAGE->set_heading($strplural);
echo $OUTPUT->header();


?>

<h2 class="head_participants">
    <?php echo $strplural; ?>
</h2>

<table class="detail">
    <tr>
        <td class="option"><?php echo get_string('user:info', 'local_courselist'); ?></td>
        <td class="value">
            <?php 
                echo '['.$userinfo->username.'] '.$userinfo->fullname.'</p>'; 
                echo $userinfo->major.' / '.$userinfo->email;
            ?>
        </td>
        <td class="option"><?php echo get_string('complete:grade_sum', 'local_courselist'); ?></td>
        <td class="value"><?php echo $total_grade; ?></td>
    </tr>
</table>

<div class="complete_type">
    <div class="type_name"><?php echo get_string('course:major', 'local_courselist');?></div>
    <div class="type_grade"><?php echo get_string('complete:grade_frm', 'local_courselist', $hakbu_sum);?></div>
</div>

<table class="course_syllabus_table" cellpadding="0" cellspacing="0">
    <thead>
        <tr>
            <th class="th_subjectid"><?php echo get_string('course:subjectid', 'local_courselist'); ?></th>
            <th><?php echo get_string('course:name', 'local_courselist'); ?></th>
            <th class="th_grade"><?php echo get_string('complete:grade', 'local_courselist'); ?></th>
            <th class="th_date"><?php echo get_string('course:date', 'local_courselist'); ?></th>
        </tr>
    </thead>
    <tbody>
        <?php
            $count = 0;
            foreach ($user_complete_list as $complete) {
                if($complete->department == SUNGKYUL_HAKBU_TYPE) {
                    $count++;
        ?>
            <tr>
                <td class="subject_id"><?php echo $complete->subject_id;?></td>
                <td class="coursename"><?php echo $complete->coursename;?> </td>
                <td class="grade"><?php echo $complete->grade;?> </td>
                <td class="date"><?php echo date('Y-m-d', $complete->timestart).' ~ '.date('Y-m-d', $complete->timeend);?> </td>
                      </tr>
        <?php
                }
                
            }
            
            if($count == 0) {
            ?>          
            <tr>
                <td class="empty" colspan="4"><?php echo get_string('complete:empty', 'local_courselist');?></td>
            </tr>
        
        <?php    
            }
        ?>
    </tbody>
</table>

<div class="complete_type">
    <div class="type_name"><?php echo get_string('course:ctl', 'local_courselist');?></div>
    <div class="type_grade"><?php echo get_string('complete:grade_frm', 'local_courselist', $center_sum); ?></div>
</div>

<table class="course_syllabus_table" cellpadding="0" cellspacing="0">
    <thead>
        <tr>
            <th class="th_subjectid"><?php echo get_string('course:subjectid', 'local_courselist'); ?></th>
            <th><?php echo get_string('course:name', 'local_courselist'); ?></th>
            <th class="th_grade"><?php echo get_string('complete:grade', 'local_courselist'); ?></th>
            <th class="th_date"><?php echo get_string('course:date', 'local_courselist'); ?></th>
        </tr>
    </thead>
    <tbody>
        <?php
            $count = 0;
            foreach ($user_complete_list as $complete) {
                if($complete->department == SUNGKYUL_CENTER_TYPE) {
                    $count++;
        ?>
            <tr>
                <td class="subject_id"><?php echo $complete->subject_id;?></td>
                <td class="coursename"><?php echo $complete->coursename;?> </td>
                <td class="grade"><?php echo $complete->grade;?> </td>
                <td class="date"><?php echo date('Y-m-d', $complete->timestart).' ~ '.date('Y-m-d', $complete->timeend);?> </td>
           </tr>
        <?php
                }
                
            }
            
            if($count == 0) {
            ?>          
            <tr>
                <td class="empty" colspan="4"><?php echo get_string('complete:empty', 'local_courselist');?></td>
            </tr>
        
        <?php    
            }
        ?>
    </tbody>
</table>

<div class="complete_type">
    <div class="type_name"><?php echo get_string('employment_support', 'local_courselist');?></div>
    <div class="type_grade"><?php echo get_string('complete:grade_frm', 'local_courselist', $work_sum); ?></div>
</div>

<table class="course_syllabus_table" cellpadding="0" cellspacing="0">
    <thead>
        <tr>
            <th class="th_subjectid"><?php echo get_string('course:subjectid', 'local_courselist'); ?></th>
            <th><?php echo get_string('course:name', 'local_courselist'); ?></th>
            <th class="th_grade"><?php echo get_string('complete:grade', 'local_courselist'); ?></th>
            <th class="th_date"><?php echo get_string('course:date', 'local_courselist'); ?></th>
        </tr>
    </thead>
    <tbody>
        <?php
            if(empty($user_clinic_arr) && empty($user_jinro_arr) && empty($user_program_arr) && empty($user_advice_arr) ) {
            
        ?>
            <tr>
                <td class="empty" colspan="4"><?php echo get_string('complete:empty', 'local_courselist');?></td>
            </tr>
        <?php    
            } else {
                foreach($user_clinic_arr as $clinic) {
        ?>
            <tr>
                <td class="subject_id">-</td>
                <td class="coursename">이력서 클리닉</td>
                <td class="grade">1</td>
                <td class="date"><?php echo $clinic->timecreated;?> </td>
            </tr>
        <?php    
                }
                foreach($user_jinro_arr as $jinro) {
        ?>
            <tr>
                <td class="subject_id">-</td>
                <td class="coursename">진로개발시스템 이력서 등록</td>
                <td class="grade">1</td>
                <td class="date"><?php echo $jinro->timecreated;?> </td>
            </tr>
        <?php    
                }
                foreach($user_program_arr as $program) {
        ?>
            <tr>
                <td class="subject_id">-</td>
                <td class="coursename"><?php echo $program->title;?></td>
                <td class="grade">2</td>
                <td class="date"><?php echo $program->timestart.' ~ '.$program->timeend;?> </td>
            </tr>
        <?php    
                }
                foreach($user_advice_arr as $advice) {
        ?>
            <tr>
                <td class="subject_id">-</td>
                <td class="coursename">취업지원과 상담</td>
                <td class="grade">1</td>
                <td class="date"><?php echo $advice->timecreated;?> </td>
            </tr>
        <?php    
                }
        } ?>
            
    </tbody>
</table>

<div class="complete_type">
    <div class="type_name"><?php echo get_string('course:other', 'local_courselist');?></div>
    <div class="type_grade"><?php echo get_string('complete:grade_frm', 'local_courselist', $other_sum);?></div>
</div>

<table class="course_syllabus_table" cellpadding="0" cellspacing="0">
    <thead>
        <tr>
            <th class="th_subjectid"><?php echo get_string('course:subjectid', 'local_courselist'); ?></th>
            <th><?php echo get_string('course:name', 'local_courselist'); ?></th>
            <th class="th_grade"><?php echo get_string('complete:grade', 'local_courselist'); ?></th>
            <th class="th_date"><?php echo get_string('course:date', 'local_courselist'); ?></th>
        </tr>
    </thead>
    <tbody>
        <?php
            $count = 0;
            foreach ($user_complete_list as $complete) {
                if($complete->department == SUNGKYUL_OTHERS_TYPE) {
                    $count++;
        ?>
            <tr>
                <td class="subject_id"><?php echo $complete->subject_id;?></td>
                <td class="coursename"><?php echo $complete->coursename;?> </td>
                <td class="grade"><?php echo $complete->grade;?> </td>
                <td class="date"><?php echo date('Y-m-d', $complete->timestart).' ~ '.date('Y-m-d', $complete->timeend);?> </td>
           </tr>
        <?php
                }
                
            }
            
            if($count == 0) {
            ?>          
            <tr>
                <td class="empty" colspan="4"><?php echo get_string('complete:empty', 'local_courselist');?></td>
            </tr>
        
        <?php    
            }
        ?>
    </tbody>
</table>

<div class="table-footer-area">
    <input type="button" class="red-form" value="<?php echo get_string('print', 'local_courselist'); ?>" onclick="javascript:window.print()"/>
    <input type="button" class="red-form" value="<?php echo get_string('return', 'local_courselist'); ?>" onclick="javascript:history.back()"/>
</div>
<?php            
    echo $OUTPUT->footer();
?>