<?php
require_once dirname(dirname(dirname (__FILE__))).'/config.php';
//require_once dirname(dirname(__FILE__)) . '/lib/page.php';
require_once dirname(dirname(dirname(__FILE__))) . '/course/lib.php';

global $DB,$SITECFG;

function print_paging_nav($totalcount, $page, $perpage, $baseurl, $params = null, $maxdisplay = 18, $paramname = 'page') {
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
            $currpage = $lastpage - $maxdisplay;
        }
    } else {
        $currpage = 1;
    }
    
    
    
    if($params == null) {
        $params = array();
    }
    
    $prevlink = '';
    if ($page > 1) {
        $params[$paramname] = $page - 1;
        $prevlink = html_writer::link(new moodle_url($baseurl, $params), '<img src="'.$CFG->wwwroot.'/siteadmin/img/pagination_left.png"/>', array('class'=>'next'));
    } else {
        $prevlink = '<a href="#" class="next"><img src="'.$CFG->wwwroot.'/siteadmin/img/pagination_left.png"/></a>';
    }
    
    $nextlink = '';
     if ($page < $lastpage) {
        $params[$paramname] = $page + 1;
        $nextlink = html_writer::link(new moodle_url($baseurl, $params), '<img src="'.$CFG->wwwroot.'/siteadmin/img/pagination_right.png"/>', array('class'=>'prev'));
    } else {
        $nextlink = '<a href="#" class="prev"><img src="'.$CFG->wwwroot.'/siteadmin/img/pagination_right.png"/></a>';
    }
    
    
    echo '<div class="pagination">';
    
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
            $params[$paramname] = $currpage;
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
   
    
    echo implode('&nbsp;', $pagelinks);
    
    echo '</div>';
}

$page_params = array();

$gubun = optional_param('gubun','',PARAM_ALPHA);
if ($gubun) {
    $page_params['gubun'] = $gubun;
}

$currpage     = optional_param('page', 1, PARAM_INT);
$perpage      = optional_param('perpage', 20, PARAM_INT);
$searchtext   = optional_param('searchtext', '', PARAM_TEXT);
$cata1        = optional_param('cata1', 0, PARAM_INT);
$cata2        = optional_param('cata2', 0, PARAM_INT);
$cata3        = optional_param('cata3', 0, PARAM_INT); //3차분류는 과정과 동일

$sql_select  = "SELECT mc.id, mc.fullname, mc.shortname, 
                lc.timestart, lc.timeend, lc.timeregstart, lc.timeregend, 
                lc.subject_id, lc.year, lc.term, lc.isreged, lc.prof_userid,
                ur.firstname, ur.lastname";

$sql_from    = " FROM {course} mc
                 JOIN {lmsdata_class} lc ON lc.course = mc.id
                 JOIN {course_categories} ca ON ca.id = mc.category 
                 LEFT JOIN {user} ur ON ur.id = lc.prof_userid ";

$sql_where   =  array();
$params = array();

$cata_path = '';
if($cata3) {
    $cata_path = $DB->get_field('course_categories', 'path', array('id'=>$cata3));
} else if($cata2) {
    $cata_path = $DB->get_field('course_categories', 'path', array('id'=>$cata2));
} else if($cata1) {
    $cata_path = $DB->get_field('course_categories', 'path', array('id'=>$cata1));
}

if(!empty($cata_path)) {
    $sql_where[]= $DB->sql_like('ca.path', ':category_path');
    $params['category_path'] = $cata_path.'%';;
    $page_params['category_path'] = $cata_path;
}

if(!empty($searchtext)) {    
    // 강의명
    $sql_where[] = $DB->sql_like('lc.kor_lec_name', ':kor_lec_name');
    $params['kor_lec_name'] = '%'.$searchtext.'%';
    $page_params['kor_lec_name'] = '%'.$searchtext.'%';
}

$sql_orderby = " ORDER BY lc.subject_id ASC, mc.timecreated DESC ";

if(!empty($sql_where)) {
    $sql_where = ' WHERE '.implode(' and ', $sql_where);
}else {
    $sql_where = '';
}

$courses = $DB->get_records_sql($sql_select.$sql_from.$sql_where.$sql_orderby, $params, ($currpage-1)*$perpage, $perpage);
$count_courses = $DB->count_records_sql("SELECT COUNT(*) ".$sql_from.$sql_where, $params);

?>

<html>
    <head>
        <title>발송대상추가</title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <link rel="stylesheet" type="text/css" href="<?php echo $CFG->wwwroot . '/siteadmin/css/style_lms_admin.css'; ?>" />
        <script src="<?php echo $CFG->wwwroot . '/siteadmin/manage/course_list.js'; ?>"></script>
        <script src="<?php echo $CFG->wwwroot . '/siteadmin/js/lib/jquery-1.11.2.min.js'; ?>"></script>
        <script src="<?php echo $CFG->wwwroot.'/siteadmin/js/common.js'; ?>"></script>
    </head>
    <body>
      
