<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Lists all the users within a given course.
 *
 * @copyright 1999 Martin Dougiamas  http://dougiamas.com
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package core_user
 */

require_once('../../config.php');
require_once($CFG->libdir.'/tablelib.php');
require_once($CFG->libdir.'/filelib.php');
require_once($CFG->libdir.'/accesslib.php');

define('USER_SMALL_CLASS', 20);   // Below this is considered small.
define('USER_LARGE_CLASS', 200);  // Above this is considered large.
define('DEFAULT_PAGE_SIZE', 50);
define('SHOW_ALL_PAGE_SIZE', 5000);
define('MODE_BRIEF', 0);

$page         = optional_param('page', 0, PARAM_INT); // Which page to show.
$perpage      = optional_param('perpage', DEFAULT_PAGE_SIZE, PARAM_INT); // How many per page.
$mode         = optional_param('mode', MODE_BRIEF, PARAM_INT); // Use the MODE_ constants.
$accesssince  = optional_param('accesssince', 0, PARAM_INT); // Filter by last access. -1 = never.
$search       = optional_param('search', '', PARAM_RAW); // Make sure it is processed with p() or s() when sending to output!
$roleid       = optional_param('roleid', 0, PARAM_INT); // Optional roleid, 0 means all enrolled users (or all on the frontpage).
$contextid    = optional_param('contextid', 0, PARAM_INT); // One of this or.
$courseid     = optional_param('id', 0, PARAM_INT); // This are required.

//대형추가
$tempcourseid=$courseid;

$PAGE->set_url('/local/okcourse/userindex.php', array(
        'page' => $page,
        'perpage' => $perpage,
        'mode' => $mode,
        'accesssince' => $accesssince,
        'search' => $search,
        'roleid' => $roleid,
        'contextid' => $contextid,
        'id' => $courseid));

if ($contextid) {
    $context = context::instance_by_id($contextid, MUST_EXIST);
    if ($context->contextlevel != CONTEXT_COURSE) {
        print_error('invalidcontext');
    }
    $course = $DB->get_record('course', array('id' => $context->instanceid), '*', MUST_EXIST);
} else {
    $course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
    $context = context_course::instance($course->id, MUST_EXIST);
}
// Not needed anymore.
unset($contextid);
unset($courseid);

require_login($course);

$systemcontext = context_system::instance();
$isfrontpage = ($course->id == SITEID);

$frontpagectx = context_course::instance(SITEID);


if ($isfrontpage) {
    $PAGE->set_pagelayout('admin');
    require_capability('moodle/site:viewparticipants', $systemcontext);
} else {
    $PAGE->set_pagelayout('incourse');
    require_capability('moodle/course:viewparticipants', $context);
}

$rolenamesurl = new moodle_url("$CFG->wwwroot/local/okcourse/userindex.php?contextid=$context->id&sifirst=&silast=");
$rolenames = role_fix_names(get_profile_roles($context), $context, ROLENAME_ALIAS, true);


if ($isfrontpage) {
    $rolenames[0] = get_string('allsiteusers', 'role');
} else {
    $rolenames[0] = get_string('allparticipants');
}

// Make sure other roles may not be selected by any means.
if (empty($rolenames[$roleid])) {
    print_error('noparticipants');
}

// No roles to display yet?
// frontpage course is an exception, on the front page course we should display all users.
if (empty($rolenames) && !$isfrontpage) {
    if (has_capability('moodle/role:assign', $context)) {
        redirect($CFG->wwwroot.'/'.$CFG->admin.'/roles/assign.php?contextid='.$context->id);
    } else {
        print_error('noparticipants');
    }
}



$event = \core\event\user_list_viewed::create(array(
    'objectid' => $course->id,
    'courseid' => $course->id,
    'context' => $context,
    'other' => array(
        'courseshortname' => $course->shortname,
        'coursefullname' => $course->fullname
    )
));
$event->trigger();

