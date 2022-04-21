<?php
/** online_attendance 출석 최고점수 default 값 */
define('LOCAL_ONATTENDANCE_GRADE_ITEM_IDNUMBER', 'oklass_online_attendancebook');

/** online_attendance activity 구현 폴더 */
define('LOCAL_ONATTENDANCE_REALIZE_DIR', $CFG->dirroot.'/local/online_attendance/classes/activityes');

/** online_attendance_cm_batchset 의 startratio default 값 */
define('LOCAL_ONATTENDANCE_DEFAULT_STARTRATIO', 0);

/** online_attendance_cm_batchset 의 attendratio default 값 */
define('LOCAL_ONATTENDANCE_DEFAULT_ATTENDRATIO', 4*60*60*24);

/** online_attendance_cm_batchset 의 lateratio default 값 */
define('LOCAL_ONATTENDANCE_DEFAULT_LATERATIO', 7*60*60*24);

/** online_attendance_cm_batchset 의 aprogress default 값 */
define('LOCAL_ONATTENDANCE_DEFAULT_APROGRESS', 80);

/** online_attendance_cm_batchset 의 lprogress default 값 */
define('LOCAL_ONATTENDANCE_DEFAULT_LPROGRESS', 50);



/**
 * select box 만들어줌
 *
 * @param int $max 최대값
 * @param int $min 최소값
 * @param int $gap 차이
 * @param int $selectval default 값
 * @param array $options select tag elements
 * 
 */
function local_online_attendance_drow_selectbox($max, $min, $gap, $selectval = null, $options = array()) {
    
    if(!empty($options)) {
        $element ='';
        foreach($options as $key => $option) {
            $element .= $key.'="'.$option.'" ';
        }
        $selectbox = '<select '.$element.' >';
    } else {
        $selectbox = '<select>';
    }
    
    for($i = $max; $i >= $min; $i -= $gap) {
        $selected = "";
        if($i == $selectval) {
            $selected = ' selected';
        }
        $selectbox .= '<option value="'.$i.'"'.$selected.'>'.$i.'</option>';
    }
    
    $selectbox .= '</select>';
    
    return $selectbox;
}

/**
 * page navbar 만들어줌
 *
 * @param int $totalcount 총 갯수
 * @param int $page       총 페이지
 * @param int $perpage    한페이지보여지는갯수
 * @param string $baseurl 이동할 base url
 * @param int $maxdisplay 페이지 보여줄 갯수
 * 
 * @retrun string 작성된 html string 리턴
 */
function onattendance_print_paging_navbar_script($totalcount, $page, $perpage, $baseurl, $maxdisplay = 10) {
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
            $currpage = $lastpage - $maxdisplay;
        }
    } else {
        $currpage = 1;
    }
   
    $prevlink = '';
    if ($page > 1) {
        $prevlink = '<span class="board-nav-prev">'.html_writer::link(str_replace(':page', $page - 1, $baseurl), "<")."</span>";
    } else {
        $prevlink = '<span class="board-nav-prev"><a href="#"><</a></span>';
    }
    
    $nextlink = '';
     if ($page < $lastpage) {
        $nextlink = '<span class="board-nav-next">'.html_writer::link(str_replace(':page', $page + 1, $baseurl), '>', array('class'=>'board-nav-next'))."</span>";;
    } else {
        $nextlink = '<span class="board-nav-next"><a href="#">></a></span>';
    }
   
   
    echo '<div class="board-breadcrumbs">';
   
    $pagelinks[] = $prevlink;
   
    $pagelinks[] ="<ul>";
    if ($currpage > 1) {
        $firstlink = '<li>'.html_writer::link(str_replace(':page', 1, $baseurl), 1).'</li>';
       
        $pagelinks[] = $firstlink;
        if($currpage > 2) {
            $pagelinks[] = '<li>...</li>';
        }
    }
   
    $displaycount = 0;
    while ($displaycount <= $maxdisplay and $currpage <= $lastpage) {
        if ($page == $currpage) {
            $pagelinks[] = '<li class="current"><a href="#">'.$currpage.'</a></li>';
        } else {
            $pagelink = '<li>'.html_writer::link(str_replace(':page', $currpage, $baseurl), $currpage).'</li>';
            $pagelinks[] = $pagelink;
        }
        
        $displaycount++;
        $currpage++;
    }
    
    if ($currpage - 1 < $lastpage) {
        $lastlink = '<li>'.html_writer::link(str_replace(':page', $lastpage, $baseurl), $lastpage).'</li>';
        
        if($currpage != $lastpage) {
            $pagelinks[] = '<li>...</li>';
        }
        $pagelinks[] = $lastlink;
    }
    $pagelinks[] ="</ul>";
   
    $pagelinks[] = $nextlink;
   
   
    echo implode('', $pagelinks);
   
    echo '</div>';
}