<form name="search_form">
<table cellpadding="0" cellspacing="0" class="detail">
    <tbody>
        <tr>
            <td class="field_title">카테고리</td>
            <td class="field_value">
               <select title="category01" name="cata1" id="course_search_cata1" onchange="cata1_changed(this);"  class="w_160">
                    <option value="0"><?php echo get_string('case1','local_lmsdata'); ?></option>
                    <?php
                        $cata1_select = " SELECT * FROM {course_categories} ";
                        $cata1_where = array('visible=1');

                        $cata1_where[] = 'depth = :depth ';
                        $cata1_params['depth'] = 1;
                        $sql_order = ' order by sortorder';

                        $cata1_sql = $cata1_select.' WHERE '.implode(' AND ', $cata1_where);
                        $cata1_arr = $DB->get_records_sql($cata1_sql.$sql_order, $cata1_params);
                        foreach($cata1_arr as $category1) {
                            $selected = "";
                            if($category1->id == $cata1) {
                               $selected = "selected";
                            } 
                            echo '<option value="'.$category1->id.'"  '.$selected.'>'.$category1->name.'</option>';
                        }
                    ?>
                </select>
                <select name="cata2" title="category02" id="course_search_cata2" onchange="cata2_changed(this)" class="w_160">
                <option value="0"><?php echo get_string('case2','local_lmsdata'); ?></option>
                <?php
                    if(!empty($cata1)) {
                        $category_sql = ' SELECT id,name FROM {course_categories} WHERE ';
                        $sql_where = array('visible=1');
                        $sql_where[] = 'parent =:parent';
                        $where = implode(" AND ", $sql_where);
                        $sql_order = ' order by sortorder';

                        $sql_params =array('parent'=>$cata1);

                        $catagories = $DB->get_records_sql($category_sql.$where.$sql_order, $sql_params);

                        foreach($catagories as $catagory2) {
                            $selected = "";
                            if($catagory2->id == $cata2) {
                               $selected = "selected";
                            } 
                            echo '<option value="'.$catagory2->id.'" '.$selected.'> '.$catagory2->name.'</option>';
                        }
                    }
                ?>
            </select>
            <select name="cata3" title="category03" id="course_search_cata3" class="w_160">
                <option value="0"><?php echo get_string('case3','local_lmsdata'); ?></option>
                <?php
                if ($cata1 && $cata2) {
                    $catagories = $DB->get_records('course_categories', array('visible' => 1, 'parent' => $cata2), 'sortorder', 'id, idnumber, name');
                    if (!empty($path_arr[3])) {
                        $cata3 = $path_arr[3];
                    }
                    foreach ($catagories as $catagory) {
                        $selected = '';
                        if ($catagory->id == $cata3) {
                            $selected = ' selected';
                        }
                        echo '<option value="' . $catagory->id . '"' . $selected . '> ' . $catagory->name . '</option>';
                    }
                }
                ?>
            </select>
            </td>
        </tr>
        <tr>
            <td class="field_title">강좌명</td>
            <td class="field_value">
                <input type="text" title="search" name="searchtext" value="<?php echo $searchtext; ?>" placeholder="<?php echo get_string('search_placeholder','local_lmsdata'); ?>"  class="search-text"/>
                <input type="submit" class="blue_btn" value="검색" style="float: right"/>
            </td>
        </tr>
    </tbody>
</table>
</form>
        
<form name="frm_list" id="frm_list">
<table cellpadding="0" cellspacing="0">

    <tbody>

        <tr>
            <th width="5%"><input type="checkbox" name="allchk" value="1" onclick="list_all_check(this,this.form.chkbox);"/></th>
            <th width="10%">년도</th>
            <th width="10%">학기</th>
            <th width="30%">강좌명</th>
            <th width="20%">교수명</th>
        </tr>
        <?php
        if ($count_courses > 0) {
            foreach ($courses as $course) {
                $prof_name = $course->prof_userid ? fullname($course):'-';
                echo '<tr>';
                echo '<td><input type="checkbox" name="chkbox" value="'.$course->id .';'.$course->fullname.'"/></td>';
                echo '<td>'.$course->year.'</td>';
                echo '<td>'.$course->term.'</td>';
                echo '<td>'.$course->fullname.'</td>';
                echo '<td>'.$prof_name.'</td>';
                echo '</tr>';
            }
        }
        ?>

    </tbody>

</table>
    
</form>

<?php
print_paging_nav($count_courses, $currpage, $perpage, 'email_select2.php', $page_params);
?>

</body>
</html>
<script>
    function list_all_check(all,chk){
        for(var i=0;i<chk.length;i++){
            if(all.checked==true){
                chk[i].checked = true;
            }else{
                chk[i].checked = false;
            }
        }
    }
</script>