$countries = get_string_manager()->get_list_of_countries();

$strnever = get_string('never');

$datestring = new stdClass();
$datestring->year  = get_string('year');
$datestring->years = get_string('years');
$datestring->day   = get_string('day');
$datestring->days  = get_string('days');
$datestring->hour  = get_string('hour');
$datestring->hours = get_string('hours');
$datestring->min   = get_string('min');
$datestring->mins  = get_string('mins');
$datestring->sec   = get_string('sec');
$datestring->secs  = get_string('secs');

if ($mode !== null) {
    $mode = (int)$mode;
    $SESSION->userindexmode = $mode;
} else if (isset($SESSION->userindexmode)) {
    $mode = (int)$SESSION->userindexmode;
} else {
    $mode = MODE_BRIEF;
}

// Check to see if groups are being used in this course
// and if so, set $currentgroup to reflect the current group.

$groupmode    = groups_get_course_groupmode($course);   // Groups are being used.
$currentgroup = groups_get_course_group($course, true);

if (!$currentgroup) {      // To make some other functions work better later.
    $currentgroup  = null;
}

$isseparategroups = ($course->groupmode == SEPARATEGROUPS and !has_capability('moodle/site:accessallgroups', $context));

$currentlang = current_language();
$coursename = $course->fullname;
if($currentlang != 'ko') {
    $coursename = $DB->get_field('lmsdata_class', 'eng_lec_name', array('course'=>$course->id));
}

$PAGE->set_title("$coursename: ".get_string('participants'));
$PAGE->set_heading($coursename);
$PAGE->set_pagetype('course-view-' . $course->format);
$PAGE->add_body_class('path-user');                     // So we can style it independently.
$PAGE->set_other_editing_capability('moodle/course:manageactivities');

echo $OUTPUT->header();

echo '<div class="userlist">';

if ($isseparategroups and (!$currentgroup) ) {
    // The user is not in the group so show message and exit.
    echo $OUTPUT->heading(get_string("notingroup"));
    echo $OUTPUT->footer();
    exit;
}


// Should use this variable so that we don't break stuff every time a variable is added or changed.
$baseurl = new moodle_url('/local/okcourse/userindex.php', array(
        'contextid' => $context->id,
        'roleid' => $roleid,
        'id' => $course->id,
        'perpage' => $perpage,
        'accesssince' => $accesssince,
        'search' => s($search)));

// Get the hidden field list.
if (has_capability('moodle/course:viewhiddenuserfields', $context)) {
    $hiddenfields = array();  // Teachers and admins are allowed to see everything.
} else {
    $hiddenfields = array_flip(explode(',', $CFG->hiddenuserfields));
}

if (isset($hiddenfields['lastaccess'])) {
    // Do not allow access since filtering.
    $accesssince = 0;
}

if ($currentgroup and (!$isseparategroups or has_capability('moodle/site:accessallgroups', $context))) {
    // Display info about the group.
    if ($group = groups_get_group($currentgroup)) {
        if (!empty($group->description) or (!empty($group->picture) and empty($group->hidepicture))) {
            $groupinfotable = new html_table();
            $groupinfotable->attributes['class'] = 'groupinfobox';
            $picturecell = new html_table_cell();
            $picturecell->attributes['class'] = 'left side picture';
            $picturecell->text = print_group_picture($group, $course->id, true, true, false);

            $contentcell = new html_table_cell();
            $contentcell->attributes['class'] = 'content';

            $contentheading = $group->name;
            if (has_capability('moodle/course:managegroups', $context)) {
                $aurl = new moodle_url('/group/group.php', array('id' => $group->id, 'courseid' => $group->courseid));
                $contentheading .= '&nbsp;' . $OUTPUT->action_icon($aurl, new pix_icon('t/edit', get_string('editgroupprofile')));
            }

            $group->description = file_rewrite_pluginfile_urls($group->description, 'pluginfile.php', $context->id, 'group',
                'description', $group->id);
            if (!isset($group->descriptionformat)) {
                $group->descriptionformat = FORMAT_MOODLE;
            }
            $options = array('overflowdiv' => true);
            $formatteddesc = format_text($group->description, $group->descriptionformat, $options);
            $contentcell->text = $OUTPUT->heading($contentheading, 3) . $formatteddesc;
            $groupinfotable->data[] = new html_table_row(array($picturecell, $contentcell));
            echo html_writer::table($groupinfotable);
        }
    }
}

