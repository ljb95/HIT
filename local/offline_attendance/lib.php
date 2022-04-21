<?php

/** offline_attendance 출석 최고점수 default 값 */
define('LOCAL_ATTENDANCE_GRADE_ITEM_IDNUMBER', 'oklass_offline_attendancebook');

function local_offline_attendance_drow_selectbox($max, $min, $gap, $selectval = null, $options = array()) {
    
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

function local_offline_attendance_dates($courseid) {
    global $DB;
    
    $sql_select = ' SELECT timedate ';
    $sql_from = ' FROM {local_off_attendance_section} ';
    $sql_where = ' WHERE courseid = :courseid ';
    $sql_groupby = ' GROUP BY timedate ';
    $sql_orderby = ' ORDER BY timedate ASC ';
    
    $param = array('courseid' => $courseid);
    
    $timedates = $DB->get_records_sql($sql_select.$sql_from.$sql_where.$sql_groupby.$sql_orderby, $param);
    
    $datearray = array();
    foreach($timedates as $time) {
        $unixtime = $time->timedate;
        $date = date('Y-m-d', $unixtime);
        $datearray[$unixtime] = $date;
    }
    $nowdate = date('Y-m-d', time());
    $now = strtotime($nowdate);
    $datearray[$now] = date('Y-m-d', $now);
    
    return $datearray;
}

function offattendance_print_paging_navbar_script($totalcount, $page, $perpage, $baseurl, $maxdisplay = 10) {
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


function local_offline_attendance_log($statusid, $userid, $ostatus, $nstatus) {
    global $DB;
    
    $log = new stdClass();
    $log->statusid = $statusid;
    $log->userid = $userid;
    $log->ostatus = $ostatus;
    $log->nstatus = $nstatus;
    $log->timecreated = time();
    
    $DB->insert_record('local_off_attendance_log', $log);
}