/**
 * 로그 저장
 *
 * @param int $max 최대값
 * @param int $min 최소값
 * @param int $max 차이
 * @param int $selectbal default 값
 * @param array $opttion select tag elements
 * 
 */

function local_online_attendance_log($statusid, $userid, $ostatus, $nstatus) {
    global $DB;
    
    $log = new stdClass();
    $log->statusid = $statusid;
    $log->userid = $userid;
    $log->ostatus = $ostatus;
    $log->nstatus = $nstatus;
    $log->timecreated = time();
    
    $DB->insert_record('local_on_attendance_log', $log);
}

/*
 *  LOCAL_ONATTENDANCE_REALIZE_DIR 경로에 있는 파일을 기준으로 온라인 출석부에서 사용할 activity 목록을 반환함 
 * 
 * @return object
 */

function local_onattendance_realize_modules() {
    global $DB;
    
    $visibles = $DB->get_records('modules', array('visible'=>1), '', 'name, visible');
    
    $activityes = array(); 

    $handler = opendir(LOCAL_ONATTENDANCE_REALIZE_DIR); 
    while ($file = readdir($handler)) { 
        if ($file != '.' && $file != '..' && is_dir($file) != '1') {
            $path = LOCAL_ONATTENDANCE_REALIZE_DIR.'/'.$file;
            preg_match('/^local_onattend_mod_([a-z]*).php/', $file, $modname); 
            
            $name = $modname[1];
            
            if(isset($visibles[$name])) {
                require_once $path;
                $classname = 'local_onattend_mod_'.$name;
                $class = new $classname;

                $modobj = new Stdclass();
                $modobj->classname = $classname;
                $modobj->modname = $name;
                $modobj->timetype = $class::TIMETYPE;
                $modobj->path = $path;
                $activityes[] = $modobj;
            }
        }
    }
    
    return $activityes;
}

/*
 *  admin 사이트관리에서 설정 된 
 *  config_plugin 테이블에 있는 online_attendance 설정값을 반환
 * 
 * @return array
 */

function local_onattendance_get_adminsetting() {
    global $DB;
    
    $sql = ' SELECT * FROM {config_plugins} ';
    
    $sql_conditions = array();
    $sql_conditions[] = 'plugin = :plugin';
    $sql_conditions[] = $DB->sql_like('name', ':mod');

    $sql_params['plugin'] = 'local_online_attendance';
    $sql_params['mod'] = 'mod_%';

    $sql_where = ' WHERE '.implode(' AND ', $sql_conditions);
    
    $settings = $DB->get_records_sql($sql.$sql_where, $sql_params);
    
    return $settings;
}

/*
 *  해당강의에 일괄설정으로 사용할 activity유형 반환
 * 
 * @param int $courseid  
 * @return array
 */