// Define a table showing a list of users in the current role selection.
$tablecolumns = array();
$tableheaders = array();

// 대형 추가

$contextinfo = $DB->get_record('context', array('instanceid'=>$tempcourseid, 'contextlevel'=>CONTEXT_COURSE));
$userrolenum = $DB->get_record_sql('select max(roleid) AS maxrole from {role_assignments} where contextid= :contextid and  userid = :userid ', array('contextid'=>$contextinfo->id,'userid'=>$USER->id));
$assistant_userrole=$userrolenum->maxrole;

//사진
$tablecolumns[] = 'userpic';
$tableheaders[] = get_string('userpic');
//이름
$tablecolumns[] = 'firstname';
$tableheaders[] = get_string('user:name', 'local_okcourse');
//역할
$tablecolumns[] = 'role';
$tableheaders[] = get_string('user:role', 'local_okcourse');

$usergroup = $DB->get_field('lmsdata_user','usergroup', array('userid'=>$USER->id));
//학번
if($usergroup != 'rs') {
    $tablecolumns[] = 'username';
    $tableheaders[] = get_string('user:usernumber', 'local_okcourse');
}else if($assistant_userrole==4){
	$tablecolumns[] = 'username';
	$tableheaders[] = get_string('user:usernumber', 'local_okcourse');

}

$tablecolumns[] = 'attach';
$tableheaders[] = get_string('user:attach', 'local_okcourse');

$tablecolumns[] = 'email';
$tableheaders[] = get_string('email','local_okcourse');


if($usergroup != 'rs') {
	$tablecolumns[] = 'mobile';
	$tableheaders[] = 'mobile';
	if (!isset($hiddenfields['lastaccess'])) {
	    $tablecolumns[] = 'lastaccess';
	    if ($course->id == SITEID) {
	        // Exception case for viewing participants on site home.
	        $tableheaders[] = get_string('lastsiteaccess');
	    } else {
	        $tableheaders[] = get_string('lastcourseaccess');
	    }
	}
}else if($assistant_userrole==4){
	$tablecolumns[] = 'mobile';
	$tableheaders[] = 'mobile';
	if (!isset($hiddenfields['lastaccess'])) {
		$tablecolumns[] = 'lastaccess';
		if ($course->id == SITEID) {
			// Exception case for viewing participants on site home.
			$tableheaders[] = get_string('lastsiteaccess');
		} else {
			$tableheaders[] = get_string('lastcourseaccess');
		}
	}
}


$table = new flexible_table('user-index-participants-'.$course->id);
$table->define_columns($tablecolumns);
$table->define_headers($tableheaders);
$table->define_baseurl($baseurl->out());

if (!isset($hiddenfields['lastcourseaccess'])) {
    $table->sortable(true, 'lastcourseaccess', SORT_DESC);
} else {
    $table->sortable(true, 'firstname', SORT_ASC);
}

$table->no_sorting('roles');
$table->no_sorting('groups');
$table->no_sorting('groupings');
$table->no_sorting('select');
$table->no_sorting('attach');
$table->no_sorting('mobile');


$table->set_attribute('cellspacing', '0');
$table->set_attribute('id', 'participants');
$table->set_attribute('class', 'generaltable generalbox');

