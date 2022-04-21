<?php
define('LMSDATA_CLASS_DRIVE_EXECUTE', 0);
define('LMSDATA_CLASS_MERGE_EXECUTE', 2);
define('LMSDATA_CLASS_DELETE_EXECUTE', 4);
define('SYSTEM_START_YEAR',2013);
define('SYSTEM_MAX_WEEK',5);
define('ENROL_MENTOR_SUBJECT','MED7185');


/**
 * 
 * @global type $CFG
 */
function lmsdata_get_years() {
    global $DB;
        
    $current = date("Y");
    
    $max = $current + 1;
//    $min = $DB->get_field_sql("SELECT min(year) FROM {lmsdata_class}");
    
    $min = 2018;
    $years = array();
    for($i = $max; $i >= $min; $i--) {
        $years[$i] = $i;
    }
    
    return $years;
}
function lmsdata_get_mons($mo=""){     
    for($m=1;$m<=12;$m++){   
        if(strlen($m) == 1) $m = "0".$m;
        if($m == $mo){
            $date_month .= "<option value='$m' selected>$m</option>\n";
        }else{
            $date_month .= "<option value='$m'>$m</option>\n";
        }
    }   
    return $date_month;
}
/**
 * made by 종범
 * @global type $DB  DB lib
 * @return type 학과(부서) 배열 반환
 */
function lmsdata_get_dept(){     
   global $DB;
   // 지역 변수 function 안에서만 사용 . 
   $query = 'select distinct cc.id,cc.name from {lmsdata_class} lc 
              join {course_categories} cc on cc.id = lc.category';
   $depts = $DB->get_records_sql($query);
   return $depts;
}
function lmsdata_get_days($da){
    for($d=1;$d<=31;$d++){
        if(strlen($d) == 1) $d = "0".$d;
        if ($d == $da){
            $date_day.="<option value='$d' selected>$d</option>\n";
        }else{
            $date_day.="<option value='$d'>$d</option>\n";
        } 
    }
    return $date_day;
}

function lmsdata_get_terms() { 
        return array(
            '10' => '1학기',
            '11' => '여름학기',
            '20' => '2학기',
            '21' => '겨울학기'
        );
}

function lmsdata_get_terms2() { 
        return array(
            '010' => '정규1학기',
            '011' => '정규여름학기',
            '020' => '정규2학기',
            '021' => '정규겨울학기',
            '110' => '비정규1학기',
            '111' => '비정규여름학기',
            '120' => '비정규2학기',
            '121' => '비정규겨울학기',
        );
}

function lmsdata_get_hyears() {
        return array(
            '1' => '1',
            '2' => '2',
            '3' => '3',
            '4' => '4'
        );
}

function lmsdata_get_terms_sync() {
    $terms = array(
        '1' => '1 학기',
        '2' => '2 학기',
        '3' => '여름학기',
        '4' => '겨울학기'
    );
    
    return $terms;
}

function lmsdata_get_sync_tabs() {
    return array(
        array(
            'class' => 'black_btn',
            'text'  => '사용자',
            'page'  => 'user'),
        array(
            'class' => 'black_btn',
            'text'  => '강의',
            'page'  => 'course'),
        array(
            'class' => 'black_btn',
            'text'  => '강의참여자',
            'page'  => 'participant'),
        array(
            'class' => 'red_btn',
            'text'  => '설정',
            'page'  => 'config')
    );
}

/**
 * course에 등록된 userid, roleid 목록 반환
 * @param array $course course array, value = courseid 
 * @return {role_assignments} stdClass
 */
function get_courses_role_assignments($course){
    global $DB;
    
    list($sql_in, $assign_params) = $DB->get_in_or_equal($course, SQL_PARAMS_NAMED, 'id');
    
    $sql_where = " WHERE instanceid ".$sql_in;
    
    $assign_params['contextlevel'] = CONTEXT_COURSE;

    $sql_select  = "SELECT ra.id, ra.roleid, ra.userid, ra.modifierid, ro.shortname, ct.instanceid ";
    $sql_from    = " FROM {role_assignments} ra 
                     JOIN {role} ro on ro.id = ra.roleid
                     JOIN (
                            SELECT * FROM {context} ".$sql_where."  and contextlevel = :contextlevel
                          )ct ON ra.contextid = ct.id ";
    
    $assign_users = $DB->get_records_sql($sql_select.$sql_from, $assign_params);
    
    return $assign_users;
}

