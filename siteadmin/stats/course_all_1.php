<?php 
error_reporting(E_ALL);
ini_set("display_errors", 1);

require (dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once dirname(dirname (__FILE__)).'/lib/paging.php';
require_once dirname(dirname (__FILE__)).'/lib.php';



// Check for valid admin user - no guest autologin
require_login(0, false);
if (isguestuser()) {
    $SESSION->wantsurl = (string)new moodle_url('/siteadmin/stats/course_all.php');
    redirect(get_login_url());
}
$context = context_system::instance();
require_capability('moodle/site:config', $context);

$currpage     = optional_param('page', 1, PARAM_INT);
$perpage      = optional_param('perpage', 20, PARAM_INT);
$year         = optional_param('year', 0, PARAM_INT);
$term         = optional_param('term', 0, PARAM_INT);
$search       = optional_param('search', 3, PARAM_INT);
$searchtext   = optional_param('searchtext', '', PARAM_TEXT);
$cata1        = optional_param('cata1', 0, PARAM_INT);
$cata2        = optional_param('cata2', 0, PARAM_INT);
$cata3        = optional_param('cata3', 0, PARAM_INT);
$coursetype   = optional_param('coursetype', 0, PARAM_INT);

// 현재 년도, 학기
if(!$year) {
    $year = get_config('moodle', 'haxa_year'); 
}
if(!$term) {
    $term = get_config('moodle', 'haxa_term');
}

$sql_select  = "SELECT mc.id, mc.fullname, mc.shortname
     , yc.subject_id, yc.isnonformal
     , mu.firstname, mu.lastname
     , mu.USERNAME
     , yu.univ
     , yu.major
     , yc.ohakkwa as ohakkwa
     , ca.name as category_name
     ,(SELECT COUNT(*) 
       FROM {role_assignments} ra
        JOIN {role} ro ON ra.roleid = ro.id
        JOIN {context} ctx ON ra.contextid = ctx.id
        JOIN {course} co ON ctx.instanceid = co.id AND contextlevel = :contextlevel1
        WHERE co.id = mc.id
        AND ro.id = 3) as editingteacher
     ,(SELECT COUNT(*) 
        FROM {role_assignments} ra
        JOIN {role} ro ON ra.roleid = ro.id
        JOIN {context} ctx ON ra.contextid = ctx.id
        JOIN {course} co ON ctx.instanceid = co.id AND contextlevel = :contextlevel2
        WHERE co.id = mc.id
        AND ro.id = 5) as student
       ,(SELECT COUNT(*) 
        FROM {role_assignments} ra
        JOIN {role} ro ON ra.roleid = ro.id
        JOIN {context} ctx ON ra.contextid = ctx.id
        JOIN {course} co ON ctx.instanceid = co.id AND contextlevel = :contextlevel3
        WHERE co.id = mc.id
        AND ro.id = 4) as teacher
        ,(SELECT COUNT(*) 
        FROM {role_assignments} ra 
        JOIN {role} ro ON ra.roleid = ro.id 
        JOIN {context} ctx ON ra.contextid = ctx.id 
        JOIN {course} co ON ctx.instanceid = co.id AND contextlevel = :contextlevel4 
        WHERE co.id = mc.id 
        AND ro.id = 41) as auditor 
   ,(SELECT count(DISTINCT jc.id)
        from m_jinotechboard_comments jc
        left JOIN m_user u ON u.id = jc.userid
        left JOIN {role_assignments} ra ON ra.USERID = u.id 
        left JOIN {role} ro ON ra.roleid = ro.id 
        WHERE jc.course = mc.id and ro.id=3) as editingteacher_comment
   ,(SELECT count(DISTINCT jc.id)
        from m_jinotechboard_comments jc
        left JOIN m_user u ON u.id = jc.userid
        left JOIN {role_assignments} ra ON ra.USERID = u.id 
        left JOIN {role} ro ON ra.roleid = ro.id 
        WHERE jc.course = mc.id and ro.id=5) as student_comment
  ,(SELECT count(DISTINCT jc.id)
        from m_jinotechboard_contents jc
        left JOIN m_user u ON u.id = jc.userid
        left JOIN {role_assignments} ra ON ra.USERID = u.id 
        left JOIN {role} ro ON ra.roleid = ro.id 
        WHERE jc.course = mc.id and ro.id=3) as editingteacher_content
   ,(SELECT count(DISTINCT jc.id)
        from m_jinotechboard_contents jc
        left JOIN m_user u ON u.id = jc.userid
        left JOIN {role_assignments} ra ON ra.USERID = u.id 
        left JOIN {role} ro ON ra.roleid = ro.id 
        WHERE jc.course = mc.id and ro.id=4) as teacher_content
   ,(SELECT count(DISTINCT jc.id)
        from m_jinotechboard_contents jc
        left JOIN m_user u ON u.id = jc.userid
        left JOIN {role_assignments} ra ON ra.USERID = u.id 
        left JOIN {role} ro ON ra.roleid = ro.id 
        WHERE jc.course = mc.id and ro.id=5) as student_content
   ,(SELECT count(DISTINCT jc.id)
        from {jinotechboard_contents} jc
        left JOIN {user} u ON u.id = jc.userid
        left JOIN {role_assignments} ra ON ra.USERID = u.id 
        left JOIN {role} ro ON ra.roleid = ro.id 
        WHERE jc.course = mc.id and ro.id=41) as auditor_content "; 
$sql_from    = " FROM {course} mc 
JOIN {lmsdata_class} yc ON yc.course = mc.id 
JOIN {course_categories} ca ON ca.id = mc.category 
LEFT JOIN {user} mu ON mu.id = yc.prof_userid 
LEFT JOIN {lmsdata_user} yu ON yu.userid = mu.id ";

$conditions = array('yc.isnonformal = :coursetype');
$page_params = array();
$param = array(
    'coursetype'=>$coursetype,
    'contextlevel1'=>CONTEXT_COURSE,
    'contextlevel2'=>CONTEXT_COURSE,
    'contextlevel3'=>CONTEXT_COURSE,
    'contextlevel4'=>CONTEXT_COURSE
);

if($coursetype == 0){
   $conditions[] = "(yc.year = :year AND yc.term = :term)";
   $param['year'] = $year;
   $param['term'] = $term;
}

if(!empty($searchtext)) {
    switch($search) {
        case 1: //강의코드
            $conditions[] = $DB->sql_like('yc.subject_id', ':subject_id');
            $param['subject_id'] = '%'.$searchtext.'%';
            break;
        case 2: // 교수명
            $conditionname = array();

            $conditionname[] = $DB->sql_like('u.firstname', ':firstname', false);
            $conditionname[] = $DB->sql_like('u.lastname', ':lastname', false);
            $conditionname[] = $DB->sql_like($DB->sql_fullname('u.firstname', 'u.lastname'), ':fullname', false);
            $conditionname[] = $DB->sql_like($DB->sql_fullname('u.lastname', 'u.firstname'), ':fullname1', false);
            $conditionname[] = $DB->sql_like($DB->sql_concat('u.firstname', 'u.lastname'), ':fullname2', false);
            $conditionname[] = $DB->sql_like($DB->sql_concat('u.lastname', 'u.firstname'), ':fullname3', false);
            $conditionname[] = $DB->sql_like('u.username', ':username', false);
            $conditions[] = '('.implode(' OR ', $conditionname).')';
            $param['firstname'] = '%'.$searchtext.'%';
            $param['lastname'] = '%'.$searchtext.'%';
            $param['fullname'] = '%'.$searchtext.'%';
            $param['fullname1'] = '%'.$searchtext.'%';
            $param['fullname2'] = '%'.$searchtext.'%';
            $param['fullname3'] = '%'.$searchtext.'%';
            $param['username'] = '%'.$searchtext.'%';
            break;
        case 3; // 강의명
            $conditions[] = $DB->sql_like('mc.fullname', ':course_name');
            $param['course_name'] = '%'.$searchtext.'%';
            break;
        default:
            break;
    }
}

$cata_path = '';
if($cata3) {
    $cata_path = $DB->get_field('course_categories', 'path', array('id'=>$cata3));
} else if($cata2) {
    $cata_path = $DB->get_field('course_categories', 'path', array('id'=>$cata2));
} else if($cata1) {
    $cata_path = $DB->get_field('course_categories', 'path', array('id'=>$cata1));
}
if(!empty($cata_path)) {
    $conditions[] = $DB->sql_like('ca.path', ':category');
    $param['category'] = $cata_path.'%';;
}

$sql_where = '';
if($conditions) $sql_where = ' WHERE '.implode(' AND ',$conditions);
//$sql_orderby = " ORDER BY mc.fullname";

$courses = $DB->get_records_sql($sql_select.$sql_from.$sql_where, $param, ($currpage-1)*$perpage, $perpage);
$count_courses = $DB->count_records_sql("SELECT COUNT(*) ".$sql_from.$sql_where, $param);


$js = array(
    $CFG->wwwroot.'/siteadmin/manage/course_list.js'
);
?>

<?php include_once ('../inc/header.php');?>
<div id="contents">
    <?php include_once ('../inc/sidebar_stats.php');?>
    <div id="content">
        <h3 class="page_title"><?php echo get_string('stats_allcourselist', 'local_lmsdata'); ?></h3>
        <p class="page_sub_title"> <?php echo get_string('stats_longtext5', 'local_lmsdata'); ?></p>
        
        <div class="down_area">
             <input type="submit" onclick="course_all_excel(); return false;" class="red_btn" value="<?php echo get_string('stats_longtext4', 'local_lmsdata'); ?> (*.xls)" style="float:left;"/>
             <p style="float:left; margin: 4px 0 0 10px;"><?php echo get_string('stats_longtext2', 'local_lmsdata'); ?></p>
        </div> <!--Down Area End-->
        
        <form name="" id="course_search" class="search_area" action="course_all.php" method="get">
            <input type="hidden" name="page" value="1" />
            <input type="hidden" name="coursetype" value="<?php echo $coursetype;?>" />
            <?php if($coursetype==0){?>
            <label><?php echo get_string('stats_years', 'local_lmsdata'); ?> &nbsp;&nbsp;</label>
            <select name="year" class="w_260" style="margin:5px 20px 5px 0;">
                <option value="0"> - <?php echo get_string('contents_now', 'local_lmsdata'); ?> -</option>
                <?php 
                $years = lmsdata_get_years();
                foreach($years as $v=>$y) {
                    $selected = '';
                    if($v == $year) {
                        $selected = ' selected';
                    }
                    echo '<option value="'.$v.'"'.$selected.'> '.$y.'</option>';
                }
                ?>
            </select>
            
            <label><?php echo get_string('stats_terms', 'local_lmsdata'); ?></label>
            <select name="term" class="w_260" style="margin:5px 20px 5px 0;">
                <option value="0"> - <?php echo get_string('stats_nowterm', 'local_lmsdata'); ?> -</option>
                <?php 
                $terms = lmsdata_get_terms();
                foreach($terms as $v=>$t) {
                    $selected = '';
                    if($v == $term) {
                        $selected = ' selected';
                    }
                    echo '<option value="'.$v.'"'.$selected.'> '.$t.'</option>';
                }
                ?>
            </select>
            
            <br>
            <?php }?>
            <label><?php echo get_string('stats_classification', 'local_lmsdata'); ?> &nbsp;&nbsp</label>
            <select name="cata1" id="course_search_cata1" onchange="cata1_changed(this);"  class="w_260" style="margin:5px 20px 5px 0;">
                <option value="0"> - <?php echo get_string('contents_all', 'local_lmsdata'); ?> -</option>
                <?php
                $category_sql = ' SELECT id, idnumber, name FROM {course_categories} WHERE ';
                $sql_where = array();
                $sql_where[] = 'visible =:visible';
                $sql_where[] = 'parent =:parent';
                $sql_where[] = $DB->sql_like('idnumber', ':idnumber');
                $where = implode(" AND ", $sql_where);
                
                $sql_params =array('visible'=>1, 'parent'=>0, 'idnumber'=>'SUNGKYUL%');
                
                $sortorder = ' ORDER BY name asc ';
                $catagories = $DB->get_records_sql($category_sql.$where.$sortorder, $sql_params);
                
                foreach($catagories as $catagory) {
                    $selected = '';
                    if($catagory->id == $cata1) {
                        $selected = ' selected';
                    }
                    echo '<option value="'.$catagory->id.'"'.$selected.'> '.$catagory->name.'</option>';
                }
                ?>
            </select>
            
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            <?php if($coursetype==0){?><input type="radio" name="search" value="1"<?php if($search == 1) {?> checked<?php } ?>/><?php echo get_string('course_code', 'local_lmsdata'); ?><?php }?>
            <input type="radio" name="search" value="2"<?php if($search == 2) {?> checked<?php } ?>/><?php echo get_string('teachername', 'local_lmsdata'); ?>
            <input type="radio" name="search" value="3"<?php if($search == 3) {?> checked<?php } ?>/><?php echo get_string('course_name', 'local_lmsdata'); ?>
            <input type="text" name="searchtext" value="<?php echo $searchtext; ?>" placeholder=" <?php echo get_string('stats_search', 'local_lmsdata'); ?> " class="w_260" style="color: #8E9094; margin:0 0 5px 15px;" />
            <input type="submit" class="blue_btn" value="<?php echo get_string('stats_search', 'local_lmsdata'); ?>" onclick="#" style="margin:0 0 5px 5px;"/>          
        </form><!--Search Area2 End-->
        <p style="float:left; color:red; margin-bottom: 5px;"><?php echo get_string('stats_longtext3', 'local_lmsdata'); ?></p>
        <table>
            <tr>
                <th rowspan = "2"><?php echo get_string('number', 'local_lmsdata'); ?></th>
                <th rowspan = "2"><?php echo get_string('stats_classification', 'local_lmsdata'); ?></th>
                <?php if($coursetype==0){?><th rowspan = "2"><?php echo get_string('course_code', 'local_lmsdata'); ?></th><?php }?>
                <th rowspan = "2"><?php echo get_string('course_name', 'local_lmsdata'); ?></th>
                <th rowspan = "2"><?php echo get_string('teachername', 'local_lmsdata'); ?>(ID)</th>
                <th rowspan = "2"><?php echo get_string('stats_belong', 'local_lmsdata'); ?></th>
                <th rowspan = "2"><?php echo get_string('teacher', 'local_lmsdata'); ?></th>
                <th rowspan = "2"><?php echo get_string('stats_assistant', 'local_lmsdata'); ?></th>
                <th rowspan = "2"><?php echo get_string('stats_student', 'local_lmsdata'); ?></th>
                <th rowspan = "2"><?php echo get_string('stats_auditor', 'local_lmsdata'); ?></th>
                <th rowspan = "2"><?php echo get_string('stats_learningactivity', 'local_lmsdata'); ?></th> 
                <th rowspan = "2"><?php echo get_string('stats_electronicboard', 'local_lmsdata'); ?></th>
                <th rowspan = "2"><?php echo get_string('stats_assignment', 'local_lmsdata'); ?></th>
                <th colspan = "3" ><?php echo get_string('stats_boardread', 'local_lmsdata'); ?><br></th>
                <th colspan = "3" ><?php echo get_string('stats_boardwrite', 'local_lmsdata'); ?><br></th>
                <th rowspan = "2">코멘트<br><?php echo get_string('student', 'local_lmsdata'); ?></th>
                <th rowspan = "2">코멘트<br><?php echo get_string('teacher', 'local_lmsdata'); ?></th>
            </tr>
            <tr>
                <th><?php echo get_string('stats_student', 'local_lmsdata'); ?></th>
                <th><?php echo get_string('stats_assistant', 'local_lmsdata'); ?></th>
                <th><?php echo get_string('teacher', 'local_lmsdata'); ?></th>
                <th><?php echo get_string('stats_student', 'local_lmsdata'); ?></th>
                <th><?php echo get_string('stats_assistant', 'local_lmsdata'); ?></th>
                <th><?php echo get_string('teacher', 'local_lmsdata'); ?></th>
            </tr>
            <?php
            if($count_courses === 0) { ?>
            <tr>
                <td colspan="24">강의가 없습니다.</td>
            </tr>
            <?php } else {
            $startnum = $count_courses - (($currpage - 1) * $perpage);
            
            foreach($courses as $course) { 
                $submitted_count = lmsdata_get_submission_assign_count($course);
                $sub_exname = explode("-", $course->subject_id);
                $sub_name = "";
                if(!empty($course->subject_id)){
                    if(!empty($sub_exname[0])) {
                        $sub_name = $sub_exname[0];
                    } else {
                         $sub_name = $course->subject_id;
                    }
                } else {
                    $sub_name = "-";
                }
                    
            ?>
            <tr>
                <td><?php echo $startnum--; ?></td>
                <td style='width:100px; text-overflow:ellipsis;'><?php echo $course->category_name; ?></td>
                <?php if($coursetype==0){?><td><?php echo $course->subject_id; ?></td><?php }?>
                <td class="text-left"><a href="<?php echo $CFG->wwwroot.'/course/view.php?id='.$course->id; ?>"><?php echo $course->fullname; ?></a></td>
                <td><?php echo fullname($course).'('.$course->username.')'; ?></td>
                <td><?php echo $course->univ.$course->major?></td>
                <td><?php echo $course->editingteacher; ?></td>
                <td><?php echo $course->teacher; ?></td>
                <td><?php echo $course->student; ?></td>
                <td><?php echo $course->auditor; ?></td>
                <td><a href="javascript:course_edit_popup('<?php echo $course->id ?>');">Click</a></td>
                <td>-</td>
                <td><?php echo $submitted_count; ?></td>
                <td>-</td>
                <td>-</td>
                <td>-</td>
                <td><?php echo $course->student_content; ?></td>
                <td><?php echo $course->teacher_content; ?></td>
                <td><?php echo $course->editingteacher_content; ?></td> 
                <td><?php echo $course->student_comment; ?></td>
                <td><?php echo $course->editingteacher_comment; ?></td>
            </tr>
            <?php }} 
            ?>
        </table><!--Table End-->
        <?php
         print_paging_navbar_script($count_courses, $currpage, $perpage, 'javascript:cata_page(:page);');
        ?>
    </div><!--Content End-->
    
</div> <!--Contents End-->
<script type="text/javascript">
    function course_edit_popup(id) {
        var tag = $("<div></div>");
        $.ajax({
          url: '<?php echo $SITECFG->wwwroot.'/siteadmin/stats/course_form.php'; ?>',
          data: {
              parent: $('[name=parent]').val(),
              category: $('[name=category]').val(),
              id: id
          },
          success: function(data) {
            tag.html(data).dialog({
                title: '<?php echo get_string('stats_learningactivitystatus', 'local_lmsdata'); ?>',
                modal: true,
                width: 600,
                maxHeight: getWindowSize().height - 20,
                close: function () {
                    $( this ).dialog('destroy').remove()
                }
            }).dialog('open');
          }
        });
    }
        function course_all_excel() {
        <?php
        $query_string = '';
        if(!empty($param)) {
            $query_array = array();
            foreach($param as $key=>$value) {
                $query_array[] = urlencode( $key ) . '=' . urlencode( $value );
            }
            $query_string = '?'.implode('&', $query_array);
        }
        ?>
        var url = "course_all.excel.php<?php echo $query_string; ?>";
        
        document.location.href = url;
    }
</script>
 <?php include_once ('../inc/footer.php');?>

