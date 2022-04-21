<?php 
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
if($coursetype == 0){
    $juya = optional_param('juya', '', PARAM_RAW);
    $dept = optional_param('dept', '', PARAM_RAW);
    $hyear = optional_param('hyear', '', PARAM_RAW);
}
$dept_sql = "select distinct ohakkwa from {lmsdata_class} ORDER BY ohakkwa ASC ";
$dept_lists = $DB->get_records_sql($dept_sql,array());

// 현재 년도, 학기
if(!$year) {
    $year = get_config('moodle', 'haxa_year'); 
}
if(!$term) {
    $term = get_config('moodle', 'haxa_term');
}

$sql_select  = "SELECT distinct mc.id, mc.fullname, mc.shortname,yc.hyear,yc.day_tm_cd 
     , yc.subject_id, yc.isnonformal , yc.year, yc.term
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
        AND ro.id = 4) as teacher ";

$sql_from    = " FROM {course} mc 
JOIN {lmsdata_class} yc ON yc.course = mc.id 
JOIN {course_categories} ca ON ca.id = mc.category  
left join {context} ctx on ctx.contextlevel = :contextlevel4 and ctx.instanceid = mc.id 
left join {role_assignments} ra on ra.contextid = ctx.id 
left join {role} r on r.id = ra.roleid and r.shortname = 'editingteacher' 
left join {user} mu on mu.id = ra.userid and mu.deleted = 0";

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
   if(!empty($dept)) {
        $conditions[] = " ca.name like :dept ";
        $param['dept'] = '%'.$dept.'%';
    }
    if(!empty($hyear)) {
        $conditions[] = "  yc.hyear = :hyear ";
        $param['hyear'] = $hyear;
    }
    if(!empty($juya)) {
        $conditions[] = " yc.day_tm_cd = :juya ";
        $param['juya'] = $juya;
    }
}

