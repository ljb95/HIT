<?php 
require_once dirname(dirname(dirname (__FILE__))).'/config.php';
require_once dirname(dirname (__FILE__)).'/lib/paging.php';
require_once dirname(dirname (__FILE__)).'/lib.php';

// Check for valid admin user - no guest autologin
require_login(0, false);
if (isguestuser()) {
    $SESSION->wantsurl = (string)new moodle_url('/siteadmin/manage/course_list_merge.php');
    redirect(get_login_url());
}
$context = context_system::instance();
require_capability('moodle/site:config', $context);

$currpage     = optional_param('page', 1, PARAM_INT);
$perpage      = optional_param('perpage', 20, PARAM_INT);
$year         = optional_param('year', 0, PARAM_INT);
$term         = optional_param('term', 0, PARAM_INT);
$hyear        = optional_param('hyear', '', PARAM_RAW); //학년
$search       = optional_param('search', 1, PARAM_INT);
$searchtext   = optional_param('searchtext', '', PARAM_TEXT);
$cata1        = optional_param('cata1', 0, PARAM_INT);
$cata2        = optional_param('cata2', 0, PARAM_INT);
$cata3        = optional_param('cata3', 0, PARAM_INT); //3차분류는 과정과 동일


$sql_select  = "SELECT mc.id, mc.fullname, mc.shortname, 
                lc.timestart, lc.timeend, lc.required, lc.hyear,
                lca.subject_id,
                ur.firstname||ur.lastname AS prof_name ";

$sql_from    = " FROM {course} mc
                 JOIN {lmsdata_class} lc ON lc.course = mc.id
                 JOIN (
                  SELECT DISTINCT standard_id FROM {lmsdata_class_drive_log} WHERE (type = 0 or type = 2) and restore_flag = 0
                  ) cdl ON cdl.standard_id = lc.course
                 JOIN {user} ur ON ur.id = lc.prof_userid
                 JOIN {course_categories} ca ON ca.id = lc.category 
                 JOIN {lmsdata_categories} lca ON lca.category = ca.id ";

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
}

if(!empty($year)) {
    $sql_where[] = " lc.year = :year ";
    $params['year'] = $year;
}

if(!empty($term)) {
    $sql_where[] = " lc.term = :term ";
    $params['term'] = $term;
}

if(!empty($hyear)) {
    $sql_where[] = ' lc.univ = :univ ';
    $params['univ'] = strpos($hyear, 'p') !== false ? 1 : 2;
    
    $sql_where[]= $DB->sql_like('lc.hyear', ':hyear');
    $params['hyear'] = '%'.str_replace('p', '', $hyear).'%';
    
}

if(!empty($searchtext)) {
    switch($search) {
        case 0: // 전체
            $sql_where[]= '( '.$DB->sql_like('lc.subject_id', ':subject_id').' or '.$DB->sql_like('lc.kor_lec_name', ':kor_lec_name').' )';
            $params['subject_id'] = '%'.$searchtext.'%';
            $params['kor_lec_name'] = '%'.$searchtext.'%';
            break;
        case 1: // 강의코드
            $sql_where[]= $DB->sql_like('lc.subject_id', ':subject_id');
            $params['subject_id'] = '%'.$searchtext.'%';
            break;
        case 2: // 과정명
            $sql_where[] = $DB->sql_like('ca.name', ':category_name');
            $params['category_name'] = '%'.$searchtext.'%';
            break;
        default:
            break;
    }
}

$sql_orderby = " ORDER BY mc.timecreated desc ";

if(!empty($sql_where)) {
    $sql_where = ' WHERE '.implode(' and ', $sql_where);
}else {
    $sql_where = '';
}

$courses = $DB->get_records_sql($sql_select.$sql_from.$sql_where.$sql_orderby, $params, ($currpage-1)*$perpage, $perpage);
$count_courses = $DB->count_records_sql("SELECT COUNT(*) ".$sql_from.$sql_where, $params);

$js = array(
    $CFG->wwwroot.'/siteadmin/manage/course_list.js'
);
?>

