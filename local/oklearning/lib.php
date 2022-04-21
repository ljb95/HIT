<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

define('COURSE_TYPE', '2'); //이러닝과정 구분값

function local_oklearning_get_all_courses($categoryid, $param, $page=1, $perpage=10, $sort=" c.fullname ASC") {
   
    global $CFG, $DB;

    $params = array();
    
    $params['categoryid'] = '%'.$categoryid.'%';
    
    $sql_like = $DB->sql_like('cc.path', ':categoryid', false);
    
    
    $sql = "select c.*, cc.name as categoryname, er.id as enrolid            
            from {course_categories} cc
            join {course} c on cc.id=c.category
            left join m_enrol er on er.courseid = c.id and er.enrol='apply'  
            where ".$sql_like." ";
    
    if(!empty($param['searchval'])){
        $params['searchval'] = '%'.$param['searchval'].'%';
        if($param['searchfield'] == 1){
            $sql .= ' AND '.$DB->sql_like('c.fullname', ':searchval', false);
        }else if($param['searchfield'] == 2){
            $sql .= ' AND '.$DB->sql_like('c.fullname', ':searchval', false);
        } else if($param['searchfield'] == 3){
            $sql .= ' AND '.$DB->sql_like('c.fullname', ':searchval', false);	
        } 
    }
    
    if(!empty($param['profess'])){
        $params['profess'] = $param['profess'];
        $sql .= ' AND ci.lt_prof_no = :profess';
    }
    
    $sql .= " order by ";
    if($page == 0){
        $courses = $DB->get_records_sql($sql.$sort, $params);
    }else{
        $courses = $DB->get_records_sql($sql.$sort, $params, local_oklearning_offset($page, $perpage), $perpage);
    }
    
    
    return $courses;
}

function local_oklearning_category($categoryid){
   global $DB;
    
//    $year = get_config('local_haxa', 'year');
//    $term = get_config('local_haxa', 'term');
    
//    $term_ko = array("10"=>"1학기", "15"=>"여름학기", "20"=>"2학기", "25"=>"겨울학기");
    
    $sql =  "select * from {course_categories} 
             where parent IN (
                select id from {course_categories} 
                where parent = (select id from {course_categories} where name = '$year') and name='$term_ko[$term]')";
    
    $campus = $DB->get_records_sql($sql);
    
    return $campus;
}

/**
 * 디비 쿼리 할 때 페이지에 대한 offset 을 계산한다.
 * @param int $page
 * @param int $limit
 * @return int
 */

function local_oklearning_offset($page, $limit) {
    return $limit * ($page-1);
}


function local_oklearning_print_paging_navbar($totalcount,$page_name, $page, $perpage, $baseurl, $params = null, $maxdisplay = 10) {
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
    $pagelinks[] ="<ul>";
    
    if ($currpage > 1) {
        $params[$page_name] = 1;
        $firstlink = '<li>'.html_writer::link(new moodle_url($baseurl, $params), 1).'</li>';
        
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
            $pagelinks[] = '<li>...</li>';
        }
        $pagelinks[] = $lastlink;
    }
    $pagelinks[] ="</ul>";
    
    $pagelinks[] = $nextlink;
    
    echo implode('', $pagelinks);
    
    echo '</div>';
}

function local_oklearning_print_paging_navbar_script($totalcount, $page, $perpage, $baseurl, $maxdisplay = 10) {
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

function local_oklearning_set_assign_user($courseid, $userid, $status, $rolename){
    global $CFG, $PAGE, $DB;
    
    require_once("$CFG->dirroot/enrol/locallib.php");
    
    $role = $DB->get_record('role', array('shortname' => $rolename));
    $course = $DB->get_record('course', array('id' => $courseid));
    $manager = new course_enrolment_manager($PAGE, $course);    
    
    if(!$ues = $manager->get_user_enrolments($userid)) {
        $enrol = $DB->get_record('enrol', array('enrol'=>'self', 'courseid'=>$course->id));
        $timestart = $course->startdate;

        if($rolename == 'editingteacher' || $rolename == 'teacher'){
            $timestart = time();
        } 

        $timeend = 0;   

        $instances = $manager->get_enrolment_instances();
        $plugins = $manager->get_enrolment_plugins();
        
        $instance = $instances[$enrol->id];
        $plugin = $plugins[$instance->enrol];

        $plugin->enrol_user($instance, $userid, $role->id, $timestart, $timeend, $status);

    }
    
    return true;
}

function local_oklearning_set_unassign_user($courseid, $userid) {
    
    global $CFG, $PAGE, $DB;
    
    require_once("$CFG->dirroot/enrol/locallib.php");
    
    $course = $DB->get_record('course', array('id' => $courseid));
    $manager = new course_enrolment_manager($PAGE, $course);
    
    if($ues = $manager->get_user_enrolments($userid)) { 
        $enrol = $DB->get_record('enrol', array('enrol'=>'self', 'courseid'=>$course->id));
        $instances = $manager->get_enrolment_instances();
        $plugins = $manager->get_enrolment_plugins();
        
        $instance = $instances[$enrol->id];
        $plugin = $plugins[$instance->enrol];

        $plugin->unenrol_user($instance, $userid);
        
    }
    return true;
}

function delete_enrol_user($enrolid, $userid) {
    
    global $CFG, $PAGE, $DB;
    
    require_once("$CFG->dirroot/enrol/locallib.php");
    
    $instanceid = $DB->get_record('enrol', array('id'=>$enrolid), '*', MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $instanceid->courseid));
    $manager = new course_enrolment_manager($PAGE, $course);
    
    if($ues = $manager->get_user_enrolments($userid)) { 
        $enrol = $DB->get_record('enrol', array('id'=>$enrolid));
        $instances = $manager->get_enrolment_instances();
        $plugins = $manager->get_enrolment_plugins();
        
        $instance = $instances[$enrol->id];
        $plugin = $plugins[$instance->enrol];

        $plugin->unenrol_user($instance, $userid);
        
    }
    return true;
}

function update_course_overviewfile($data) {
    global $DB;
    $course = get_course($data->id);
    $oldcourse = course_get_format($data->id)->get_course();
    $context = context_course::instance($oldcourse->id);
    if ($overviewfilesoptions = course_overviewfiles_options($course)) {
        $data = file_postupdate_standard_filemanager($data, 'overviewfiles', $overviewfilesoptions, $context, 'course', 'overviewfiles', 0);
    }

    //$courseimage = new course_in_list($course);
    //$contentimages = "";
    //$fs = get_file_storage();
   // foreach ($courseimage->get_course_overviewfiles() as $file) {

     //  $conid = $file->get_contextid();
      // $file_record = array('contextid' => $file->get_contextid(), 'component' => $file->get_component(), 'filearea' => $file->get_filearea(),'itemid' => $file->get_itemid(), 'filepath' => $file->get_filepath(),'filename' => "f_".$file->get_filename(), 'userid' => $file->get_userid());
      // $t_file = $fs->convert_image($file_record, $file, 320, 0, true);
     //  $th_file->id = $t_file->get_id();
     //  $th_file->filename= $file->get_filename();
      // $th_file->contextid = $file->get_contextid();
     //  $DB->update_record('files',$th_file); 
     //   $DB->delete_records('files',array('id'=>$file->get_id()));
   // }
}

