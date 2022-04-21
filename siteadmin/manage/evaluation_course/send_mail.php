<?php

require_once dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php';
require_once dirname(dirname(dirname(__FILE__))) . '/lib/paging.php';
require_once dirname(dirname(dirname(__FILE__))) . '/lib.php';

// Check for valid admin user - no guest autologin
require_login(0, false);
if (isguestuser()) {
    $SESSION->wantsurl = (string) new moodle_url('/siteadmin/manage/evaluation_course/evaluation_list.php');
    redirect(get_login_url());
}
$context = context_system::instance();
require_capability('moodle/site:config', $context);

$id = optional_param('id', 0, PARAM_INT);
$profid = optional_param('profid', 0, PARAM_INT);

// 현재 년도, 학기

$page_params = array();
$params = array(
    'contextlevel' => CONTEXT_COURSE
);

$js = array(
    $CFG->wwwroot . '/siteadmin/manage/course_list.js'
);
$evaluation = $DB->get_record('lmsdata_evaluation', array('id' => $id));
$userfrom = $DB->get_record('user', array('id' => $USER->id));

$cnt = 1;
switch ($evaluation->targets) {
    case '1':
        $sql = "select u.* ,c.fullname 
                        from {course} c 
                        join {context} ct on ct.contextlevel = 50 and ct.instanceid = c.id 
                        join {role_assignments} ra on ra.contextid = ct.id 
                        join {user} u on u.id = ra.userid  
                        join {role} r on r.id = ra.roleid and r.shortname = 'student' 
                        where c.id = :courseid and (select id from {lmsdata_evaluation_submits} where userid = u.id and evaluation = :evaluation) is null";
        $users = $DB->get_records_sql($sql, array('courseid' => $evaluation->course, 'evaluation' => $evaluation->id));
        break;
    case '2':
        $sql = "select u.* ,c.fullname 
                        from {course} c 
                        join {context} ct on ct.contextlevel = 50 and ct.instanceid = c.id 
                        join {role_assignments} ra on ra.contextid = ct.id 
                        join {user} u on u.id = ra.userid  
                        join {role} r on r.id = ra.roleid and r.shortname = 'student' 
                        where c.id = :courseid and (select id from {lmsdata_evaluation_submits} where userid = u.id and prof_userid = :profid and evaluation = :evaluation) is null";
        $users = $DB->get_records_sql($sql, array('courseid' => $evaluation->course, 'evaluation' => $evaluation->id, 'profid' => $profid));
        break;
    case 'p1':
        $sql = "select u.* ,c.fullname 
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
        $users = $DB->get_records_sql($sql, array('courseid' => $evaluation->course, 'evaluation' => $evaluation->id, 'profid' => $profid, 'endmonth' => date('m'), 'endday' => date('d')));
        break;
}

foreach ($users as $user) {
    $postsubject = $user->fullname . ' ' . get_string('mailsubject');
    $posttext = get_string('mailsubject').'
                
링크 : ' . $CFG->wwwroot . '/local/evaluation/course_evaluation.php?id='.$evaluation->course.'&evaluation=' . $evaluation->id.'&profid='.$profid;
    $posthtml = '';
    $eventdata = new object();
    $eventdata->component = 'moodle';    // the component sending the message. Along with name this must exist in the table message_providers
    $eventdata->name = 'instantmessage';        // type of message from that module (as module defines it). Along with component this must exist in the table message_providers
    $eventdata->userfrom = $userfrom;      // user object
    $eventdata->userto = $user;        // user object
    $eventdata->subject = $postsubject;   // very short one-line subject
    $eventdata->fullmessage = $posttext;      // raw text
    $eventdata->fullmessageformat = FORMAT_PLAIN;   // text format
    $eventdata->fullmessagehtml = $posthtml;      // html rendered version
    $eventdata->smallmessage = $postsubject;             // useful for plugins like sms or twitter
    $eventdata->notification = 1;

    message_send($eventdata);
    
    siteadmin_println(fullname($user).get_string('mailto','local_lmsdata').' <br>');
}
?>
<input type="button" value="<?php echo get_string('wait_complete','local_lmsdata'); ?>" onclick="location.href='<?php echo $CFG->wwwroot; ?>/siteadmin/manage/evaluation_course/view_users.php?id=<?php echo $id; ?>&profid=<?php echo $profid; ?>'" />