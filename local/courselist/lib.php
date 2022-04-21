<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
/** board type notice */
define('COURSE_REGULAR', 1);
/** Course context level - one instances for each course */
define('COURSE_IRREGULAR', 2);

define('SELF_COURSE_CATEGORY', 'oklass_irregular');

/**
 * 2014.11.12 최현수
 * 등록된강의 갯수를 반환한다. 
 * /lib/coursecatlib.php 에 get_course()
 * 동기화 course 테이블과 join 필요
 *
 */

function local_get_all_courses($categoryid, $param, $page=1, $perpage=10, $sort=" c.fullname ASC") {
   
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
        $courses = $DB->get_records_sql($sql.$sort, $params, local_courselist_offset($page, $perpage), $perpage);
    }
    
    
    return $courses;
}

function local_courselist_category($categoryid){
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

function local_courselist_offset($page, $limit) {
    return $limit * ($page-1);
}


function courselist_print_paging_navbar($totalcount,$page_name, $page, $perpage, $baseurl, $params = null, $maxdisplay = 10) {
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

function courselist_print_paging_navbar_script($totalcount, $page, $perpage, $baseurl, $maxdisplay = 10) {
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