function local_onattendance_get_batchset($courseid) {
    global $DB;
    
    $sql = ' SELECT * FROM {local_onattend_cm_batchset} ';
    
    $sql_conditions = array();
    $sql_conditions[] = 'courseid = :courseid';
    $sql_conditions[] = 'visible = :visible';

    $sql_params['courseid'] = $courseid;
    $sql_params['visible'] = 1;

    $sql_where = ' WHERE '.implode(' AND ', $sql_conditions);
    
    $settings = $DB->get_records_sql($sql.$sql_where, $sql_params);
    
    return $settings;
}

/*
 *  admin 사이트관리에서 설정 된 활성화된 activity를 default 값으로 생성 
 * 
 * @param int $courseid
 */

function local_onattendance_default_batchset($courseid) {

    $adminsetting = local_onattendance_get_adminsetting();
    
    foreach($adminsetting as $setting) {
        if($setting->value) {
            $modname = str_replace('mod_', '', $setting->name);
            new online_attendance_batchset($courseid, $modname);
        }
    }
}

/*
 * 초단위 시간을 받아서 일/시간으로 반환
 * 
 * @param int $second 초
 * @return object 
 */

function local_onattendance_get_datetime($second) {
    
    //날짜
    $date = floor($second / (60*60*24));
    $second -= ($date*60*60*24);
    //시간
    $hour = floor($second / (60*60));
    $second -= ($hour*60*60);
    //분
    $minute = floor($second / 60);
    
    $datetime = new Stdclass();
    $datetime->date = $date;
    $datetime->hour = sprintf("%02d",$hour);
    $datetime->minute = sprintf("%02d",$minute);
    
    return $datetime;
}

/*
 * 활성화된 활동 유형 중, 해당 course에 만들어져 있는 activity 목록을 반환함
 * 
 * @param int $courseid
 * @param bool $notcomplete local_onattend_cm_set에 있는 activity 중 지각인정시간 까지 지난 후 정산완료된 것을 포함할것인가 여부
 * @return object 
 */

function local_onattendance_get_cmset($courseid, $visible = true) {
    global $DB;
    
    $sql = ' SELECT loc.*, cm.instance
             FROM {local_onattend_cm_set} loc
             JOIN {course_modules} cm ON cm.id = loc.cmid
             JOIN {local_onattend_cm_batchset} lob ON lob.courseid = loc.courseid and lob.modname = loc.modname ';
    
    $sql_conditions = array();
    $sql_conditions[] = 'loc.courseid = :courseid';
    $sql_params['courseid'] = $courseid;
    
    if($visible) {
        $sql_conditions[] = 'lob.visible = :visible';
        $sql_params['visible'] = 1;
    }
    
    $sql_where = ' WHERE '.implode(' AND ', $sql_conditions);
    $sql_orderby = ' ORDER BY loc.section, loc.cmid, loc.timecreated ASC ';
    $cmset = $DB->get_records_sql($sql.$sql_where.$sql_orderby, $sql_params);
    
    return $cmset;
}

/*
 * H:i 형태 시간을 받아서 초로 반환
 * 
 * @param string $hitime 
 * @return object 
 */

function local_onattendance_get_hisecond($hitime) {
    $hiarray = explode(':', $hitime);
    $second = (int)$hiarray[0]*60*60;
    $second += (int)$hiarray[1]*60;
    return $second;
}

/*
 * 일괄 설정 시 변경된 값과 기존 mod의 값이 변경되었는지 확인, change 값 추가
 * 
 * @param object $batch local_onattend_cm_batchset 테이블 유형
 * @param object $mod local_onattend_cm_set 테이블 유형
 * @param int $startdate 해당 course의 시작 시간 unixtime
 * @return object 
 */

