<?php
require_once $CFG->dirroot.'/lib/enrollib.php';

function local_lmsdata_course_created($eventdata) {
    global $DB, $CFG, $USER;

    $modinfo = $DB->get_record('modules', array('name' => 'jinotechboard'));

    $notice = new stdClass();

    $notice->forcesubscribe = 0;

    if (isset($CFG->board_massmaxbytes)) {
        $notice->maxbytes = $CFG->board_massmaxbytes;
    } else {
        $notice->maxbytes = 0;
    }

    $notice->maxattachments = 5;

    $notice->allowrecommend = 0;
    $notice->allowcomment = 0;
    $notice->gradecat = 1;
    $notice->assessed = 1;
    $notice->assesstimestart = time();
    $notice->assesstimefinish = time();
    $notice->visible = 1;
    $notice->groupmode = 0;
    $notice->groupingid = 0;
    $notice->groupmembersonly = 0;
    $notice->course = $eventdata->id;
    $notice->section = 0;
    $notice->module = $modinfo->id;
    $notice->modulename = 'jinotechboard';
    $notice->completion = 0;
    $notice->completionview = 0;
    $notice->completiongradeitemnumber = 0;
    $notice->introformat = 1;
    $notice->introeditor = '';

    $notice->timecreated = time();
    $notice->timemodified = time();

    $notice->allowreply = 0;
    $notice->allownotice = 1;
    $notice->allowgigan = 1;
    $notice->type = 1;
    if($eventdata->lang == 'en') {
        $notice->name = 'Notice';
    } else {
        $notice->name = '강의공지';
    }
    $notice->intro = " ";
    create_module($notice);

    $qna = new stdClass();

    $qna->forcesubscribe = 0;
    if (isset($CFG->board_massmaxbytes)) {
        $qna->maxbytes = $CFG->board_massmaxbytes;
    } else {
        $qna->maxbytes = 0;
    }
    $qna->maxattachments = 5;

    $qna->allowrecommend = 0;
    $qna->allowcomment = 0;
    
    $qna->gradecat = 1;
    $qna->assessed = 1;
    $qna->assesstimestart = time();
    $qna->assesstimefinish = time();
    $qna->visible = 1;
    $qna->groupmode = 0;
    $qna->groupingid = 0;
    $qna->groupmembersonly = 0;
    $qna->course = $eventdata->id;
    $qna->section = 0;
    $qna->module = $modinfo->id;
    $qna->modulename = 'jinotechboard';
    $qna->completion = 0;
    $qna->completionview = 0;
    $qna->completiongradeitemnumber = 0;
    $qna->introformat = 1;
    $qna->introeditor = '';

    $qna->timecreated = time();
    $qna->timemodified = time();

    $qna->allowsecret = 1;
    $qna->allowreply = 1;
    $qna->allownotice = 0;
    $qna->type = 2;
    if($eventdata->lang == 'en') {
        $qna->name = 'Q&A';
    } else {
        $qna->name = '질문과 답변';
    } 
    $qna->intro = " ";

    create_module($qna);
    
    $modinfo_lp = $DB->get_record('modules', array('name' => 'lcmsprogress'));
    
    /*
    $lcmsprogress = new stdClass();
    
    $lcmsprogress->gradecat = 1;    
    $lcmsprogress->assessed = 1;
    $lcmsprogress->assesstimestart = time();
    $lcmsprogress->assesstimefinish = time();
    $lcmsprogress->visible = 1;
    $lcmsprogress->groupmode = 0;
    $lcmsprogress->groupingid = 0;
    $lcmsprogress->groupmembersonly = 0;
    $lcmsprogress->course = $eventdata->id;
    $lcmsprogress->section = 0;
    $lcmsprogress->module = $modinfo_lp->id;
    $lcmsprogress->modulename = 'lcmsprogress';
    $lcmsprogress->completion = 0;
    $lcmsprogress->completionview = 0;
    $lcmsprogress->completiongradeitemnumber = 0;
    $lcmsprogress->introformat = 1;
    $lcmsprogress->introeditor = '';
    $lcmsprogress->grade['modgrade_type'] = 'point';
    $lcmsprogress->grade['modgrade_point'] = 100;
    
    if($eventdata->lang == 'ko' ) {
        $lcmsprogress->name = '강의콘텐츠 이용통계';
    } else if($eventdata->lang == 'en') {
        $lcmsprogress->name = 'Contents use statistics';
    } 
    $lcmsprogress->intro = " ";
    $lcmsprogress->grade = 100;
    $lcmsprogress->completionprogress = 0;
    $lcmsprogress->timemodified = time();
    
    create_module($lcmsprogress);
    */
    
    local_lmsdata_blockinstances_update($eventdata->id);
    
    // 등록 신청 추가. 정규=>청강생, 비정규=>수강생
    $courseid = $eventdata->id;
    $course = $DB->get_record('course', array('id'=>$courseid), '*', MUST_EXIST);
    $roles = $DB->get_records_menu('role', null, '', 'shortname, id');
    $roleid = $roles['auditor'];
    if($ysclass = $DB->get_record('lmsdata_class', array('course'=>$course->id))) {
        if($ysclass->isnonformal) {
            $roleid = $roles['student'];
        }
    }
    if($instance = $DB->get_record('enrol', array('courseid'=>$course->id, 'enrol'=>'apply'))) {
        $instance->status         = 0;
        $instance->roleid         = $roleid;
        $instance->timemodified   = time();
        $DB->update_record('enrol', $instance);
    } else {
        $plugin = enrol_get_plugin('apply');
        $fields = array(
                'status'          =>0,
                'name'            =>'',
                'roleid'          =>$roleid,
                'customtext1'     =>'');
        $plugin->add_instance($course, $fields);
    }
    
}

