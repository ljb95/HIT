<?php

/** board type notice */
define('BOARD_NOTICE', 1);
/** Course context level - one instances for each course */
define('BOARD_QNA', 2);



/**
 * Returns list of courses current $USER is enrolled in and can access
 *
 * - $fields is an array of field names to ADD
 *   so name the fields you really need, which will
 *   be added and uniq'd
 *
 * @param string|array $fields
 * @param string $sort
 * @param int $limit max number of courses
 * @return array
 */

function enrol_get_my_courses_by_my($total = NULL, $param = array(), $page=1, $limit=10, $fields = NULL, $sort = 'visible DESC,sortorder ASC', $userid = 0) {
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
        $courses = $DB->get_records_sql($sql, $params, board_jino_offset($page, $limit), $limit);
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

/**
 * 코스의 게시판 목록을 가져온다.
 * @param string|array $courses
 * @param string $type
 * @param int $limit max number of courses
 * @return array
 */

function board_my_courses_in_board($courses, $type="", $sort="jc.timemodified desc"){
    global $CFG, $DB;

    if (empty($courses) || !is_array($courses) || count($courses) == 0) {
        return null;
    }
    
    if(empty($type)){
        $type = BOARD_NOTICE;
    }
    
    if(!empty($page_param['page'])){
        $page = $page_param['page'];
    }
    
    if(!empty($page_param['perpage'])){
        $perpage = $page_param['perpage'];
    }
    
    list($coursessql, $params) = $DB->get_in_or_equal(array_keys($courses), SQL_PARAMS_NAMED, 'c0');
    
    $sql_like = '';
    if(!empty($parameter['searchval'])){
        $params['assignname'] = '%'.$parameter['searchval'].'%';
        $sql_like = ' AND '.$DB->sql_like('m.name', ':assignname', false);
    }
    
    $year = get_config('moodle', 'haxa_year');
    $term = get_config('moodle', 'haxa_term');
    
    $gigansql = '';
    if(!empty($year) && !empty($term)) {
        $params['year'] = $year;
        $params['term'] = $term;
        $gigansql = ' AND ((yc.year = 9999 AND yc.term = 0) OR (yc.year = :year AND (yc.term = :term or yc.term = 0))) ';
    }
    
    
    $params['type'] = $type;
    $sql = "select jc.*, c.fullname as coursename, yc.subject_id as subject_id
            from {jinotechboard} jc
            join {course} c on c.id = jc.course 
            join {lmsdata_class} yc on yc.course = jc.course
            WHERE jc.type=:type and jc.course $coursessql $sql_like $gigansql ";
    
    if(!empty($sort)) {
        $sql .= " ORDER BY ".$sort;
    }
    
    $rawmods = $DB->get_records_sql($sql, $params);
    return $rawmods;
}

/*
 * 게시글의 갯수를 반환한다
 * @param int $page
 * @param int $limit
 * @return int
 */

function board_get_contents_count($courses, $type = BOARD_NOTICE , $searchvalue="") {
    global $CFG, $DB, $USER;
    
    $params = array();
    $where = "";
    
    if (empty($courses) || !is_array($courses) || count($courses) == 0) {
        return 0;
    }
    
    list($coursessql, $params) = $DB->get_in_or_equal(array_keys($courses), SQL_PARAMS_NAMED, 'c0');
          
    if(!EmptyStr($searchvalue)) {
        $where = " and ".$DB->sql_like('jbc.title', ':searchvalue', false); 
        $params["searchvalue"] = '%'.$searchvalue.'%';
    }
    
    if($type == BOARD_NOTICE) {
        $where .= " and jbc.isnotice != :notice ";
        $params["notice"] = 1;
    }
    
    $sql = "select COUNT(*) 
            from {jinotechboard_contents} jbc
            join {jinotechboard} jb on jb.id = jbc.board
            where jb.type = '".$type."' and jb.course ".$coursessql.$where;
    
    return $DB->count_records_sql($sql, $params);
    
}

function board_get_contents($courses, $type = BOARD_NOTICE ,$page = 0, $perpage = 10, $searchvalue="") {
    global $CFG, $DB, $USER;

    if (empty($courses) || !is_array($courses) || count($courses) == 0) {
        return 0;
    }

    $offset = 0;
    if ($page != 0) {
            $offset = $page * $perpage;
    }

    $params = array();
    $where = "";

    list($coursessql, $params) = $DB->get_in_or_equal(array_keys($courses), SQL_PARAMS_NAMED, 'c0');

    if(!EmptyStr($searchvalue)) {
        $where = " and ".$DB->sql_like('jbc.title', ':searchvalue', false);
        $params["searchvalue"] = '%'.$searchvalue.'%';
    }

    if($type == BOARD_NOTICE) {
        $where .= " and jbc.isnotice != :notice ";
        $params["notice"] = 1;
    }
    $params['contextlevel'] = CONTEXT_MODULE;

    $sql = "select jbc.*,jb.name, jb.id as bid, co.fullname as coursename , us.firstname, us.lastname, fi.id as fileid
            from {jinotechboard_contents} jbc
            join {jinotechboard} jb on jb.id = jbc.board
            join {course} co on co.id = jb.course
            join {modules} mo ON mo.name = 'jinotechboard'
            join {course_modules} cm ON cm.course = co.id AND cm.instance = jb.id AND cm.module = mo.id
            join {context} ctx ON ctx.contextlevel = :contextlevel AND ctx.instanceid = cm.id
            left join {user} us on us.id = jbc.userid
            left join {files} fi on fi.contextid = ctx.id and fi.itemid = jbc.id and fi.source is not null
            where jb.type = '".$type."' and jb.course ".$coursessql.$where." order by jbc.ref DESC, jbc.step asc, jbc.timecreated DESC";

    return $DB->get_records_sql($sql, $params, $offset, $perpage);

}

function board_get_notice_contents($courses, $type = BOARD_NOTICE) {
    global $CFG, $DB, $USER;

    if (empty($courses) || !is_array($courses) || count($courses) == 0) {
        return 0;
    }

    $params = array();
    $where = "";

    list($coursessql, $params) = $DB->get_in_or_equal(array_keys($courses), SQL_PARAMS_NAMED, 'c0');

    $where = " and jbc.isnotice = :notice ";
    $params["notice"] = 1;
    $params['contextlevel'] = CONTEXT_MODULE;

    $sql = "select jbc.*, jb.id as bid, co.fullname as coursename , us.firstname, us.lastname, fi.id as fileid
            from {jinotechboard_contents} jbc
            join {jinotechboard} jb on jb.id = jbc.board
            join {course} co on co.id = jb.course
            join {modules} mo ON mo.name = 'jinotechboard'
            join {course_modules} cm ON cm.course = co.id AND cm.instance = jb.id AND cm.module = mo.id
            join {context} ctx ON ctx.contextlevel = :contextlevel AND ctx.instanceid = cm.id
            left join {user} us on us.id = jbc.userid
            left join {files} fi on fi.contextid = ctx.id and fi.itemid = jbc.id and fi.source is not null
            where jb.type = '".$type."' and jb.course ".$coursessql.$where." order by jbc.timecreated DESC ";

    return $DB->get_records_sql($sql, $params);

}

function board_get_access_content($current_content, $userid = 0) {
    global $USER, $DB;
   
    if($userid == 0) {
        $userid = $USER->id;
    }
    
    //권한을 주기 위한 작업
    if (!$cm = get_coursemodule_from_instance("jinotechboard", $current_content->board, $current_content->course)) {
        print_error('missingparameter');
    }
    
    $classname = context_helper::get_class_for_level(CONTEXT_MODULE);
    $contexts[$cm->id] = $classname::instance($cm->id);
    $context = $contexts[$cm->id];    
    
    if(!empty($current_content->isprivate)){
        
        if(has_capability('mod/jinotechboard:secretmanager',$context)) return true;

        if($current_content->id == $current_content->ref){
            if($current_content->userid == $userid){
                return true;
            }else{
                return false;
            }
        }else{
            $parent = $DB->get_record("jinotechboard_contents", array("id" => $current_content->ref));
            if($parent->userid == $userid){
                return true;
            }else{
                return false;
            }
        }
    }else{
        return true;
    }
    
}

/**
 * 디비 쿼리 할 때 페이지에 대한 offset 을 계산한다.
 * @param int $page
 * @param int $limit
 * @return int
 */
function board_jino_offset($page, $limit) {
    return $limit * ($page-1);
}

 function EmptyStr($str) {
        if(!isset($str)||$str==NULL||$str=='') return true;
        else return false;
} 
 
function board_print_paging_navbar($totalcount,$page_name, $page, $perpage, $baseurl, $params = null, $maxdisplay = 10) {
    global $CFG, $SITECFG;
    
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
            $currpage = $lastpage - $maxdisplay > 1 ? $lastpage-$maxdisplay : 1;
        }
    } else {
        $currpage = 1;
    }
    
    
    
    if($params == null) {
        $params = array();
    }
    
    $prevlink = '';
    if ($page > 1) {
        $params[$page_name] = $page - 1;
        $prevlink = '<span class="board-nav-prev">'.html_writer::link(new moodle_url($baseurl, $params), "<")."</span>";
    } else {
        $prevlink = '<span class="board-nav-prev"><a href="#"><</a></span>';
    }
    
    $nextlink = '';
     if ($page < $lastpage) {
        $params[$page_name] = $page + 1;
        $nextlink = '<span class="board-nav-next">'.html_writer::link(new moodle_url($baseurl, $params), '>', array('class'=>'board-nav-next'))."</span>";;
    } else {
        $nextlink = '<span class="board-nav-next"><a href="#">></a></span>';
    }
    
    
    echo '<div class="board-breadcrumbs">';
    
    $pagelinks[] = $prevlink;
    
    if ($currpage > 1) {
        $params[$page_name] = 1;
        $firstlink = html_writer::link(new moodle_url($baseurl, $params), 1);
        
        $pagelinks[] = $firstlink;
        if($currpage > 2) {
            $pagelinks[] = '...';
        }
    }
    
    $displaycount = 0;
    $pagelinks[] ="<ul>";
    while ($displaycount <= $maxdisplay and $currpage <= $lastpage) {
        if ($page == $currpage) {
            $pagelinks[] = '<li class="current"><a href="#">'.$currpage.'</a></li>';
        } else {
            $params[$page_name] = $currpage;
            $pagelink = '<li>'.html_writer::link(new moodle_url($baseurl, $params), $currpage).'</li>';
            $pagelinks[] = $pagelink;
        }
        
        $displaycount++;
        $currpage++;
    }
    
    if ($currpage - 1 < $lastpage) {
        $params[$page_name] = $lastpage;
        $lastlink = '<li>'.html_writer::link(new moodle_url($baseurl, $params), $lastpage).'</li>';
        
        if($currpage != $lastpage) {
            $pagelinks[] = '...';
        }
        $pagelinks[] = $lastlink;
    }
    $pagelinks[] ="</ul>";
    
    $pagelinks[] = $nextlink;
    
    echo implode('', $pagelinks);
    
    echo '</div>';
}

function board_editor_options($context, $contentid) {
        global $COURSE, $PAGE, $CFG; 
        // TODO: add max files and max size support
        $maxbytes = get_user_max_upload_file_size($context, $CFG->maxbytes, $CFG->maxbytes);
        return array(
            'maxfiles' => EDITOR_UNLIMITED_FILES,
            'maxbytes' => $maxbytes,
            'trusttext'=> true,
            'return_types'=> FILE_INTERNAL | FILE_EXTERNAL,
            'subdirs' => file_area_contains_subdirs($context, 'mod_jinotechboard', 'contents', $contentid)
        );
    }
?>
