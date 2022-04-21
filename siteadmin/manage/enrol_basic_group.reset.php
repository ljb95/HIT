<?php
require_once dirname(dirname(dirname (__FILE__))).'/config.php';
require_once dirname(dirname (__FILE__)).'/lib/paging.php';
require_once dirname(dirname (__FILE__)).'/lib.php';
require_once($CFG->dirroot.'/group/lib.php');

// Check for valid admin user - no guest autologin
$courseid   = optional_param('course', 0, PARAM_INT);

require_login(0, false);
if (isguestuser()) {
    $SESSION->wantsurl = (string)new moodle_url('/siteadmin/manage/enrol_basic_group.php', array('course'=>$coruseid));
    redirect(get_login_url());
}

$groups = $DB->get_records('groups', array('courseid'=>$courseid));

foreach($groups as $group) {
    $DB->delete_records('lmsdata_group', array('mgroupid'=>$group->id));
}
//모든 그룹 맴버 삭제
groups_delete_group_members($courseid);

//모든 그룹 삭제
groups_delete_groups($courseid);

redirect($CFG->wwwroot.'/siteadmin/manage/enrol_basic_group.php?course='.$courseid);