if(!empty($searchtext)) {
    switch($search) {
        case 1: // 강의코드
            $conditions[] = $DB->sql_like('yc.subject_id', ':subject_id');
            $param['subject_id'] = '%'.$searchtext.'%';
            break;
        case 2: // 교수명
            $conditionname = array();

            $conditionname[] = $DB->sql_like('mu.firstname', ':firstname', false);
            $conditionname[] = $DB->sql_like('mu.lastname', ':lastname', false);
            $conditionname[] = $DB->sql_like($DB->sql_fullname('mu.firstname', 'mu.lastname'), ':fullname', false);
            $conditionname[] = $DB->sql_like($DB->sql_fullname('mu.lastname', 'mu.firstname'), ':fullname1', false);
            $conditionname[] = $DB->sql_like($DB->sql_concat('mu.firstname', 'mu.lastname'), ':fullname2', false);
            $conditionname[] = $DB->sql_like($DB->sql_concat('mu.lastname', 'mu.firstname'), ':fullname3', false);
            $conditionname[] = $DB->sql_like('mu.username', ':username', false);
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
    $param['category'] = $cata_path.'%';
}

$sql_where = '';
if($conditions) $sql_where = ' WHERE '.implode(' AND ',$conditions);

$courses = $DB->get_records_sql($sql_select.$sql_from.$sql_where, $param, ($currpage-1)*$perpage, $perpage);
$count_courses = $DB->count_records_sql("SELECT COUNT(distinct mc.id) ".$sql_from.$sql_where, $param);


$js = array( 
    $CFG->wwwroot.'/siteadmin/manage/course_list.js'
);
?>

<?php include_once ('../inc/header.php');?>
<div id="contents">
    <?php include_once ('../inc/sidebar_stats.php');?>
    <div id="content">
<!--        <h3 class="page_title"><?php echo get_string('stats_allcourselist', 'local_lmsdata'); ?></h3>-->
        <h3 class="page_title">
            <?php if($coursetype == 0){
                echo get_string('regular_course', 'local_lmsdata'); 
            }else if($coursetype == 1){
                echo get_string('irregular_course', 'local_lmsdata');
            }else{
                echo get_string('elearning_course', 'local_lmsdata');
            } ?>
        </h3>
        <div class="page_navbar"><a href="./contact_stats_day.php"><?php echo get_string('stats_management', 'local_lmsdata'); ?></a> > <a href="<?php echo $CFG->wwwroot; ?>/siteadmin/stats/course_all.php"><?php echo get_string('stats_allcourselist', 'local_lmsdata'); ?></a> > 
            <?php if($coursetype == 0){
                echo get_string('regular_course', 'local_lmsdata'); 
            }else if($coursetype == 1){
                echo get_string('irregular_course', 'local_lmsdata');
            }else{
                echo get_string('elearning_course', 'local_lmsdata');
            } ?></div>
        <p class="page_sub_title"> <?php echo get_string('stats_longtext5', 'local_lmsdata'); ?></p>
        
        <div class="down_area">
             <input type="submit" onclick="course_all_excel(); return false;" class="red_btn" value="<?php echo get_string('stats_longtext4', 'local_lmsdata'); ?> (*.xls)" style="float:left;"/>
             <p style="float:left; margin: 4px 0 0 10px;"><?php echo get_string('stats_longtext2', 'local_lmsdata'); ?></p>
        </div> <!--Down Area End-->
        
        <form name="" id="course_search" class="search_area" action="course_all.php" method="get">
            <input type="hidden" name="page" value="1" />
            <input type="hidden" name="coursetype" value="<?php echo $coursetype;?>" />
            <?php if($coursetype == 0){ ?>
            <b>주야구분 : </b> 
            <select title="주야" name="juya" class="w_160">
                <option value="">- <?php echo get_string('contents_all', 'local_lmsdata'); ?> -</option>
                <option value = '10' <?php if($juya == 10) echo 'selected'; ?>>주간</option>
                <option value = '20' <?php if($juya == 20) echo 'selected'; ?>>야간</option>
            </select> 
            <b>학과 : </b> 
            <select title="학과" name="dept" class="w_160">
                <option value="">- <?php echo get_string('contents_all', 'local_lmsdata'); ?> -</option>
                <?php
                    foreach ($dept_lists as $dept_list){
                        $select = '';
                        if($dept == $dept_list->ohakkwa){
                            $select = 'selected'; 
                        }
                        if( $dept_list->ohakkwa == '' ){
                            continue;
                        }else{
                            echo '<option value="'.$dept_list->ohakkwa.'" '.$select.'>'.$dept_list->ohakkwa.' </option>';
                        }
                    }
                ?>
            </select> 
            <b>학년 : </b> 
            <select title="학년" name="hyear" class="w_160">
                <option value="">- <?php echo get_string('contents_all', 'local_lmsdata'); ?> -</option>
                <option <?php if ($hyear == '1') echo 'selected'; ?> value="1">1<?php echo get_string('class','local_lmsdata'); ?></option>
                <option <?php if ($hyear == '2') echo 'selected'; ?> value="2">2<?php echo get_string('class','local_lmsdata'); ?></option>
                <option <?php if ($hyear == '3') echo 'selected'; ?> value="3">3<?php echo get_string('class','local_lmsdata'); ?></option>
                <option <?php if ($hyear == '4') echo 'selected'; ?> value="4">4<?php echo get_string('class','local_lmsdata'); ?></option>
            </select>
           <br>
            <?php if($coursetype==0){?>
            <label><?php echo get_string('stats_years', 'local_lmsdata'); ?> &nbsp;&nbsp;</label>
            <select name="year" title="year" class="w_260" style="margin:5px 20px 5px 0;">
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
            <?php } ?>
            <label><?php echo get_string('stats_terms', 'local_lmsdata'); ?></label>
            <select name="term" title="term" class="w_260" style="margin:5px 20px 5px 0;">
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
            <select name="cata1" title="category" id="course_search_cata1" onchange="cata1_changed(this);"  class="w_260" style="margin:5px 20px 5px 0;">
                <option value="0"> - <?php echo get_string('contents_all', 'local_lmsdata'); ?> -</option>
                <?php
                switch ($coursetype) {
                    case 0: $coursetypetext = get_string('regular_course', 'local_lmsdata'); $num = 2;
                        break;
                    case 1: $coursetypetext = get_string('irregular_activity', 'local_lmsdata'); $num = 3;
                        break;
                    case 2: $coursetypetext = get_string('elearning_course', 'local_lmsdata'); $num = 6;
                        break;
                }
                echo '<option value="'.$num.'">'.$coursetypetext.'</option>';
                
//                $category_sql = ' SELECT  ca.id, ca.idnumber, ca.name FROM {course_categories} ca join {lmsdata_class} cl on ca.id = cl.category WHERE ';
//                $sql_where = array();
//                $sql_where[] = 'ca.visible =:visible';
//                $sql_where[] = 'ca.parent =:parent';
//                $sql_where[] = 'cl.isnonformal =:coursetype';
//                $sql_where[] = $DB->sql_like('idnumber', ':idnumber');
//                $where = implode(" AND ", $sql_where);
//                
//                $sql_params =array('visible'=>1, 'parent'=>0, 'coursetype'=>$coursetype, 'idnumber'=>'oklass%');
//                
//                $sortorder = ' ORDER BY name asc ';
//                $catagories = $DB->get_records_sql($category_sql.$where.$sortorder, $sql_params);
//                
//                foreach($catagories as $catagory) {
//                    $selected = '';
//                    if($catagory->id == $cata1) {
//                        $selected = ' selected';
//                    }
//                    echo '<option value="'.$catagory->id.'"'.$selected.'> '.$catagory->name.'</option>';
//                }
                ?>
            </select>
            
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            <label><?php if($coursetype==0){?><input type="radio" name="search" title="code" value="1"<?php if($search == 1) {?> checked<?php } ?>/><?php echo get_string('course_code', 'local_lmsdata'); ?><?php }?></label>
            <label><input type="radio" title="professor" name="search" value="2"<?php if($search == 2) {?> checked<?php } ?>/><?php echo get_string('teachername', 'local_lmsdata'); ?></label>
            <label><input type="radio" title="lecture" name="search" value="3"<?php if($search == 3) {?> checked<?php } ?>/><?php echo get_string('course_name', 'local_lmsdata'); ?></label>
            <input type="text" name="searchtext" title="serch" value="<?php echo $searchtext; ?>" placeholder=" <?php echo get_string('stats_search', 'local_lmsdata'); ?> " class="w_260" style="color: #8E9094; margin:0 0 5px 15px;" />
            <input type="submit" class="blue_btn" value="<?php echo get_string('stats_search', 'local_lmsdata'); ?>" onclick="#" style="margin:0 0 5px 5px;"/>          
        </form><!--Search Area2 End-->
        <p style="float:left; color:red; margin-bottom: 5px;"><?php echo get_string('stats_longtext3', 'local_lmsdata'); ?></p>
        <table>
            <caption class="hidden-caption">강의운영현황</caption>
            <thead>
            <tr>
                <th scope="row"><?php echo get_string('number', 'local_lmsdata'); ?></th>
             <?php if($coursetype == 0){ ?>
                <th scope="row"><?php echo get_string('major', 'local_lmsdata'); ?></th>
                <th scope="row"><?php echo get_string('class', 'local_lmsdata'); ?></th>
             <?php }else{ ?>
                <th scope="row"><?php echo get_string('stats_classification', 'local_lmsdata'); ?></th>
             <?php } ?>
                <th scope="row"><?php echo get_string('stats_years', 'local_lmsdata'); ?></th>
                <th scope="row"><?php echo get_string('stats_terms', 'local_lmsdata'); ?></th>
                <th scope="row"><?php echo get_string('course_code', 'local_lmsdata'); ?></th>
                <th scope="row"><?php echo get_string('course_name', 'local_lmsdata'); ?></th>
                <?php if($coursetype == 0){ ?>    <th scope="row"><?php echo get_string('dayandnight', 'local_lmsdata'); ?></th> <?php } ?>
                <th scope="row"><?php echo get_string('teachername', 'local_lmsdata'); ?>(ID)</th>
                <th scope="row"><?php echo get_string('teacher', 'local_lmsdata'); ?></th>
                <th scope="row"><?php echo get_string('stats_assistant', 'local_lmsdata'); ?></th>
                <th scope="row"><?php echo get_string('stats_student', 'local_lmsdata'); ?></th>
                <th scope="row"><?php echo get_string('stats_learningactivity', 'local_lmsdata'); ?></th>
            </tr>
            </thead>
            <?php
            if($count_courses === 0) { ?>
            <tr>
                <td colspan="24">강의가 없습니다.</td>
            </tr>
            <?php } else {
            $startnum = $count_courses - (($currpage - 1) * $perpage);
            
            foreach($courses as $course) { 
                $user_query = "select u.* from {course} c
                        join {context} ctx on ctx.contextlevel = :contextlevel and ctx.instanceid = c.id 
                        join {role_assignments} ra on ra.contextid = ctx.id 
                        join {role} r on r.id = ra.roleid and r.shortname = 'editingteacher' 
                        join {user} u on u.id = ra.userid and u.deleted = 0
                    where c.id = :courseid  
                       ";
                    $teachers = $DB->get_records_sql($user_query, array('courseid' => $course->id, 'contextlevel'=> CONTEXT_COURSE));
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
                <?php if($coursetype == 0){ 
                echo '<td>';
                    if($course->hyear == 0 || $course->hyear == NULL || $course->hyear == ''){
                        $course->hyear = '-';
                    }else{
                        $course->hyear .= '학년';
                    }
                    echo $course->hyear.'</td>'; 
                 } ?>
                <td><?php echo $course->year.get_string('contents_year', 'local_lmsdata'); ?></td>
                <?php
                            if ($course->term == 10 ) {
                                $term = '1' . get_string('term', 'local_lmsdata');
                            } else if ($course->term == 20 ) {
                                $term = '2' . get_string('term', 'local_lmsdata');
                            } else if ($course->term == 11) {
                                $term = get_string('summer', 'local_okregular').get_string('term', 'local_lmsdata'); 
                            } else if ($course->term == 21) {
                                $term = get_string('winter', 'local_okregular').get_string('term', 'local_lmsdata'); 
                            } else {
                                $term = '-';
                            } 
                ?>     
                <td><?php echo $term; ?></td>
                <td><?php echo $course->subject_id; ?></td>
                <td class="text-left"><a href="<?php echo $CFG->wwwroot.'/course/view.php?id='.$course->id; ?>"><?php echo $course->fullname; ?></a></td>
                <?php if($coursetype == 0){ 
                echo '<td>'; 
                    if($course->day_tm_cd == '0' || $course->day_tm_cd == null || $course->day_tm_cd == ''){ $course->day_tm_cd = '-'; 
                    }else if($course->day_tm_cd == '10'){$course->day_tm_cd = '주간';
                    }else if($course->day_tm_cd == '20'){$course->day_tm_cd = '야간';}
                    echo $course->day_tm_cd;
                echo '</td>';
                } ?>
                <td>
                    <?php
                    foreach($teachers as $teacher) {
                        echo fullname($teacher).'('.$teacher->username.')<br>'; 
                    }
                    ?>
                </td>
                <td><?php echo $course->editingteacher; ?></td>
                <td><?php echo $course->teacher; ?></td>
                <td><?php echo $course->student; ?></td>
                <td><input type="submit" id="" class="orange_btn_small" value="<?php echo get_string('stats_learninghistory', 'local_lmsdata'); ?>" onclick="document.location.href='course_history.php?id=<?php echo $course->id ?>&coursetype=<?php echo $coursetype; ?>'; return false;">
                <input type="submit" id="" class="orange_btn_small" value="<?php echo get_string('stats_learningprogress', 'local_lmsdata'); ?>" onclick="document.location.href='course_progress.php?id=<?php echo $course->id ?>&coursetype=<?php echo $coursetype; ?>'; return false;">
                    
                </td>
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