function local_onattendance_batch_change($batch, $mod, $startdate) {
   $mod->change = 0;
   $sectionstart = $startdate +(($mod->section - 1) * 60 * 60 * 24 * 7);
   $setstart = $sectionstart + $batch->startratio;
   $setattend = $sectionstart + $batch->attendratio;

   if($setstart != $mod->starttime) {
       $mod->starttime = $setstart;
       $mod->change = 1;
   }
   if($setattend != $mod->attendtime) {
       $mod->attendtime = $setattend;
       $mod->change = 1;
   }
   
   if($batch->aprogress != $mod->aprogress) {
       $mod->aprogress = $batch->aprogress;
       $mod->change = 1;
   }
   
   return $mod;
}

/*
 * 일괄 설정 시 변경된 값과 기존 mod의 값이 변경되었는지 확인, change 값 추가
 * 
 * @param object $batch local_onattend_cm_batchset 테이블 유형
 * @param object $mod local_onattend_cm_set 테이블 유형
 * @param int $startdate 해당 course의 시작 시간 unixtime
 * @return object 
 */

function local_onattendance_mod_change($data, $mod) {
   $mod->change = 0;
   
   if($data->starttime != $mod->starttime) {
       $mod->starttime = $data->starttime;
       $mod->change = 1;
   }
   if($data->attendtime != $mod->attendtime) {
       $mod->attendtime = $data->attendtime;
       $mod->change = 1;
   }
   if($data->aprogress != $mod->aprogress) {
       $mod->aprogress = $data->aprogress;
       $mod->change = 1;
   }
   if($data->approval != $mod->approval) {
       $mod->approval = $data->approval;
       $mod->change = 1;
   }
   
   return $mod;
}

/*
 * 일괄 설정 시 변경된 값과 기존 mod의 값이 변경되었는지 확인, change 값 추가
 * 
 * @param int $cmid         course_moduels id
 * @param int $cmid         user id
 * @param int $progress     진도율
 * @param int $time         입력시간
 * @return object 
 */

require_once($CFG->dirroot.'/local/online_attendance/classes/online_attendance_batchset.php');
function local_onattendance_set_status($cmid, $userid, $progress, $time) {
    global $DB;
    
    $courseid = $DB->get_field('course_modules', 'course', array('id'=>$cmid));
    $section = $DB->get_field_sql(' SELECT cs.section FROM {course_modules} cm JOIN {course_sections} cs ON cs.id = cm.section WHERE cm.id = :cmid', array('cmid'=>$cmid));
    if(!$DB->record_exists('local_onattend', array('courseid'=>$courseid))) {
        new online_attendance($courseid);
    }
    if(!$DB->record_exists('local_onattend_cm_batchset', array('courseid'=>$courseid))) {
        local_onattendance_default_batchset($courseid);
    }
    
    if(!$cmset = $DB->get_record('local_onattend_cm_set', array('cmid'=>$cmid))) {
        $modname = $DB->get_field_sql(' SELECT mo.name FROM {course_modules} cm JOIN {modules} mo ON mo.id = cm.module WHERE cm.id = :cmid', array('cmid'=>$cmid));
        $startdate = $DB->get_field_sql(' SELECT startdate FROM {course} WHERE id = :courseid', array('courseid'=>$courseid));
        $batchset = new online_attendance_batchset($courseid, $modname);
        
        $sectiondate = 0;
        if($section != 0) {
            $sectiondate = ($section-1) * 60 *60 *24 * 7;
        }
            
        $cmset = new Stdclass();
        $cmset->courseid = $courseid;
        $cmset->cmid = $cmid;
        $cmset->modname = $modname;
        $cmset->section = $section;
        $cmset->approval = 1;
        $cmset->starttime = $sectiondate + $startdate + $batchset->startratio;
        $cmset->attendtime = $sectiondate + $startdate + $batchset->attendratio;
        $cmset->aprogress = $batchset->aprogress;
        $cmset->timecreated = time();
        $cmset->timemodified = time();
        $cmset->id = $DB->insert_record('local_onattend_cm_set', $cmset);
    } else {
        $cmset = $DB->get_record('local_onattend_cm_set', array('cmid'=>$cmid));
    }
    
    $status = $DB->get_record('local_onattend_status', array('cmid'=>$cmid, 'userid'=>$userid));
    
    if(empty($status)) {
        $status = new Stdclass();
        $status->courseid = $cmset->courseid;
        $status->cmid = $cmid;
        $status->modname = $cmset->modname;
        $status->userid = $userid;
        $status->aprogress = 0;
        $status->lprogress = 0;
        $status->status = 0;
        if($cmset->starttime <= $time && $cmset->attendtime >= $time) {
            $status->aprogress = $progress;
            if($cmset->aprogress <= $progress) {
                $status->status = 1;    //출석
            } else {
                $status->status = 2;    //지각
            }
        }
        $status->timecreated = time();
        $status->timemodified = time();
        
        $status->id = $DB->insert_record('local_onattend_status', $status);
    }else {
        if($cmset->starttime <= $time && $cmset->attendtime >= $time) {
            if($status->aprogress < $progress) {
                $status->aprogress = $progress;
            }
            if($cmset->aprogress <=  $status->aprogress) {
                $status->status = 1;    //출석
            } else {
                $status->status = 2;    //지각
            }
        }
        $status->timecreated = time();
        $status->timemodified = time();
        
        $DB->update_record('local_onattend_status', $status);
    }
    
    local_onattendance_week_recalculate($courseid, $section, $userid);
    
    return $status->id;
}