$table->set_control_variables(array(
            TABLE_VAR_SORT    => 'ssort',
            TABLE_VAR_HIDE    => 'shide',
            TABLE_VAR_SHOW    => 'sshow',
            TABLE_VAR_IFIRST  => 'sifirst',
            TABLE_VAR_ILAST   => 'silast',
            TABLE_VAR_PAGE    => 'spage'
            ));
$table->setup();

list($esql, $params) = get_enrolled_sql($context, null, $currentgroup, true);
$joins = array("FROM {user} u");
$wheres = array();

$extrasql = get_extra_user_fields_sql($context, 'u', '', array(
        'id', 'username', 'firstname', 'lastname', 'email', 'city', 'country',
        'picture', 'lang', 'timezone', 'maildisplay', 'imagealt', 'lastaccess'));

$mainuserfields = user_picture::fields('u', array('username', 'email', 'city', 'country', 'lang', 'timezone', 'maildisplay'));

$joins[] = " LEFT JOIN {lmsdata_user} yu ON yu.userid = u.id ";
$mainuserfields .= " ,yu.b_email, yu.b_univ, yu.b_major, yu.univ, yu.major, yu.ehks as e_major, yu.edhs as e_univ ";
if ($isfrontpage) {
    $select = "SELECT $mainuserfields, u.phone2, u.lastaccess$extrasql";
    $joins[] = "JOIN ($esql) e ON e.id = u.id"; // Everybody on the frontpage usually.
    if ($accesssince) {
        $wheres[] = get_user_lastaccess_sql($accesssince);
    }

} else {
    $select = "SELECT $mainuserfields, u.phone2, COALESCE(ul.timeaccess, 0) AS lastaccess$extrasql";
    $joins[] = "JOIN ($esql) e ON e.id = u.id"; // Course enrolled users only.
    $joins[] = "LEFT JOIN {user_lastaccess} ul ON (ul.userid = u.id AND ul.courseid = :courseid)"; // Not everybody accessed course yet.
    $params['courseid'] = $course->id;
    if ($accesssince) {
        $wheres[] = get_course_lastaccess_sql($accesssince);
    }
}

// Performance hacks - we preload user contexts together with accounts.
$ccselect = ', ' . context_helper::get_preload_record_columns_sql('ctx');
$ccjoin = "LEFT JOIN {context} ctx ON (ctx.instanceid = u.id AND ctx.contextlevel = :contextlevel)";
$params['contextlevel'] = CONTEXT_USER;
$select .= $ccselect;
$joins[] = $ccjoin;


// Limit list to users with some role only.
if ($roleid) {
    // We want to query both the current context and parent contexts.
    list($relatedctxsql, $relatedctxparams) = $DB->get_in_or_equal($context->get_parent_context_ids(true), SQL_PARAMS_NAMED, 'relatedctx');

    $wheres[] = "u.id IN (SELECT userid FROM {role_assignments} WHERE roleid = :roleid AND contextid $relatedctxsql)";
    $params = array_merge($params, array('roleid' => $roleid), $relatedctxparams);
}

$from = implode("\n", $joins);
if ($wheres) {
    $where = "WHERE " . implode(" AND ", $wheres);
} else {
    $where = "";
}

$totalcount = $DB->count_records_sql("SELECT COUNT(u.id) $from $where", $params);

if (!empty($search)) {
    $fullname = $DB->sql_fullname('u.firstname', 'u.lastname');
    $wheres[] = "(". $DB->sql_like($fullname, ':search1', false, false) .
                " OR ". $DB->sql_like('email', ':search2', false, false) .
                " OR ". $DB->sql_like('idnumber', ':search3', false, false) .") ";
    $params['search1'] = "%$search%";
    $params['search2'] = "%$search%";
    $params['search3'] = "%$search%";
}

list($twhere, $tparams) = $table->get_sql_where();
if ($twhere) {
    $wheres[] = $twhere;
    $params = array_merge($params, $tparams);
}