function set_assign_user($course, $user){
    global $CFG, $PAGE, $DB;
    
    require_once("$CFG->dirroot/enrol/locallib.php");
    
    $manager = new course_enrolment_manager($PAGE, $course);
    
    if($ues = $manager->get_user_enrolments($user->userid)) { 
        $manager->assign_role_to_user($user->roleid, $user->userid);
    } else { 
        $enrol = $DB->get_record('enrol', array('enrol'=>'manual', 'courseid'=>$course->id));
        $timestart = 0;

        if($user->shortname == 'student'){
            $timestart = $course->startdate - 604800;
        } 

        $timeend = 0;

        $instances = $manager->get_enrolment_instances();
        $plugins = $manager->get_enrolment_plugins();
        
        $instance = $instances[$enrol->id];
        $plugin = $plugins[$instance->enrol];

        $plugin->enrol_user($instance, $user->userid, $user->roleid, $timestart, $timeend);

    }
    
    return true;
}

function lmsdata_get_submission_assign_count($courses, $userid = 0) {
    global $DB, $USER;

    if (empty($courses) || !is_array($courses) || count($courses) == 0) {
        return 0;
    }
    
    list($coursessql, $params) = $DB->get_in_or_equal(array_keys($courses), SQL_PARAMS_NAMED, 'c0');
    
    if(!empty($userid)){
        $params['userid'] = $userid;
    }else{
        $params['userid'] = $USER->id;
    }
    
    $submitted_sql = " SELECT ai.id as assignid, su.status as submitted  
                       FROM {assign} ai
                       JOIN {course_modules} cm ON ai.id = cm.instance AND ai.course = cm.course AND cm.module = 1
                       LEFT JOIN 
                            (SELECT * FROM {assign_submission} WHERE userid = :userid ) su on ai.id = su.assignment
                       WHERE cm.visible = 1 AND ai.course $coursessql ";
    
    $submitted_arr = $DB->get_records_sql($submitted_sql, $params);
    if(!empty($submitted_arr)){
        $assign_count = 0; 
        $submitted_count = 0;
        foreach ($submitted_arr as $value) {
            if(!empty($value->assignid)){
                $assign_count++;
            }
            if(!empty($value->submitted)){
                $submitted_count++;
            }
        }
        $count = (int)($assign_count) - (int)($submitted_count); 
    }else{
        $count = 0;
    }
    return $count;
}

