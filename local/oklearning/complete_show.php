<?php
require(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once $CFG->dirroot . '/siteadmin/lib.php';
require_once $CFG->dirroot . '/local/oklearning/lib.php';
require_once $CFG->dirroot.'/local/haksa/config.php';

require_login();

$context = context_system::instance();
$user = $DB->get_record('lmsdata_user', array('userid'=>$USER->id));

$userid = $user->userid;

$sql = " SELECT 
            ic.id
           ,ic.grade
           ,ic.department
           ,lc.timestart
           ,lc.timeend
           ,lc.kor_lec_name as coursename
           ,lc.subject_id
           ,lc.course
           ,lc.certificate
           ,qc.qcount
           ,qs.scount
        FROM {course_irregular_complete} ic 
        JOIN {lmsdata_class} lc ON lc.course = ic.courseid
        LEFT JOIN (
           select course,count(course) as qcount from {questionnaire}  group by course
            )qc ON qc.course = lc.course
        LEFT JOIN (
           select que.course, count(que.course) as scount from {questionnaire} que
           join (
                select survey_id 
                from {questionnaire_response} where username = :userid1 and complete = :complete group by survey_id 
              ) qr ON que.id = qr.survey_id
           group by que.course
        ) qs ON qs.course = lc.course
        WHERE ic.userid = :userid2  order by department, lc.timestart asc";

$sql_param = array(
                'userid1' => $userid,
                'userid2' => $userid,
                'complete' => 'y'
            );

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

$total_grade += $work_sum;
        
$PAGE->set_context($context);
$PAGE->set_url('/local/oklearning/complete_show.php');
$PAGE->set_pagelayout('standard');

$PAGE->requires->css('/local/oklearning/style.css');
$PAGE->requires->jquery();
$PAGE->requires->jquery_plugin('ui');
$PAGE->requires->jquery_plugin('ui-css');
$PAGE->requires->jquery_plugin('migrate');
$PAGE->requires->css('/siteadmin/css/loading.css');
$PAGE->requires->js('/siteadmin/js/loading.js');

$strplural = get_string('course:completion', 'local_oklearning');
$PAGE->navbar->add(get_string("course:manage", "local_oklearning"), new moodle_url($CFG->wwwroot.'/local/oklearning/course_manage.php'));
$PAGE->navbar->add($strplural);
$PAGE->set_title($strplural);
$PAGE->set_heading(get_string("course:manage", "local_oklearning"));
echo $OUTPUT->header();

//tab
$tabmenu =  trim(basename($_SERVER['PHP_SELF']), '.php');
if ($tabmenu === 'course_manage') {
    $currenttab = 'manage';
} else if($tabmenu === 'complete_show'){
    $currenttab = 'completion';
} else if($tabmenu === 'course_list_drive'){
    $currenttab = 'list_drive';
} else if($tabmenu === 'course_list_restore'){
    $currenttab = 'list_restore';
}
if(is_siteadmin() || $user->usergroup == 'pr' || $user->usergroup == 'sa') {
    
$rows = array (
    new tabobject('manage', "$CFG->wwwroot/local/oklearning/course_manage.php", get_string('course:list', 'local_oklearning')),
    new tabobject('completion', "$CFG->wwwroot/local/oklearning/complete_show.php", get_string('course:completion', 'local_oklearning')),
    new tabobject('list_drive', "$CFG->wwwroot/local/oklearning/course_list_drive.php", get_string('course:classes_drive_log', 'local_oklearning')),
    new tabobject('list_restore', "$CFG->wwwroot/local/oklearning/course_list_restore.php", get_string('course:classes_restore_log', 'local_oklearning'))
    );
print_tabs(array($rows), $currenttab);
}

?>

<div class="user_info">
    <div class="option_grade"><p><?php echo get_string('complete:grade_sum', 'local_oklearning').' : '.$total_grade; ?></p></div>
</div>

<div class="complete_type">
    <div class="type_name"><?php echo get_string('course:major', 'local_oklearning');?></div>
    <div class="type_grade"><?php echo get_string('complete:grade_frm', 'local_oklearning', $hakbu_sum);?></div>
</div>

<table class="generaltable course_syllabus_table" cellpadding="0" cellspacing="0">
    <thead>
        <tr>
            <th class="th_subjectid"><?php echo get_string('course:subjectid', 'local_oklearning'); ?></th>
            <th><?php echo get_string('course:name', 'local_oklearning'); ?></th>
            <th class="th_grade"><?php echo get_string('complete:grade', 'local_oklearning'); ?></th>
            <th class="th_date"><?php echo get_string('course:date', 'local_oklearning'); ?></th>
            <th class="th_certificate"><?php echo get_string('complete:certificate', 'local_oklearning'); ?></th>
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
                <td class="certificate">
                    <?php if($complete->certificate){ 
                            if($complete->qcount <=  $complete->scount){
                    ?>
                        <input type="button" class="red-form" value="<?php echo get_string('issue', 'local_oklearning'); ?>" onclick="javascript:location.href = '<?php echo $CFG->wwwroot."/local/oklearning/certificate_pdf.php?id=".$complete->course;?>'"/>
                    <?php
                            } else {
                    ?>
                        <input type="button" class="red-form" value="<?php echo get_string('questionnaire', 'local_oklearning'); ?>" onclick="javascript:location.href = '<?php echo $CFG->wwwroot."/course/view.php?id=".$complete->course;?>'"/>
                    <?php
                            }
                    ?>
                    <?php } ?>
                </td>
            </tr>
        <?php
                }
                
            }
            
            if($count == 0) {
            ?>          
            <tr>
                <td class="empty" colspan="5"><?php echo get_string('complete:empty', 'local_oklearning');?></td>
            </tr>
        
        <?php    
            }
        ?>
    </tbody>