$from = implode("\n", $joins);
if ($wheres) {
    $where = "WHERE " . implode(" AND ", $wheres);
} else {
    $where = "";
}

if ($table->get_sql_sort()) {
    $sort = ' ORDER BY '.$table->get_sql_sort();
} else {
	if($currentlang != 'ko') {
		$sort = 'ORDER BY lastname ASC';
	}else{
		$sort = 'ORDER BY firstname ASC';
	}
}

$matchcount = $DB->count_records_sql("SELECT COUNT(u.id) $from $where", $params);

$table->initialbars(false);
$table->pagesize($perpage, $matchcount);

// List of users at the current visible page - paging makes it relatively short.
$userlist = $DB->get_recordset_sql("$select $from $where $sort", $params, $table->get_page_start(), $table->get_page_size());

// If there are multiple Roles in the course, then show a drop down menu for switching.
if (count($rolenames) > 1) {
    echo '<div class="rolesform">';
    echo '<label for="rolesform_jump">'.get_string('currentrole', 'role').'&nbsp;</label>';
    echo $OUTPUT->single_select($rolenamesurl, 'roleid', $rolenames, $roleid, null, 'rolesform');
    echo '</div>';

} else if (count($rolenames) == 1) {
    // When all users with the same role - print its name.
    echo '<div class="rolesform">';
    echo get_string('role').get_string('labelsep', 'langconfig');
    $rolename = reset($rolenames);
    echo $rolename;
    echo '</div>';
}