function enrol_get_my_courses_by_my_info($total = NULL, $param = array(), $page=1, $limit=10, $fields = NULL, $sort = 'visible DESC,sortorder ASC') {
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

    $coursefields = 'c.' .join(',c.', $fields);
    list($ccselect, $ccjoin) = context_instance_preload_sql('c.id', CONTEXT_COURSE, 'ctx');
    
    if (!empty($param['year'])) {
        $params['year'] = $param['year'];
        $wheres = array(" ci.shyy = :year ");
    }
    if (!empty($param['term'])) {
        $params['term'] = $param['term'];
        $wheres = array(" ci.shtm_dcd = :term ");
    }
    if (!empty($param['searchval'])) {
        $params['searchval'] = '%'.$param['searchval'].'%';
        $sql_like = $DB->sql_like('c.fullname', ':searchval', false);
        $wheres = array($sql_like);
    }
    
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
             WHERE $wheres
          $orderby";
    $params['userid']  = $USER->id;
    $params['active']  = ENROL_USER_ACTIVE;
    $params['enabled'] = ENROL_INSTANCE_ENABLED;
    $params['now1']    = round(time(), -2); // improves db caching
    $params['now2']    = $params['now1'];
    if($total == 'all'){
        $courses = $DB->get_records_sql($sql, $params);
    }else if($total == null){
        $courses = $DB->get_records_sql($sql, $params, jino_offset_lmsdata($page, $limit), $limit);
    }
    // preload contexts and check visibility
    foreach ($courses as $id=>$course) {
        context_instance_preload($course);
        if (!$course->visible) {
            if (!$context = get_context_instance(CONTEXT_COURSE, $id)) {
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

function jino_offset_lmsdata($page, $limit) {
    return $limit * ($page-1);
}

 /**
    * @param int $standard courseid
    * @param int $subject_list courseid
    * @param int $type 몰아넣기(0), 되돌리기(1), 분반몰아넣기(2), 교수자 강의삭제(3)
    * @param array $invisible subject_list의 course를 학생들에게 보여줄것인가? 보임(0), 숨김(1))
    * 
    * @return int lmsdata_class_drive_log table id
 */

function add_class_drive_log($standard, $subject_id, $type, $invisible = 0){
    global $DB, $USER;
    
    $class = new stdClass();
    
    $class->standard_id = $standard;
    $class->subject_id = $subject_id;
    $class->user_id = $USER->id;
    $class->invisible = $invisible;
    $class->type = $type;
    $class->timecreated = time();
    $class->timerestore = time();
    
    return $DB->insert_record('lmsdata_class_drive_log', $class); 
}

/**
 * lmsdata_course->hyear 에 있는 연속적인 학년값을 /로 구분하여 반환
 * @param string $hyear
 * 
 * @return string $hyear_str
*/
function get_hyear_str($hyear) {
    $hyear_len = strlen($hyear);
    $temp = array();
    for($i=0; $i < $hyear_len; $i++) {
        $temp[] = substr($hyear, $i, 1);
    }
    
    $hyear_str = implode('/', $temp);
    return $hyear_str;
}

/**
 * lmsdata_course->lectype 에 있는 강의타입을 한글 string으로 반환
 * @param string $lectype
 * 
 * @return string $lectype_str
*/
function get_lectype_str($lectype) {
    $lectype_str;
    switch($lectype) {
        case 1: // 학정번호
            $lectype_str = '강의';
            break;
        case 2: // 과정명
            $lectype_str = '실습';
            break;
        default:
            $lectype_str = '기타';
            break;
    }
    
    return $lectype_str;
}
/**
 * lmsdata_course->required_str 에 있는 종필을 한글 string으로 반환
 * @param string $required
 * 
 * @return string $required_str
*/
function get_required_str($required) {
    $required_str;
    switch($required) {
        case 1: // 학정번호
            $required_str = '전필';
            break;
        case 2: // 과정명
            $required_str = '전선';
            break;
        case 3:
            $required_str = '기타';
            break;
    }
    
    return $required_str;
}

/**
 * 브라우저로 메시지를 출력한다.
 * 메시지 끝에 '<br/>'을 붙여서 줄바꿈흘 한다.
 * @param string $message
 */
function siteadmin_println($message) {
    echo $message.'<br/>'."\n";
    
    siteadmin_flushdata();
}

/**
 * 출력 버퍼에 있는 내용을 브라우저로 보낸다.
 */
function siteadmin_flushdata() {
    while (ob_get_level() > 0) {
        ob_end_flush();
    }

    flush();

    ob_start();
}

function siteadmin_scroll_down() {
    echo '<script type="text/javascript">
    window.scrollTo(0, document.body.scrollHeight);
</script>';

    siteadmin_flushdata();
}

function get_weeknum($time) { 
    $week = date('w', mktime(0,0,0, date('n',$time), 1, date('Y',$time))); 
    return ceil(($week + date('j',$time) -1) / 7); 
} 

function local_popup_get_allcount($searchfield="", $searchvalue="") {
    global $CFG, $DB, $USER;
    
    $params = array();
    $where = "";
    
    if(!isNullOrEmptyStr2($searchvalue)){
        switch ($searchfield) {
            case 1: 
                $where = " title LIKE ?"; //
                $params[] = '%'.$searchvalue.'%';
                    break;
            case 2:
                    break;
            case 3:
                $where = " description LIKE ?"; 
                $params[] = '%'.$searchvalue.'%';
                break;
        }
        
    }
    
    return $DB->count_records_select ("popup", $where, $params);
}


function local_pupup_print_popups($sort, $searchfield="1", $searchvalue="", $page, $perpage, $totalcount){
    global $CFG;
     
    echo '<div class="writing_content">';
    echo '<table class="mylearning_table">
	<tr >
		<th class="header">'.get_string("no", 'local_popup').'</th>
		<th class="header">'.get_string("title", 'local_popup').'</th>
		<th class="header">'.get_string("period", 'local_popup').'</th>
		<th class="header">'.get_string("isactive", 'local_popup').'</th>'.
            '</tr>';
    $offset = 0;
    if ($page != 0) {
            $offset = $page * $perpage;
    }
    
    $popups = local_pupup_get_popups($sort, $searchfield, $searchvalue, $page, $perpage);
    $num = $totalcount - $offset;
    if(!$popups){
        echo "<tr><td colspan=4 align=center>".get_string("emptycontent", 'local_popup')."</td></tr>";
         //return;
    }else {
        $rowi = 0;
        
        foreach ($popups as $popup) {
            echo("<tr  class=\"body_order ".($rowi%2==1?" r1 ": "")." \">
		<td align=center>".$num."</td>
                <td align=left><div  style='text-align:left'><a href='./view.php?id=".$popup->id."'>".$popup->title."</a></div></td>
                <td align=center>".date("Y-m-d",$popup->timeavailable)."~".date("Y-m-d",$popup->timedue)."</td>
                <td align=center>". ($popup->isactive==0?get_string("inactive","local_popup"):get_string("active","local_popup")) ."</td>
               </tr>");
            $num-- ; 
            $rowi ++;
        }
        
    }
    echo "</table>
        
        <input type='button'  id='writebtn'  onclick='document.location.href =\"./write.php\"'  class='listmenu_list' value='".get_string('addnewpopup', 'local_popup')."' />
    </div>";
    
    
}

function local_popup_get_popups($sort, $searchvalue = null, $page, $perpage) { 
    
    global $CFG, $DB, $USER;
    
    $offset = 0;
    if ($page != 0) {
            $offset = $page * $perpage;
    }
    $where = '';
    $params = array();
    
    if(!$searchvalue){
        return $DB->get_records_select("popup",null, null,$sort,"*",$offset,$perpage);
    }else{
        $where .= $DB->sql_like('title', ':searchvalue');
        $params['searchvalue'] = '%'.$searchvalue.'%';
    } 
    return $DB->get_records_select("popup", $where, $params, $sort, "*", $offset, $perpage);
}

function local_popup_get_popups_count($searchvalue = null) {
    global $DB;
    
    $where = '';
    $params = array();
    if($searchvalue){
        $where .= $DB->sql_like('title', ':searchvalue');
        $params['searchvalue'] = '%'.$searchvalue.'%';
    } 
    return $DB->count_records_select('popup', $where, $params);
}

function local_pupup_get_paging_bar($totalcount, $page, $perpage, $baseurl, $maxdisplay=15, $separator="&nbsp;", $previousandnext = true, $prevpage, $nextpage) {

    $code = '';

    //If there are results (more than 1 page)
//    if ($totalcount > $perpage) {
        $code .= "<div class=\"pagingDiv\">";

        $maxpage = (int)(($totalcount-1)/$perpage);

        //Lower and upper limit of page
        if ($page < 0) {
            $page = 0;
        }
        if ($page > $maxpage) {
            $page = $maxpage;
        }

        //Calculate the window of pages
        $pagefrom = $page - ((int)($maxdisplay / 2));
        if ($pagefrom < 0) {
            $pagefrom = 0;
        }
        $pageto = $pagefrom + $maxdisplay - 1;
        if ($pageto > $maxpage) {
            $pageto = $maxpage;
        }

        //Some movements can be necessary if don't see enought pages
        if ($pageto - $pagefrom < $maxdisplay - 1) {
            if ($pageto - $maxdisplay + 1 > 0) {
                $pagefrom = $pageto - $maxdisplay + 1;
            }
        }

        //Calculate first and last if necessary
        $firstpagecode = '';
        $lastpagecode = '';
        if ($pagefrom > 0) {
            $firstpagecode = "$separator<a class=\"page\" href=\"{$baseurl}&page=0\">1</a>";
            if ($pagefrom > 1) {
                $firstpagecode .= "$separator...";
            }
        }
        if ($pageto < $maxpage) {
            if ($pageto < $maxpage -1) {
                $lastpagecode = "$separator...";
            }
            $lastpagecode .= "$separator<a class=\"page\" href=\"{$baseurl}&page=$maxpage\">".($maxpage+1)."</a>";
        }

        //Previous
        if ($page > 0 && $previousandnext) {
            $pagenum = $page - 1;
            $code .= "<span class=\"pagenavi\"><a href=\"{$baseurl}&page=$pagenum\">".$prevpage."</a></span>&nbsp;";
        } else {
            $code .= "<span class=\"pagenavi_disable\">".$prevpage."</span>&nbsp;";
        }

        //Add first
        $code .= $firstpagecode;

        $pagenum = $pagefrom;

        //List of maxdisplay pages
        while ($pagenum <= $pageto) {
            $pagetoshow = $pagenum +1;
            if ($pagenum == $page) {
                $code .= "$separator<span class=\"currentpage\">$pagetoshow</span>";
            } else {
                $code .= "$separator<a class=\"page\" href=\"{$baseurl}&page=$pagenum\">$pagetoshow</a>";
            }
            $pagenum++;
        }

        //Add last
        $code .= $lastpagecode;

        //Next
        if ($page < $maxpage && $previousandnext) {
            $pagenum = $page + 1;
            $code .= "&nbsp;&nbsp;<span class=\"pagenavi\"><a href=\"{$baseurl}&page=$pagenum\">".$nextpage."</a></span>";
        } else {
            $code .= "&nbsp;&nbsp;<span class=\"pagenavi_disable\">".$nextpage."</span>";
        }

        //End html
        $code .= "</div>";
//    }

    return $code;
}

 function isNullOrEmptyStr2($str) {
        if(!isset($str)||$str==NULL||$str=='') return true;
        else return false;
}