function local_lmsdata_user_delete($eventdata) {
    global $DB;
    if (!empty($eventdata->id)) {
        $DB->delete_records('lmsdata_user', array('userid' => $eventdata->id));
    }
}
function local_lmsdata_course_deleted($eventdata) {
    global $DB;
    if (!empty($eventdata->id)) {
        $DB->delete_records('lmsdata_class', array('course' => $eventdata->id));
    }
}

function local_lmsdata_blockinstances_update($courseid) {
    global $DB, $CFG;

    $sql = "select bi.* from {block_instances} bi
            join (select id from {context} where instanceid = :instanceid and contextlevel= :contextlevel) co on co.id = bi.parentcontextid";

    $params = array("instanceid" => $courseid, "contextlevel" => CONTEXT_COURSE);

    $instanceid = $DB->get_records_sql($sql, $params);

    foreach ($instanceid as $instance) {

        $data = new stdClass();
        $data->id = $instance->id;
        $data->showinsubcontexts = 1;
        $data->pagetypepattern = '*';

        $DB->update_record('block_instances', $data);
    }
}

function local_lmsdata_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options=array()){
 	global $CFG, $DB;

	$fileareas = array('attachment', 'content');
	if (!in_array($filearea, $fileareas)) {
		return false;
	}


	$fs = get_file_storage();
	$relativepath = implode('/', $args);

	$fullpath = "/$context->id/local_lmsdata/$filearea/$relativepath";
	if (!$file = $fs->get_file_by_hash(sha1($fullpath)) or $file->is_directory()) {
		return false;
	}


	// finally send the file
	send_stored_file($file, 0, 0, true); // download MUST be forced - security!
}

/* made by jb */

function local_lmsdata_block_reset($eventdata) {
    
    $context = context_course::instance($eventdata->id);
    blocks_delete_all_for_context($context->id);
    blocks_add_default_course_blocks($eventdata);
    
    local_lmsdata_blockinstances_update($eventdata->id);
}


function get_usercase($userid = 0) {
    global $DB, $USER;
    if ($userid == 0) {
        $userid = $USER->id;
    }
    
    if(is_siteadmin($userid)) {
        $usercase = 'manager';
        return $usercase;
    }
    $user_info = $DB->get_record('lmsdata_user', array('userid' => $userid));
    
    if(!empty($user_info->b_temp)){
        $usercase = "temp";
        return $usercase;
    }
    $stts_clsf_dcd = array(
        'student' => array('rs'),
        'assistant' => array('ad'),
        'teacher' => array('pr'));
    
    $usercase = 'student';
    if (in_array($user_info->usergroup, $stts_clsf_dcd['student'])) {
        $usercase = 'student';
    } else if (in_array($user_info->usergroup, $stts_clsf_dcd['assistant'])) {
        $usercase = 'assistant';
    } else if (in_array($user_info->usergroup, $stts_clsf_dcd['teacher'])) {
        $usercase = 'teacher';
    } 
    
    return $usercase;
}