if ($roleid > 0) {
    $a = new stdClass();
    $a->number = $totalcount;
    $a->role = $rolenames[$roleid];
    $heading = format_string(get_string('xuserswiththerole', 'role', $a));

    if ($currentgroup and $group) {
        $a->group = $group->name;
        $heading .= ' ' . format_string(get_string('ingroup', 'role', $a));
    }

    if ($accesssince) {
        $a->timeperiod = $timeoptions[$accesssince];
        $heading .= ' ' . format_string(get_string('inactiveformorethan', 'role', $a));
    }

    $heading .= ": $a->number";

    if (user_can_assign($context, $roleid)) {
        $headingurl = new moodle_url($CFG->wwwroot . '/' . $CFG->admin . '/roles/assign.php',
                array('roleid' => $roleid, 'contextid' => $context->id));
        $heading .= $OUTPUT->action_icon($headingurl, new pix_icon('t/edit', get_string('edit')));
    }
    echo $OUTPUT->heading($heading, 3);
} else {
    if ($course->id != SITEID && has_capability('moodle/course:enrolreview', $context)) {
        $editlink = $OUTPUT->action_icon(new moodle_url('/enrol/users.php', array('id' => $course->id)),
                                         new pix_icon('t/edit', get_string('edit')));
    } else {
        $editlink = '';
    }
    if ($course->id == SITEID and $roleid < 0) {
        $strallparticipants = get_string('allsiteusers', 'role');
    } else {
        $strallparticipants = get_string('allparticipants');
    }
    if ($matchcount < $totalcount) {
        echo $OUTPUT->heading($strallparticipants.get_string('labelsep', 'langconfig').$matchcount.'/'.$totalcount . $editlink, 3);
    } else {
        echo $OUTPUT->heading($strallparticipants.get_string('labelsep', 'langconfig').$matchcount . $editlink, 3);
    }
}


    echo '<form action="userindex.php" method="post" id="participantsform" >';
    echo '<div>';
    echo '<input type="hidden" name="sesskey" value="'.sesskey().'" />';
    echo '<input type="hidden" name="returnto" value="'.s($PAGE->url->out(false)).'" />';

    echo '<input type="text" title="'.get_string('search:keywords', 'local_courselist').'" class="w-200" name="search" placeholder="'.get_string('search:keywords', 'local_courselist').'" value="'.$search.'" />';
    echo '<input type="submit" value="'.get_string('search', 'local_courselist').'"  class="board-search" />';

    $countrysort = (strpos($sort, 'country') !== false);
    $timeformat = get_string('strftimedate');

    if ($userlist) {

        $usersprinted = array();
        foreach ($userlist as $user) {
            if (in_array($user->id, $usersprinted)) { // Prevent duplicates by r.hidden - MDL-13935.
                continue;
            }
            $usersprinted[] = $user->id; // Add new user to the array of users printed.

            context_helper::preload_from_record($user);

            if ($user->lastaccess) {
                $lastaccess = format_time(time() - $user->lastaccess, $datestring);
            } else {
                $lastaccess = $strnever;
            }

            $usercontext = context_user::instance($user->id);

            	$profilelink = '<strong><a href="'.$CFG->wwwroot.'/user/profile.php?id='.$user->id.'&amp;course='.$course->id.'">'.fullname($user).'</a></strong>';

            $data = array();
            $data[] = $OUTPUT->user_picture($user, array('size' => 35, 'courseid' => $course->id));
            $userroles = $ras = get_user_roles($context, $user->id, true, 'c.contextlevel DESC, r.sortorder ASC');
            $userrole_names = array();
            foreach($userroles as $userrole) {
                $userrole_names[] = role_get_name($userrole);
            }
            $data[] = $profilelink;
            $data[] = implode('/', $userrole_names);
                        
            //if($USER->id == $user->id || $usergroup == 'ad' || $usergroup == 'pr' || is_siteadmin() || $assistant_userrole==4){
            //본인도 개인정보 안보이게 2015.09.21
			if($usergroup == 'ad' || $usergroup == 'pr' || is_siteadmin() || $assistant_userrole==4){
				$user_email = $user->email;
                if($currentlang != 'ko') {
                    $attach_univ = empty($user->e_univ) ? '-' : ($user->e_univ) ;
                    $attach_major = empty($user->e_major) ?  '-' : ($user->e_major);
                } else {
                    $attach_univ = empty($user->univ) ? '-' : ($user->univ) ;
                    $attach_major = empty($user->major) ?  '-' : ($user->major);
                }
            }
            /* 2015.08.17 OSE 김대형 수정 : 학생일때 이메일,소속 비공개일때는 비공개로 표시
            else if($usergroup == 'rs') {
            	$user_email = empty($user->b_email) ? '-' : ($user->email) ;

            	if($currentlang != 'ko') {
            		$attach_univ = empty($user->b_univ) ? '-' : ($user->e_univ) ;
            		$attach_major = empty($user->b_major) ?  '-' : ($user->e_major);
            	}else {
            		$attach_univ = empty($user->b_univ) ? '-' : ($user->univ) ;
            		$attach_major = empty($user->b_major) ?  '-' : ($user->major);
            	}
            }
            */
            else if($usergroup == 'rs' && $assistant_userrole!=4) {
                //0이나 ""이면 FALSE
                //$user->b_email 이 0(비공개)이거나 ""이면  '-' 로 표시
              	if($user->b_email == 0 && trim($user->email) != null) {
            		$user_email = get_string('user:private', 'local_okcourse');
            	}else{
            		$user_email = $user->email;
            	}

                if($currentlang != 'ko') {
                	if($user->b_univ == 0 && $user->e_univ !=null) {
                    	$attach_univ = get_string('user:private', 'local_okcourse');
                    }else{
                    	$attach_univ = $user->e_univ;
                    }

                    if($user->b_major == 0 && $user->e_major !=null) {
                    	$attach_major = get_string('user:private', 'local_okcourse');
                    }else{
                    	$attach_major = $user->e_major;
                    }
                }else {
                    if($user->b_univ == 0 && $user->univ !=null) {
                		$attach_univ = get_string('user:private', 'local_okcourse');
                	}else{
                		$attach_univ = $user->univ;
                	}

                	if($user->b_major == 0 && $user->major !=null) {
                		$attach_major = get_string('user:private', 'local_okcourse');
                	}else{
                		$attach_major = $user->major;
                	}
                }
            }
            $attach = $attach_univ.' / '.$attach_major;
            if($usergroup == 'ad' || $usergroup == 'pr' || is_siteadmin() || $assistant_userrole==4){
            //2015.09.02 OSE 김대형 수정 : 교번은 안보이게
				if(substr($user->username, 0, 1)=='y' || substr($user->username, 0, 1)=='z'){
            		$data[] = "";
            	}else{
            		$data[] = $user->username;
            	}
            }
            $data[] = $attach;
            $data[] = $user_email;

            //2015.08.17 OSE 김대형 수정 : 학생이면 최근 접속시간 안보이게
            if($usergroup == 'ad' || $usergroup == 'pr' || is_siteadmin() || $assistant_userrole==4){
	            $data[] = $user->phone2;
	            if (!isset($hiddenfields['lastaccess'])) {
	                $data[] = $lastaccess;
	            }
            }

            $table->add_data($data);
        }
    }
    $table->print_html();
    echo '<br /><div class="buttons">';


    echo '<input type="hidden" name="id" value="'.$course->id.'" />';
    echo '<input type="hidden" name="formaction" value="messageselect.php" />';
    echo '<noscript style="display:inline">';
    echo '<div><input type="submit" value="'.get_string('ok').'" /></div>';
    echo '</noscript>';
    echo '</div></div>';
    echo '</form>';

    $module = array('name' => 'core_user', 'fullpath' => '/user/module.js');
    $PAGE->requires->js_init_call('M.core_user.init_participation', null, false, $module);