</table>

<div class="complete_type">
    <div class="type_name"><?php echo get_string('course:ctl', 'local_oklearning');?></div>
    <div class="type_grade"><?php echo get_string('complete:grade_frm', 'local_oklearning', $center_sum); ?></div>
</div>

<table class="generaltable course_syllabus_table" cellpadding="0" cellspacing="0">
    <thead>
        <tr>
            <th class="th_subjectid"><?php echo get_string('course:subjectid', 'local_oklearning'); ?></th>
            <th><?php echo get_string('course:name', 'local_oklearning'); ?></th>
            <th class="th_grade"><?php echo get_string('complete:grade', 'local_oklearning'); ?></th>
            <th class="th_date"><?php echo get_string('course:date', 'local_oklearning'); ?></th>
            <th class="th_certificate"><?php echo get_string('complete:certificate', 'local_oklearning'); ?></th>
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
                <td class="date"><?php echo date('Y-m-d', $complete->timestart).' ~ '.date('Y-m-d', $complete->timeend);?></td>
                <td class="certificate">
                    <?php if($complete->certificate){ ?>
                        <input type="button" class="red-form" value="<?php echo get_string('issue', 'local_oklearning'); ?>" onclick="javascript:location.href = '<?php echo $CFG->wwwroot."/local/oklearning/certificate_pdf.php?id=".$complete->course;?>'"/>
                    <?php } ?>
                </td>
            </tr>
        <?php
                }
                
            }
            
            if($count == 0) {
            ?>          
            <tr>
                <td class="empty" colspan="5"><?php echo get_string('complete:empty', 'local_oklearning');?></td>
            </tr>
        
        <?php    
            }
        ?>
    </tbody>
</table>

<div class="complete_type">
    <div class="type_name"><?php echo get_string('employment_support', 'local_oklearning');?></div>
    <div class="type_grade"><?php echo get_string('complete:grade_frm', 'local_oklearning', $work_sum); ?></div>
</div>