function update_user_profile(){
    global $CFG, $DB;
    require_once("$CFG->libdir/gdlib.php");

    $context = context_user::instance($usernew->id, MUST_EXIST);
    $user = $DB->get_record('user', array('id' => $usernew->id), 'id, picture', MUST_EXIST);

    $newpicture = $user->picture;
    // Get file_storage to process files.
    $fs = get_file_storage();
    if (!empty($usernew->deletepicture)) {
        // The user has chosen to delete the selected users picture.
        $fs->delete_area_files($context->id, 'user', 'icon'); // Drop all images in area.
        $newpicture = 0;

    } else {
        // Save newly uploaded file, this will avoid context mismatch for newly created users.
        file_save_draft_area_files($usernew->imagefile, $context->id, 'user', 'newicon', 0, $filemanageroptions);
        if (($iconfiles = $fs->get_area_files($context->id, 'user', 'newicon')) && count($iconfiles) == 2) {
            // Get file which was uploaded in draft area.
            foreach ($iconfiles as $file) {
                if (!$file->is_directory()) {
                    break;
                }
            }
            // Copy file to temporary location and the send it for processing icon.
            if ($iconfile = $file->copy_content_to_temp()) {
                // There is a new image that has been uploaded.
                // Process the new image and set the user to make use of it.
                // NOTE: Uploaded images always take over Gravatar.
                $newpicture = (int)process_new_icon($context, 'user', 'icon', 0, $iconfile);
                // Delete temporary file.
                @unlink($iconfile);
                // Remove uploaded file.
                $fs->delete_area_files($context->id, 'user', 'newicon');
            } else {
                // Something went wrong while creating temp file.
                // Remove uploaded file.
                $fs->delete_area_files($context->id, 'user', 'newicon');
                return false;
            }
        }
    }

    if ($newpicture != $user->picture) {
        $DB->set_field('user', 'picture', $newpicture, array('id' => $user->id));
        return true;
    } else {
        return false;
    }
}



