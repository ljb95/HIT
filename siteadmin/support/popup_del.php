<?php
require(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once(dirname(dirname(__FILE__)) . '/lib.php');

$ids = required_param_array('id', PARAM_INT);

// Check for valid admin user - no guest autologin 
require_login(0, false);
if (isguestuser()) {
    $SESSION->wantsurl = (string)new moodle_url('/siteadmin/manage/course_list.php');
    redirect(get_login_url());
}
$context = context_system::instance();
require_capability('moodle/site:config', $context);

foreach($ids as $id) {
    if($popup = $DB->get_record('popup', array('id'=>$id))) {
        $DB->delete_records('popup', array('id'=>$id));
    }
}

redirect(new moodle_url('/siteadmin/support/popup.php'));