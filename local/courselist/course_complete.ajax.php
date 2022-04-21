<?php

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once $CFG->dirroot . '/local/courselist/lib.php';

$id   = required_param('id', PARAM_INT);
$complete   = required_param('complete', PARAM_BOOL);
$user_list  = required_param_array('user_list', PARAM_INT);

if($complete) {
    $complete_int = 1;
} else {
    $complete_int = 0;
}

$users = $DB->get_records_sql('SELECT id,userid from {course_irregular_complete} WHERE courseid =:courseid ', array('courseid' => $id));

$in_userlist = array();
foreach($users as $value) {
    $in_userlist[$value->userid] = $value->id;
}

$count = 0;
foreach($user_list as $userid) {
    if(array_key_exists($userid, $in_userlist)) {
        $complete_user = new stdClass();
        $complete_user->id = $in_userlist[$userid];
        $complete_user->complete = $complete;
        $complete_user->timemodified = time();
        $DB->update_record('course_irregular_complete', $complete_user);
    } else {
        $complete_user = new stdClass();
        $complete_user->id = $in_userlist[$userid];
        $complete_user->courseid = $id;
        $complete_user->userid = $userid;
        $complete_user->complete = $complete;
        $complete_user->timecreated = time();
        $complete_user->timemodified = time();
        $complete_user->grade = 0;
        $complete_user->department = '';
        $DB->insert_record('course_irregular_complete', $complete_user);
    }
    $count++;
}

echo $count++;