function enrol_get_my_courses_by_my_lmsdata($total = NULL, $param = array(), $page=1, $limit=10, $fields = NULL, $sort = 'visible DESC,sortorder ASC', $userid = 0) {
    global $DB, $USER;

    // Guest account does not have any courses
    if (isguestuser() or !isloggedin()) {
        return(array());
    }

    $basefields = array('id', 'category', 'sortorder',
                        'shortname', 'fullname', 'idnumber',
                        'startdate', 'visible',
                        'groupmode', 'groupmodeforce');

    if (empty($fields)) {
        $fields = $basefields;
    } else if (is_string($fields)) {
        // turn the fields from a string to an array
        $fields = explode(',', $fields);
        $fields = array_map('trim', $fields);
        $fields = array_unique(array_merge($basefields, $fields));
    } else if (is_array($fields)) {
        $fields = array_unique(array_merge($basefields, $fields));
    } else {
        throw new coding_exception('Invalid $fileds parameter in enrol_get_my_courses()');
    }
    if (in_array('*', $fields)) {
        $fields = array('*');
    }

    $orderby = "";
    $sort    = trim($sort);
    if (!empty($sort)) {
        $rawsorts = explode(',', $sort);
        $sorts = array();
        foreach ($rawsorts as $rawsort) {
            $rawsort = trim($rawsort);
            if (strpos($rawsort, 'c.') === 0) {
                $rawsort = substr($rawsort, 2);
            }
            $sorts[] = trim($rawsort);
        }
        $sort = 'c.'.implode(',c.', $sorts);
        $orderby = "ORDER BY $sort";
    }

    $wheres = array("c.id <> :siteid");
    $params = array('siteid'=>SITEID);

    if (isset($USER->loginascontext) and $USER->loginascontext->contextlevel == CONTEXT_COURSE) {
        // list _only_ this course - anything else is asking for trouble...
        $wheres[] = "courseid = :loginas";
        $params['loginas'] = $USER->loginascontext->instanceid;
    }

    $ycjoin = " ";
    $year = get_config('moodle', 'haxa_year');
    $term = get_config('moodle', 'haxa_term');
    
    if(!empty($year) && !empty($term)) {
        $ycjoin = " LEFT JOIN {lmsdata_class} yc ON yc.course = c.id ";
        $wheres[] = '((yc.year = :year AND yc.term = :term) OR (yc.year = :self_course ))';
        $params['year'] = get_config('moodle', 'haxa_year');
        $params['term'] = get_config('moodle', 'haxa_term');
        $params['self_course'] = "9999";
    }
    
    $coursefields = 'c.' .join(',c.', $fields);
    $ccselect = ', ' . context_helper::get_preload_record_columns_sql('ctx');
    $ccjoin = "LEFT JOIN {context} ctx ON (ctx.instanceid = c.id AND ctx.contextlevel = :contextlevel)";
    $params['contextlevel'] = CONTEXT_COURSE;
    $wheres = implode(" AND ", $wheres);
    
    //note: we can not use DISTINCT + text fields due to Oracle and MS limitations, that is why we have the subselect there
    $sql = "SELECT $coursefields $ccselect
              FROM {course} c
              JOIN (SELECT DISTINCT e.courseid
                      FROM {enrol} e
                      JOIN {user_enrolments} ue ON (ue.enrolid = e.id AND ue.userid = :userid)
                     WHERE ue.status = :active AND e.status = :enabled AND ue.timestart < :now1 AND (ue.timeend = 0 OR ue.timeend > :now2)
                   ) en ON (en.courseid = c.id)
           $ccjoin
           $ycjoin    
             WHERE $wheres
          $orderby";
    if(empty($userid)) {
        $params['userid']  = $USER->id;
    } else {
        $params['userid'] = $userid;
    }
    $params['active']  = ENROL_USER_ACTIVE;
    $params['enabled'] = ENROL_INSTANCE_ENABLED;
    $params['now1']    = round(time(), -2); // improves db caching
    $params['now2']    = $params['now1'];
    if($total == 'all'){
        $courses = $DB->get_records_sql($sql, $params);
    }else if($total == null){
        $courses = $DB->get_records_sql($sql, $params, ($page-1)* $limit, $limit);
    }

    // preload contexts and check visibility
    foreach ($courses as $id=>$course) {
        //context_instance_preload($course);
        if (!$course->visible) {
            if (!$context = context_course::instance(CONTEXT_COURSE, $id)) {
                unset($courses[$id]);
                continue;
            }
            if (!has_capability('moodle/course:viewhiddencourses', $context)) {
                unset($courses[$id]);
                continue;
            }
        }
        $courses[$id] = $course;
    }

    return $courses;
}
function local_lmsdata_browser_check(){
    
    $data = new stdClass();
    $data->device = '';
    
    $agent = get_browser(null, true);
    $data->browser = $agent['browser'];          // 브라우저 종류
    $data->version = $agent['version'];           // 브라우저 버전
    $data->platform = $agent['platform'];         // OS 종류 Win7, Win10, iOS, Android
    $data->device   = $agent['device_type'];     // Desktop, Mobile Phone
    
    
    if($data->device == 'Mobile Phone' || $data->device == 'Mobile Device') {
        $data->device = 'M';
    }else if($data->device == 'Desktop') {
        $data->device = 'P';
    }else {
        $data->device = 'E';
    }
    
    return $data;
    
}
function local_lmsdata_get_client_ip() {
    $ipaddress = '';

    if (getenv('HTTP_IV_REMOTE_ADDRESS')) {
        $ipaddress = getenv('HTTP_IV_REMOTE_ADDRESS');
    }else if (getenv('HTTP_CLIENT_IP')) {
        $ipaddress = getenv('HTTP_CLIENT_IP');
    } else if(getenv('HTTP_X_FORWARDED_FOR')) {
        $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
    } else if(getenv('HTTP_X_FORWARDED')) {
        $ipaddress = getenv('HTTP_X_FORWARDED');
    } else if(getenv('HTTP_FORWARDED_FOR')) {
        $ipaddress = getenv('HTTP_FORWARDED_FOR');
    } else if(getenv('HTTP_FORWARDED')) {
       $ipaddress = getenv('HTTP_FORWARDED');
    } else if(getenv('REMOTE_ADDR')) {
        $ipaddress = getenv('REMOTE_ADDR');
    } else {
        $ipaddress = 'UNKNOWN';
    }
    
    return $ipaddress;
}

