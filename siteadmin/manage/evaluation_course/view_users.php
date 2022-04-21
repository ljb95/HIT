<?php 
require_once dirname(dirname(dirname (dirname (__FILE__)))).'/config.php';
require_once dirname(dirname(dirname (__FILE__))).'/lib/paging.php';
require_once dirname(dirname (dirname (__FILE__))).'/lib.php';

// Check for valid admin user - no guest autologin
require_login(0, false);
if (isguestuser()) {
    $SESSION->wantsurl = (string)new moodle_url('/siteadmin/manage/evaluation_course/evaluation_list.php');
    redirect(get_login_url());
}
$context = context_system::instance();
require_capability('moodle/site:config', $context);

$id     = optional_param('id', 0, PARAM_INT);
$profid = optional_param('profid', 0, PARAM_INT);

// 현재 년도, 학기

$page_params = array();
$params = array(
    'contextlevel'=>CONTEXT_COURSE
);

$js = array(
    $CFG->wwwroot.'/siteadmin/manage/course_list.js'
);
$evaluation = $DB->get_record('lmsdata_evaluation',array('id'=>$id));

?>

<?php include_once (dirname(dirname(dirname(__FILE__))).'/inc/header.php'); ?>
<div id="contents">
    <?php include_once  (dirname(dirname(dirname(__FILE__))).'/inc/sidebar_manage.php');?>
    
    <div id="content">
        <h3 class="page_title"><?php echo get_string('lectureevaluation', 'local_lmsdata'); ?></h3>
        <div class="page_navbar"><a href="../category_list.php"><?php echo get_string('course_management', 'local_lmsdata'); ?></a> > <a href="../evaluation/evaluation_form.php"><?php echo get_string('evalandsur','local_lmsdata'); ?></a> > <?php echo get_string('lectureevaluation', 'local_lmsdata'); ?></div> 

        <table>
            <tr>
                <th><?php echo get_string('number', 'local_lmsdata'); ?></th>
                <th><?php echo get_string('name','local_lmsdata'); ?></th> 
                <th><?php echo get_string('student_number','local_lmsdata'); ?></th>
            </tr>
            <?php
                $cnt = 1;
                switch($evaluation->targets){
                    case '1':
                        $sql = "select u.* 
                        from {course} c 
                        join {context} ct on ct.contextlevel = 50 and ct.instanceid = c.id 
                        join {role_assignments} ra on ra.contextid = ct.id 
                        join {user} u on u.id = ra.userid  
                        join {role} r on r.id = ra.roleid and r.shortname = 'student' 
                        where c.id = :courseid and (select id from {lmsdata_evaluation_submits} where userid = u.id and evaluation = :evaluation) is null";
                        $users = $DB->get_records_sql($sql, array('courseid' => $evaluation->course,'evaluation'=>$evaluation->id));
                        foreach($users as $user){
             ?>
                <tr>
                    <td><?php echo $cnt++; ?></td>
                    <td><?php echo fullname($user); ?></td>
                    <td><?php echo $user->username; ?></td>
                </tr>
            <?php
                        }
                        break;
                    case '2':
                        $sql = "select u.* 
                        from {course} c 
                        join {context} ct on ct.contextlevel = 50 and ct.instanceid = c.id 
                        join {role_assignments} ra on ra.contextid = ct.id 
                        join {user} u on u.id = ra.userid  
                        join {role} r on r.id = ra.roleid and r.shortname = 'student' 
                        where c.id = :courseid and (select id from {lmsdata_evaluation_submits} where userid = u.id and prof_userid = :profid and evaluation = :evaluation) is null";
                        $users = $DB->get_records_sql($sql, array('courseid' => $evaluation->course,'evaluation'=>$evaluation->id,'profid'=>$profid));
                        foreach($users as $user){
            ?>
                <tr>
                    <td><?php echo $cnt++; ?></td>
                    <td><?php echo fullname($user); ?></td>
                    <td><?php echo $user->username; ?></td>
                </tr>
            <?php
                        }
                        break;
                    case 'p1':
                        $sql = "select u.* 
                        from {course} c 
                        join {context} ct on ct.contextlevel = 50 and ct.instanceid = c.id 
                        join {role_assignments} ra on ra.contextid = ct.id 
                        join {user} u on u.id = ra.userid  
                        join {lmsdata_user} lu on lu.userid = u.id  
                        join {lmsdata_group_member} gm on gm.userid = u.id
                        join {lmsdata_group} g on g.id = gm.groupid 
                        join {lmsdata_group_schedule} gs on gs.groupid = g.id and gs.course = c.id
                        join {lmsdata_timetable_training} tp on tp.hakyear = lu.hakyear and tp.period = gs.period and tp.year = gs.year and tp.endmonth <= :endmonth  and tp.endday <= :endday
                        join {role} r on r.id = ra.roleid and r.shortname = 'student' 
                        where c.id = :courseid and (select id from {lmsdata_evaluation_submits} where userid = u.id and prof_userid = :profid and evaluation = :evaluation) is null";
                         $users = $DB->get_records_sql($sql, array('courseid' => $evaluation->course,'evaluation'=>$evaluation->id,'profid'=>$profid,'endmonth'=>date('m'),'endday'=>date('d')));
                        foreach($users as $user){
            ?>
                 <tr>
                    <td><?php echo $cnt++; ?></td>
                    <td><?php echo fullname($user); ?></td>
                    <td><?php echo $user->username; ?></td>
                </tr>
            <?php
                        }
                        break;
                }
            ?>
        </table>
        
        <div id="btn_area">
            <div style="float:right;">
                <input type="button" class="blue_btn" style="margin-right: 10px;" value="<?php echo get_string('list','local_lmsdata'); ?>" onclick="location.href='evaluation_list.php'"/> 
                <input type="button" class="blue_btn" style="margin-right: 10px;" value="<?php echo get_string('sendmail','local_lmsdata'); ?>" onclick="if(confirm('<?php echo get_string('confirm3','local_lmsdata'); ?>')){ location.href='send_mail.php?id=<?php echo $id;?>&profid=<?php echo $profid; ?>' }"/> 
            </div>
        </div>
            
    </div><!--Content End-->
    
</div> <!--Contents End-->

 <?php include_once ('../../inc/footer.php');?>