/*
 * 해당 코스에 온라인출석부에서 사용되는 모든 activity의 user 출석값을 반환 
 * 
 * @param int $courseid         course table id
 * @param int|array $userid     user table id userid값이 0이면 등록된 모든 학생,
 * @return array                [cmid_userid] 형식, 값은 검색 object 
 */
function local_onattendance_get_status($courseid, $userid = 0) {
    global $DB;
    
    $roleid = $DB->get_field('role', 'id', array('archetype' => 'student'));
    
    $sql_conditions = array();
    $sql_params = array();
    if($userid != 0) {
        if(is_array($userid)) {
            list($sql_in, $sql_params) = $DB->get_in_or_equal($userid, SQL_PARAMS_NAMED, 'userid');
            $sql_conditions[] = 'ra.userid '.$sql_in;
        }else {
            $sql_conditions[] = ' ra.userid = :userid';
            $sql_params['userid'] = $userid;
        }
    }
    
    $concat='\'_\'';
    $fields = array('loc.id','ur.id');
    
    $sql_select = " SELECT ".$DB->sql_concat_join($concat, $fields)." AS id,
                          loc.id as cmsetid,
                          loc.starttime,
                          loc.attendtime,
                          loc.aprogress AS alimit, 
                          loc.section,
                          loc.courseid,
                          loc.modname,
                          loc.cmid,
                          cm.instance,
                          ra.userid AS userid,
                          ".$DB->sql_fullname('ur.firstname', 'ur.lastname')." AS fullname,
                          ur.username,    
                          CASE WHEN los.aprogress IS NULL THEN 0 ELSE los.aprogress END AS aprogress, 
                          CASE WHEN los.status IS NULL THEN 0 ELSE los.status END AS status ";
    $sql_from   = " FROM {local_onattend_cm_set} loc 
                    JOIN {course_modules} cm ON cm.id = loc.cmid
                    JOIN {context} co ON co.instanceid = loc.courseid and co.contextlevel = :contextlevel
                    JOIN {role_assignments} ra ON ra.contextid = co.id AND roleid =:roleid
                    JOIN {user} ur ON ur.id = ra.userid
                    LEFT JOIN {local_onattend_status} los ON los.cmid = cm.id AND los.userid = ra.userid ";

    $sql_conditions[] = 'loc.courseid = :courseid';
    $sql_conditions[] = 'loc.approval = :approval';

    
    $sql_params['approval'] = 1;
    $sql_params['contextlevel'] = CONTEXT_COURSE;
    $sql_params['roleid'] = $roleid;
    $sql_params['courseid'] = $courseid;
    
    $sql_where = ' WHERE '.implode(' AND ', $sql_conditions);
    $sql_orderby = ' ORDER BY ur.id, loc.section, loc.cmid ASC ';
  
    $users = $DB->get_records_sql($sql_select.$sql_from.$sql_where.$sql_orderby, $sql_params);
    
    return $users;
}