$perpageurl = clone($baseurl);
$perpageurl->remove_params('perpage');
if ($perpage == SHOW_ALL_PAGE_SIZE) {
    $perpageurl->param('perpage', DEFAULT_PAGE_SIZE);
    echo $OUTPUT->container(html_writer::link($perpageurl, get_string('showperpage', '', DEFAULT_PAGE_SIZE)), array(), 'showall');

} else if ($matchcount > 0 && $perpage < $matchcount) {
    $perpageurl->param('perpage', SHOW_ALL_PAGE_SIZE);
    echo $OUTPUT->container(html_writer::link($perpageurl, get_string('showall', '', $matchcount)), array(), 'showall');
}

echo '</div>';  // Userlist.

echo $OUTPUT->footer();

if ($userlist) {
    $userlist->close();
}

/**
 * Returns SQL that can be used to limit a query to a period where the user last accessed a course..
 *
 * @param string $accesssince
 * @return string
 */
function get_course_lastaccess_sql($accesssince='') {
    if (empty($accesssince)) {
        return '';
    }
    if ($accesssince == -1) { // Never.
        return 'ul.timeaccess = 0';
    } else {
        return 'ul.timeaccess != 0 AND ul.timeaccess < '.$accesssince;
    }
}

/**
 * Returns SQL that can be used to limit a query to a period where the user last accessed the system.
 *
 * @param string $accesssince
 * @return string
 */
function get_user_lastaccess_sql($accesssince='') {
    if (empty($accesssince)) {
        return '';
    }
    if ($accesssince == -1) { // Never.
        return 'u.lastaccess = 0';
    } else {
        return 'u.lastaccess != 0 AND u.lastaccess < '.$accesssince;
    }
}


?>

<script typp="text/javascript">
    function course_message_submit(){
        var checked = false;
        $('.usercheckbox').each(function(index, element){
            if($(this).is(":checked")){
                checked = true;
            }
        });
        if(!checked) {
            alert('<?php echo get_string('alertmessage', 'local_okcourse') ?>');
            return false();
        }
        $('#participantsform').attr('action','<?php echo $CFG->wwwroot.'/user/messageselect.php'; ?>');
        $('#participantsform').submit();
    }
</script>