<table class="generaltable course_syllabus_table" cellpadding="0" cellspacing="0">
    <thead>
        <tr>
            <th class="th_subjectid"><?php echo get_string('course:subjectid', 'local_oklearning'); ?></th>
            <th><?php echo get_string('course:name', 'local_oklearning'); ?></th>
            <th class="th_grade"><?php echo get_string('complete:grade', 'local_oklearning'); ?></th>
            <th class="th_date"><?php echo get_string('course:date', 'local_oklearning'); ?></th>
            <th class="th_certificate"><?php echo get_string('complete:certificate', 'local_oklearning'); ?></th>
        </tr>
    </thead>
    <tbody>
        <?php
            if(empty($user_clinic_arr) && empty($user_jinro_arr) && empty($user_program_arr) && empty($user_advice_arr) ) {
            
        ?>
            <tr>
                <td class="empty" colspan="5"><?php echo get_string('complete:empty', 'local_oklearning');?></td>
            </tr>
        <?php    
            } else {
                foreach($user_clinic_arr as $clinic) {
        ?>
            <tr>
                <td class="subject_id">-</td>
                <td class="coursename"><?php echo get_string('resumeclinic', 'local_oklearning');?></td>
                <td class="grade">1</td>
                <td class="date"><?php echo $clinic->timecreated;?></td>
                <td class="certificate">-</td>
            </tr>
        <?php    
                }
                foreach($user_jinro_arr as $jinro) {
        ?>
            <tr>
                <td class="subject_id">-</td>
                <td class="coursename"><?php echo get_string('registrationresume', 'local_oklearning');?></td>
                <td class="grade">1</td>
                <td class="date"><?php echo $jinro->timecreated;?></td>
                <td class="certificate">-</td>
            </tr>
        <?php    
                }
                foreach($user_program_arr as $program) {
        ?>
            <tr>
                <td class="subject_id">-</td>
                <td class="coursename"><?php echo $program->title;?></td>
                <td class="grade">2</td>
                <td class="date"><?php echo $program->timestart.' ~ '.$program->timeend;?></td>
                <td class="certificate">-</td>
            </tr>
        <?php    
                }
                foreach($user_advice_arr as $advice) {
        ?>
            <tr>
                <td class="subject_id">-</td>
                <td class="coursename"><?php echo get_string('supportconsultation', 'local_oklearning');?></td>
                <td class="grade">1</td>
                <td class="date"><?php echo $advice->timecreated;?></td>
                <td class="certificate">-</td>
            </tr>
        <?php    
                }
        } ?>
            
    </tbody>
</table>

<div class="complete_type">
    <div class="type_name"><?php echo get_string('course:other', 'local_oklearning');?></div>
    <div class="type_grade"><?php echo get_string('complete:grade_frm', 'local_oklearning', $other_sum);?></div>
    <div class="complete_help"><?php echo get_string('employment', 'local_oklearning');?></div>
</div>

<table class="generaltable course_syllabus_table" cellpadding="0" cellspacing="0">
    <thead>
        <tr>
            <th class="th_subjectid"><?php echo get_string('course:subjectid', 'local_oklearning'); ?></th>
            <th><?php echo get_string('course:name', 'local_oklearning'); ?></th>
            <th class="th_grade"><?php echo get_string('complete:grade', 'local_oklearning'); ?></th>
            <th class="th_date"><?php echo get_string('course:date', 'local_oklearning'); ?></th>
            <th class="th_certificate"><?php echo get_string('complete:certificate', 'local_oklearning'); ?></th>
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
                <td class="date"><?php echo date('Y-m-d', $complete->timestart).' ~ '.date('Y-m-d', $complete->timeend);?></td>
                <td class="certificate">
                    <?php if($complete->certificate){ ?>
                        <input type="button" class="red-form" value="<?php echo get_string('issue', 'local_oklearning'); ?>" onclick="javascript:location.href = '<?php echo $CFG->wwwroot."/local/oklearning/certificate_pdf.php?id=".$complete->course;?>'"/>
                    <?php } ?>
                </td>
           </tr>
        <?php
                }
                
            }
            
            if($count == 0) {
            ?>          
            <tr>
                <td class="empty" colspan="5"><?php echo get_string('complete:empty', 'local_oklearning');?></td>
            </tr>
        
        <?php    
            }
        ?>
    </tbody>
</table>

<div class="table-footer-area">
    <input type="button" class="red-form" value="<?php echo get_string('print', 'local_oklearning'); ?>" onclick="javascript:window.print()"/>
    <!--<input type="button" class="red-form" value="<?php echo get_string('return', 'local_oklearning'); ?>" onclick="javascript:location.href = '<?php echo $CFG->wwwroot;?>'"/>-->
</div>
<?php            
    echo $OUTPUT->footer();
?>