/*
 * 해당 코스에 온라인출석부에서 사용되는 모든 section의 user 출석값을 반환 
 * 
 * @param int $courseid         course table id
 * @param int|array $userid     user table id userid값이 0이면 등록된 모든 학생,
 * @return array               
 */
function local_onattendance_week_status($courseid, $userid = 0) {
    global $DB;
    
    $sql_params = array();
    if($userid != 0) {
        if(is_array($userid)) {
            list($sql_in, $sql_params) = $DB->get_in_or_equal($userid, SQL_PARAMS_NAMED, 'userid');
            $sql_conditions[] = 'ra.userid '.$sql_in;
        }else{
            $sql_conditions[] = ' ra.userid = :userid';
            $sql_params['userid'] = $userid;
        }
    }
    
    $concat='\'_\'';
    $fields = array('cs.section','ur.id');
    
    $sql_select = " SELECT ".$DB->sql_concat_join($concat, $fields)." AS id,
                          cs.section as sec,
                          ra.userid AS userid,
                          ".$DB->sql_fullname('ur.firstname', 'ur.lastname')." AS fullname,
                          ur.username,
                          ws.status,
                          ws.timecreated,
                          ws.timemodified,
                          ws.fixstatus ";
    $sql_from   = " FROM {context} co 
                    JOIN {role_assignments} ra ON ra.contextid = co.id and roleid = :roleid
                    JOIN {user} ur ON ur.id = ra.userid
                    JOIN {course_sections} cs ON cs.course = co.instanceid
                    LEFT JOIN {local_onattend_week_status} ws ON ws.courseid = cs.course and ws.section = cs.section and ur.id = ws.userid ";
    
    $sql_conditions[] = 'co.instanceid = :instanceid';
    $sql_conditions[] = 'co.contextlevel = contextlevel';
    $sql_conditions[] = 'cs.section <> :notsection';

    $sql_params['roleid'] = $DB->get_field('role', 'id', array('archetype' => 'student'));
    $sql_params['instanceid'] = $courseid;
    $sql_params['contextlevel'] = CONTEXT_COURSE;
    $sql_params['notsection'] = 0;
    
    $sql_where = ' WHERE '.implode(' AND ', $sql_conditions);
    $sql_orderby = ' ORDER BY ur.id, cs.section ASC ';
    
    $users = $DB->get_records_sql($sql_select.$sql_from.$sql_where.$sql_orderby, $sql_params);
    
    return $users;
}

/*
 * 해당 코스에 온라인출석부에서 사용되는 모든 section의 user 출석값을 반환 
 * 
 * @param int $userid           user id
 * @param int $section          주차
 * @param int $selected         기본 선택값
 * @return string               
 */
function local_onattendance_week_selectbox($userid, $section, $selected=0) {
//    $status_string = array(
//            0 =>'X',
//            1 =>'○',
//            2 =>'△'
//        );
    $status_string = array(
            0 =>'결석',
            1 =>'출석',
            2 =>'지각'
        );
    $key = $userid.'_'.$section;
    $selectname = "status[$key]";
    $selectbox = '<select class="week-status" name="'.$selectname.'">';
    
    foreach($status_string as $value=>$string) {
        $sel = '';
        if($selected == $value) {
            $sel = 'selected';
        }
        $selectbox .= '<option value="'.$value.'" '.$sel.'>'.$string.'</option>';
    }
    
    $selectbox .= '</select>';
    
    return $selectbox;
}