<?php include_once (dirname(dirname (__FILE__)).'/inc/header.php'); ?>
<div id="contents">
    <?php include_once  (dirname(dirname (__FILE__)).'/inc/sidebar_manage.php');?>
    
    <div id="content">
        <h3 class="page_title">통합분반내역</h3>
        <div class="page_navbar"><a href="./category_list.php"><?php echo get_string('course_management', 'local_lmsdata'); ?></a> > <a href="./course_list.php"><?php echo get_string('opencourse', 'local_lmsdata'); ?></a> > 통합분반내역</div>
        <div class="content_navigation">
            <a href="course_list.php"><p class="black_btn">강의리스트</p></a>
            <a href="course_list_merge.php"><p class="black_btn black_btn_selected">분반통합내역</p></a>
            <a href="course_list_restore.php"><p class="black_btn">분반되돌리기내역</p></a>
        </div><!--Content Navigation End-->
        <form name="course_search" id="course_search" class="search_area" action="course_list_merge.php" method="get">
            <input type="hidden" name="page" value="1" />
            
            <select name="cata1" id="course_search_cata1" onchange="cata1_changed(this);"  class="w_160">
                <option value="0"><?php echo get_string('case1','local_lmsdata'); ?></option>
                <?php
                    $cata1_select = " SELECT * FROM {course_categories} ";
                    $cata1_where = array();

                    $cata1_where[] = 'depth = :depth ';
                    $cata1_params['depth'] = 1;

                    $cata1_sql = $cata1_select.' WHERE '.implode(' AND ', $cata1_where);
                    $cata1_arr = $DB->get_records_sql($cata1_sql, $cata1_params);
                    foreach($cata1_arr as $category1) {
                        $selected = "";
                        if($category1->id == $cata1) {
                           $selected = "selected";
                        } 
                        echo '<option value="'.$category1->id.'"  '.$selected.'>'.$category1->name.'</option>';
                    }
                ?>
            </select>
            <select name="cata2" id="course_search_cata2" onchange="cata2_changed(this)" class="w_160">
                <option value="0"><?php echo get_string('case2','local_lmsdata'); ?></option>
                <?php
                    if(!empty($cata1)) {
                        $category_sql = ' SELECT id,name FROM {course_categories} WHERE ';
                        $sql_where = array();
                        $sql_where[] = 'parent =:parent';
                        $where = implode(" AND ", $sql_where);

                        $sql_params =array('parent'=>$cata1);

                        $catagories = $DB->get_records_sql($category_sql.$where, $sql_params);

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
            <select name="cata3" id="course_search_cata3" class="w_160">
                <option value="0"><?php echo get_string('case3','local_lmsdata'); ?></option>
                <?php
                    if(!empty($cata2)) {
                        $category_sql = ' SELECT id,name FROM {course_categories} WHERE ';
                        $sql_where = array();
                        $sql_where[] = 'parent =:parent';
                        $where = implode(" AND ", $sql_where);

                        $sql_params =array('parent'=>$cata2);

                        $catagories = $DB->get_records_sql($category_sql.$where, $sql_params);

                        foreach($catagories as $catagory3) {
                            $selected = "";
                            if($catagory3->id == $cata3) {
                               $selected = "selected";
                            } 
                            echo '<option value="'.$catagory3->id.'" '.$selected.'> '.$catagory3->name.'</option>';
                        }
                    }
                ?>
            </select>
            <br/>
            <select name="year" class="w_160">
                <option value="0"  <?php echo $year == 0 ? 'selected' : ''?>><?php echo get_string('all','local_lmsdata'); ?></option>
                <?php
                    $year_arr = lmsdata_get_years();
                    foreach($year_arr as $tg_year) {
                        $selected = "";
                        if($tg_year == $year) {
                           $selected = "selected";
                        } 
                        echo '<option value="'.$tg_year.'"  '.$selected.'>'. get_string('year','local_lmsdata',$tg_year) . '</option>';
                    }
                ?>
            </select>
            <select name="term" class="w_160">
                <option value="0" <?php echo $term == 0 ? 'selected' : ''?>><?php echo get_string('all','local_lmsdata'); ?></option>
                <?php
                echo $term;
                    $term_arr =lmsdata_get_terms();
                    foreach($term_arr as $term_key=> $tg_term) {
                        $selected = "";
                        if($term_key === $term) {
                           $selected = "selected";
                        } 
                        echo '<option value="'.$term_key.'"  '.$selected.'>'.$tg_term.'</option>';
                    }
                ?>
            </select>
            <select name="hyear" class="w_160">
                <option value="" <?php echo $hyear == '' ? 'selected' : ''?>><?php echo get_string('all','local_lmsdata'); ?></option>
                <?php
                    $hyear_arr = lmsdata_get_hyears();
                    foreach($hyear_arr as $hak_key => $tg_hyear) {
                        $selected = "";
                        if($hak_key == trim($hyear)) {
                           $selected = "selected";
                        } 
                        echo '<option value="'.$hak_key.'"  '.$selected.'>'.$tg_hyear.'학년</option>';
                    }
                ?>
            </select>
            <br/>
            <select name="search" class="w_160">
                <option value="0" <?php echo $search == 0 ? 'selected' : ''?> ><?php echo get_string('all','local_lmsdata'); ?></option>
                <option value="1" <?php echo $search == 1 ? 'selected' : ''?> ><?php echo get_string('course_code', 'local_lmsdata'); ?></option>
                <option value="2" <?php echo $search == 2 ? 'selected' : ''?>><?php echo get_string('cata_name','local_lmsdata'); ?></option>
            </select> 
            <input type="text" name="searchtext" value="<?php echo $searchtext; ?>" placeholder="<?php echo get_string('search_placeholder','local_lmsdata'); ?>"  class="search-text"/>
            <input type="submit" class="blue_btn" value="<?php echo get_string('search','local_lmsdata'); ?>"/>          
        </form><!--Search Area2 End-->
        
        <table>
            <tr>
                <th><?php echo get_string('number', 'local_lmsdata'); ?></th>
                <th><?php echo get_string('course_code', 'local_lmsdata'); ?></th>
                <th><?php echo get_string('course_name', 'local_lmsdata'); ?></th>
                <th><?php echo get_string('teacher', 'local_lmsdata'); ?></th>
                <th><?php echo get_string('opencourse_term', 'local_lmsdata'); ?></th>
                <th><?php echo get_string('required','local_lmsdata'); ?></th>
                <th><?php echo get_string('class','local_lmsdata'); ?></th>
                <th>분반내역</th>
            </tr>
            <?php
                if($count_courses === 0) { ?>
                <tr>
                    <td colspan="9"><?php echo get_string('empty_course','local_lmsdata'); ?></td>
                </tr>
                <?php } else {
                    $startnum = $count_courses - (($currpage - 1) * $perpage);
                    foreach($courses as $course) {
                        switch($course->required) {
                            case 1: // 강의코드
                                $required = "전필";
                                break;
                            case 2: // 과정명
                                $required = "전선";
                                break;
                            case 3: // 과정명
                                $required = "기타";
                                break;
                        }
                ?>
                <tr>
                    <td><?php echo $startnum--; ?></td>
                    <td><?php echo $course->subject_id; ?></td>
                    <td class="text-left"><?php echo $course->fullname; ?></td>
                    <td><?php echo $course->prof_name; ?></td>
                    <td><?php echo date('Y-m-d',$course->timestart).' ~ '.date('Y-m-d',$course->timeend); ?></td>
                    <td><?php echo $required; ?></td>
                    <td><?php echo get_hyear_str($course->hyear); ?></td>
                    <td><input type="submit" class="gray_btn" value="보기" onclick="drive_log_dialog('<?php echo $course->id;?>');"/></td>
                </tr>
                <?php
                    }
                }
                ?>    
        </table><!--Table End-->
        <?php
            print_paging_navbar_script($count_courses, $currpage, $perpage, 'javascript:cata_page(:page);');
        ?>   
            
    </div><!--Content End-->
    
</div> <!--Contents End-->

 <?php include_once ('../inc/footer.php');?>