function local_lmsdata_cron() { 
    global $CFG,$DB;
    
    $current_time = time();
     $param = array('time1'=>$current_time,'time2'=>$current_time);
        
    $query = 'select * from {excel_user_period} where startdate > :time1 or enddate < :time2';
    $periods = $DB->get_records_sql($query,$param);
    
    foreach($periods as $period){
        $user = new stdClass();
        $user->id = $period->userid;
        $user->suspended = 1;
        $DB->update_record('user',$user);
    }
     
    $query = 'select * from {excel_user_period} where startdate < :time1 and enddate > :time2';
    $periods = $DB->get_records_sql($query,$param);
    
    foreach($periods as $period){
        $user = new stdClass();
        $user->id = $period->userid;
        $user->suspended = 0;
        $DB->update_record('user',$user);
    }

    return true;
}

function my_print_paging_navbar($totalcount, $page, $perpage, $baseurl, $params = null, $maxdisplay = 10) {
    global $CFG;
    $pagelinks = array();
   
    $lastpage = 1;
    if($totalcount > 0) {
        $lastpage = ceil($totalcount / $perpage);
    }
   
    if($page > $lastpage) {
        $page = $lastpage;
    }
           
    if ($page > round(($maxdisplay/3)*2)) {
        $currpage = $page - round($maxdisplay/2);
        if($currpage > ($lastpage - $maxdisplay)) {
            if(($lastpage - $maxdisplay) > 0){
                $currpage = $lastpage - $maxdisplay;
            }
        }
    } else {
        $currpage = 1;
    }
   
   
   
    if($params == null) {
        $params = array();
    }
   
    $prevlink = '';
    if ($page > 1) {
        $params['page'] = $page - 1;
        $prevlink = html_writer::link(new moodle_url($baseurl, $params), '<img alt="next" src="'.$CFG->wwwroot.'/siteadmin/img/pagination_left.png"/>', array('class'=>'next'));
    } else {
        $prevlink = '<a href="#" class="next"><img alt="next" src="'.$CFG->wwwroot.'/siteadmin/img/pagination_left.png"/></a>';
    }
   
    $nextlink = '';
     if ($page < $lastpage) {
        $params['page'] = $page + 1;
        $nextlink = html_writer::link(new moodle_url($baseurl, $params), '<img alt="prev" src="'.$CFG->wwwroot.'/siteadmin/img/pagination_right.png"/>', array('class'=>'prev'));
    } else {
        $nextlink = '<a href="#" class="prev"><img alt="prev" src="'.$CFG->wwwroot.'/siteadmin/img/pagination_right.png"/></a>';
    }
   
   
    $output = '<div class="pagination" style="text-align: center;">';
   
    $pagelinks[] = $prevlink;
   
    if ($currpage > 1) {
        $params['page'] = 1;
        $firstlink = html_writer::link(new moodle_url($baseurl, $params), 1);
       
        $pagelinks[] = $firstlink;
        if($currpage > 2) {
            $pagelinks[] = '...';
        }
    }
   
    $displaycount = 0;
    while ($displaycount <= $maxdisplay and $currpage <= $lastpage) {
        if ($page == $currpage) {
            $pagelinks[] = '<strong>'.$currpage.'</strong>';
        } else {
            $params['page'] = $currpage;
            $pagelink = html_writer::link(new moodle_url($baseurl, $params), $currpage);
            $pagelinks[] = $pagelink;
        }
       
        $displaycount++;
        $currpage++;
    }
   
    if ($currpage - 1 < $lastpage) {
        $params['page'] = $lastpage;
        $lastlink = html_writer::link(new moodle_url($baseurl, $params), $lastpage);
       
        if($currpage != $lastpage) {
            $pagelinks[] = '...';
        }
        $pagelinks[] = $lastlink;
    }
   
    $pagelinks[] = $nextlink;
   
   
    $output .= implode('&nbsp;', $pagelinks);
   
    $output .= '</div>';
    
    return $output; 
}