/*
 * 해당 코스에 온라인출석부 local_onattend_week_status 테이블의 status를 
 * local_onattend_cm_set 테이블의 해당 주차 값을 가져와 변경함
 * 
 * @param int $courseid         course table id
 * @param int|array $userid     user table id userid값이 0이면 등록된 모든 학생,
 * @return array               
 */
function local_onattendance_week_recalculate($courseid, $section, $userid = 0) {
    global $DB;
    
    $sql_params = array();
    if($userid != 0) {
        if(is_array($userid)) {
            list($sql_in, $sql_params) = $DB->get_in_or_equal($userid, SQL_PARAMS_NAMED, 'userid');
            $sql_conditions[] = 'ra.userid '.$sql_in;
        }else{
            $sql_conditions[] = ' ra.userid = :userid';
            $sql_params['userid'] = $userid;
        }
    }
    
    $concat='\'_\'';
    $fields = array('loc.cmid','ra.userid');
    
    $sql_select = " SELECT ".$DB->sql_concat_join($concat, $fields)." AS id,
                          ra.userid,
                          loc.cmid,
                          loc.aprogress AS alimit, 
                          los.aprogress AS progress, 
                          los.status ";
    $sql_from   = " FROM {local_onattend_cm_set} loc
                    JOIN {context} co ON co.instanceid = loc.courseid and co.contextlevel = :contextlevel
                    JOIN {role_assignments} ra ON ra.contextid = co.id and roleid = :roleid
                    LEFT JOIN {local_onattend_status} los ON los.cmid = loc.cmid and ra.userid = los.userid ";
    
    $sql_conditions[] = 'co.instanceid = :instanceid';
    $sql_conditions[] = 'loc.section = :section';
    $sql_conditions[] = 'loc.approval = :approval';

    $sql_params['roleid'] = $DB->get_field('role', 'id', array('archetype' => 'student'));
    $sql_params['contextlevel'] = CONTEXT_COURSE;
    $sql_params['instanceid'] = $courseid;
    $sql_params['section'] = $section;
    $sql_params['approval'] = 1;
    
    $sql_where = ' WHERE '.implode(' AND ', $sql_conditions);
    $sql_orderby = ' ORDER BY ra.userid, loc.cmid ASC ';
    
    $users = $DB->get_records_sql($sql_select.$sql_from.$sql_where.$sql_orderby, $sql_params);
    
    $userdata = array();
    foreach($users as $user) {
        if(is_null($user->progress) && empty($userdata[$user->userid])) {
            $userdata[$user->userid] = 0;
        } else {
            if($user->alimit > $progress) {
                $userdata[$user->userid] = 1;
            } else {
                $userdata[$user->userid] = 2;
            }
        } 
        
    }
    
    
    list($sql_in, $sql_params) = $DB->get_in_or_equal(array_keys($userdata), SQL_PARAMS_NAMED, 'userid');
    $sql = 'SELECT * FROM {local_onattend_week_status} WHERE courseid = :courseid AND section = :section AND userid '.$sql_in;
    $sql_params['courseid'] = $courseid;
    $sql_params['section'] = $section;
    $week_status = $DB->get_records_sql($sql, $sql_params);
  
    foreach($week_status as $status) {
        if( ($userdata[$status->userid] != $status->status) && ($status->fixstatus == 0)) {
            $status->status = $userdata[$status->userid];
            $DB->update_record('local_onattend_week_status', $status);
        }
        
        unset($userdata[$status->userid]);
    }
    
    foreach($userdata as $userid => $status) {
        $weekstatus = new Stdclass();
        $weekstatus->courseid = $courseid;
        $weekstatus->section = $section;
        $weekstatus->userid = $userid;
        $weekstatus->status = $status;
        $weekstatus->timecreated = time();
        $weekstatus->timemodified = time();
        $weekstatus->fixstatus = 0;
        
        $DB->insert_record('local_onattend_week_status', $weekstatus);
    }
}