<?php

$parent1 = optional_param('parent1', 0, PARAM_INT);
$parent2 = optional_param('parent2', 0, PARAM_INT);
$parent3 = optional_param('parent3', 0, PARAM_INT);

$page = optional_param('page', 1, PARAM_INT);
$perpage = optional_param('perpage', 10, PARAM_INT);
$searchtext = optional_param('searchval', '', PARAM_RAW);
$search = optional_param('searchfield', 3, PARAM_INT);
$current_course = optional_param('courseid', 1, PARAM_INT);

$currentlang = current_language();

$sql_select = "SELECT mc.id
     , mc.fullname AS course_name
     , lc.eng_lec_name AS course_name_eng
     , lc.subject_id 
     , lc.domain
     , lc.timeregstart 
     , lc.timeregend 
     , ca.name AS category_name 
     , ca.path AS category_path 
     , case '".$currentlang."' when 'ko' then u.firstname else u.lastname END as prof_name 
     , CASE WHEN en.status IS NULL THEN 1 ELSE en.status END AS can_auditing 
     , en.enrol
     , r.shortname AS rolename 
     , (SELECT COUNT(*) 
        FROM {role_assignments} ra
        WHERE ra.contextid = ctx.id
          AND ra.userid = :userid) AS enroled ";
$sql_from = " FROM {course} mc
JOIN {context} ctx ON ctx.instanceid = mc.id 
                  AND ctx.contextlevel = :contextlevel 
JOIN {lmsdata_class} lc ON lc.course = mc.id 
JOIN {course_categories} ca ON ca.id = mc.category 
LEFT JOIN {user} u ON u.id = lc.prof_userid  
LEFT JOIN {enrol} en ON en.courseid = mc.id AND en.enrol = 'apply'
LEFT JOIN {role} r ON r.id = en.roleid AND r.shortname = 'auditor' ";

$sql_conditions = array('lc.isnonformal = :isnonformal');
$sql_params = array(
    'contextlevel' => CONTEXT_COURSE,
    'userid' => $USER->id,
    'isnonformal' => 0);

$cata_path = '';
if($parent3) {
    $cata_path = $DB->get_field('course_categories', 'path', array('id'=>$parent3));
} else if($parent2) {
    $cata_path = $DB->get_field('course_categories', 'path', array('id'=>$parent2));
} else if($parent1) {
    $cata_path = $DB->get_field('course_categories', 'path', array('id'=>$parent1));
}
if(!empty($cata_path)) {
    $sql_conditions[] = $DB->sql_like('ca.path', ':path');
    $sql_params['path'] = $cata_path.'%';
}

if(!empty($searchtext)) {
    switch($search) {
        case 1: // 학정번호
            $sql_conditions[] = $DB->sql_like('lc.subject_id', ':subject_id');
            $sql_params['subject_id'] = '%'.$searchtext.'%';
            break;
        case 2: // 교수명
            $sql_conditions[] = $DB->sql_like("case '".$currentlang."' when 'ko' then u.firstname else u.lastname END", ':prof_name');
            $sql_params['prof_name'] = '%'.$searchtext.'%';
            break;
        case 3; // 강의명
            $sql_conditions[] = $DB->sql_like('mc.fullname', ':course_name');
            $sql_params['course_name'] = '%'.$searchtext.'%';
            break;
        default:
            break;
    }
}

$year = get_config('moodle', 'haxa_year');
$term = get_config('moodle', 'haxa_term');

//년도가 없을 경우
if(!$year) $year = date('Y');

//검색으로 나오는 년도, 학기
$syear = optional_param('syear', $year, PARAM_INT);
$sterm = optional_param('sterm', $term, PARAM_INT);
$gubun = optional_param('gubun', 3, PARAM_INT);
$sql_conditions[] = 'lc.year = :year';
$sql_params['year'] = $syear;
if($sterm != 0){
    $sql_conditions[] = '(lc.term = :term or lc.term = 0)';
    $sql_params['term'] = $sterm;
}
if($gubun != 3){
    $sql_conditions[] = 'lc.gubun = :gubun';
    $sql_params['gubun'] = $gubun;
} 
$sql_conditions[] = "ca.idnumber != :irregular AND ca.idnumber != :oklass_self ";
$sql_params['irregular'] = SELF_COURSE_CATEGORY;
$sql_params['oklass_self'] = SELF_OKLASS_CATEGORY;

$sql_where = ' WHERE '.implode(' AND ', $sql_conditions);

$sql_orderby = ' ORDER BY ca.sortorder asc, lc.subject_id, mc.fullname asc';

$totalcount = $DB->count_records_sql('SELECT COUNT(*) '.$sql_from.$sql_where, $sql_params);
$courses = $DB->get_records_sql($sql_select.$sql_from.$sql_where.$sql_orderby, $sql_params, ($page-1)*$perpage, $perpage);
$context = context_system::instance();
$isadmin = has_capability('moodle/site:config', $context);
?>
            <!-- Table Area Start -->
            <form class="table-search-option" id="frm_course">
                <!--<span class="table-search-option-label"><?php echo get_string('category:sort', 'local_courselist') ?></span>-->
                <select name="syear">
                    <?php 
                    for($y = $year; $y >= 2015; $y--){
                        if($y == $syear){
                                $selecte_y = "selected";
                            }else{
                                $selecte_y = "";

                            }
                            echo '<option value="'.$y.'" '.$selecte_y.'>'.get_string('year', 'local_courselist', $y).'</option>';
                    }
                    ?>
                </select>
                <select name="sterm">
                    <?php 
                    $terms = array(1=>1,2=>2);
                    $selecte_0 = $sterm == 0 ? 'selected':'';
                    echo '<option value="0" '.$selecte_0.'>'.get_string('term:all','local_courselist').'</option>';
                    foreach($terms as $k => $t){
                        if($t == $sterm){
                                $selecte_t = "selected";
                            }else{
                                $selecte_t = "";
                            }
                            echo '<option value="'.$k.'" '.$selecte_t.'>'.get_string('term', 'local_courselist', $t).'</option>';
                    }
                    ?>
                </select>
                <select name="gubun">
                    <?php 
                    $gubuns = array(3=>get_string('category:all','local_courselist'), 1=> get_string('elearning','local_courselist'), 0=>get_string('regularcourse','local_courselist'));
                    foreach($gubuns as $k => $t){
                        if($k == $gubun){
                                $selecte_t = "selected";
                            }else{
                                $selecte_t = "";
                            }
                            echo '<option value="'.$k.'" '.$selecte_t.'>'.get_string('gubun', 'local_courselist', $t).'</option>';
                    }
                    ?>
                </select>
                <br/>
                <select name="parent1" onChange="javascript:category_parent1_changed(this.options[this.selectedIndex].value, 'frm_course');">
                   <option value="0"><?php echo get_string('category:all', 'local_courselist'); ?></option>
                   <?php 
                   $sql_select = "SELECT id, name ";
                   $sql_sort = " ORDER BY sortorder";
                   $sql = $sql_select."
                            FROM {course_categories} 
                            WHERE parent = :parent
                                AND ".$DB->sql_like('idnumber', ':idnumber', false);
                   
                   $params = array("parent" => 0);
                   if($syear < 2016) {
                       $params['idnumber'] = '%div_%';
                   } else {
                       $params['idnumber'] = '%OKLASS_%';
                   }
                   
                   $parent = $DB->get_records_sql($sql.$sql_sort, $params);
                    foreach($parent as $category1) {
                        if($category1->id == $parent1) {
                            $selected = "selected";
                        }else {
                            $selected = "";
                        }
                        echo '<option value="'.$category1->id.'" '.$selected.'>'.$category1->name.'</option>';
                    }
                    ?>
                </select>
                <select name="parent2" onChange="javascript:category_parent2_changed(this.options[this.selectedIndex].value, 'frm_course');">
                    <option value="0"><?php echo get_string('category:all', 'local_courselist'); ?></option>
                    <?php
                    if(!empty($parent1)){
                        $sql_select = "SELECT id, name ";
                        $sql_sort = " ORDER BY sortorder";
                        $sql = $sql_select."
                            FROM {course_categories} 
                            WHERE parent = :parent";
                   
                        $parent2_arr = $DB->get_records_sql($sql.$sql_sort, array('parent'=>$parent1));
                        foreach ($parent2_arr as $value) {
                            if($parent2 == $value->id){
                                $selecte_p2 = "selected";
                            }else{
                                $selecte_p2 = "";
                            }
                            echo '<option value="'.$value->id.'" '.$selecte_p2.'>'.$value->name.'</option>';
                        }
                    }
                ?>
                </select>
                <select name="parent3">
                    <option value="0"><?php echo get_string('category:all', 'local_courselist'); ?></option>
                    <?php
                    if(!empty($parent2)){
                        $sql_select = "SELECT id, name ";
                        $sql_sort = " ORDER BY sortorder";
                        $sql = $sql_select."
                            FROM {course_categories} 
                            WHERE parent = :parent";
                        
                        $parent3_arr = $DB->get_records_sql($sql.$sql_sort, array('parent'=>$parent2));
                        foreach ($parent3_arr as $value) {
                            if($parent3 == $value->id){
                                $selecte_p3 = "selected";
                            }else{
                                $selecte_p3 = "";

                            }
                            echo '<option value="'.$value->id.'" '.$selecte_p3.'>'.$value->name.'</option>';
                        }
                    }
                ?>
                </select>
                <br/>
                <!--<span class="table-search-option-label"><?php echo get_string('search', 'local_courselist'); ?></span>-->
                <select name="searchfield">
                    <option value="1" <?php echo ($search==1)? 'selected':'';?>><?php echo get_string('course:subjectid', 'local_courselist');?></option>
                    <option value="2" <?php echo ($search==2)? 'selected':'';?>><?php echo get_string('professor', 'local_courselist');?></option>
                    <option value="3" <?php echo ($search==3)? 'selected':'';?>><?php echo get_string('course:name', 'local_courselist');?></option>
                </select>
                <input type="text" name="searchval" placeholder="<?php echo get_string('search:keywords', 'local_courselist');?>" value="<?php echo $searchtext;?>"/>
                <input type="submit" value="<?php echo get_string('search', 'local_courselist');?>"  class="board-search" onclick="javascript:course_all_select_submit();"/>
                <input type="hidden" name = "type" value="<?php echo $type; ?>">
                <input type="hidden" name = "page" value="1">
                <input type="hidden" name = "perpage" value="<?php echo $perpage; ?>">
            </form>
            <!-- Table Search Option -->
            <div class="table-header-area">
            </div>
            <!-- Table Header -->
            <div class="table-filter-area">
                <form method="get" action="#" id="frm_perpage">
                    <div>
                        <select class="select perpage" name="perpage" onchange="change_perpage(this.options[this.selectedIndex].value);">
                            <?php
                            $nums = array(10,20,30,50);
                            foreach ($nums as $num) {
                                $selected = '';
                                if($num == $perpage) {
                                    $selected = ' selected';
                                }
                                echo '<option value="'.$num.'"'.$selected.'>'.get_string('showperpage', 'local_courselist', $num).'</option>';
                            } ?>
                        </select>
                    </div>
                </form>
            </div>
            <!-- Table Filter --> 

            <!-- Table Start -->
            <table class="generaltable" id="table_courses">
                <thead>
                    <tr>
                        <th class="mobile" width="15%"><?php echo get_string('course:category', 'local_courselist'); ?></th>
                        <th width="20%"><?php echo get_string('course:subjectid', 'local_courselist'); ?></th>
                        <th><?php echo get_string('course:name', 'local_courselist'); ?></th>
                        <th width="15%"><?php echo get_string('course:professor', 'local_courselist'); ?></th>
                        <?php if($isadmin) { ?>
                        <th class="mobile" width="15%"><?php echo get_string('viewcourse', 'local_courselist'); ?></th>
                        <?php } ?>
                    </tr>
                </thead>
                <tbody>
                <?php
                if($totalcount >0) {
                    $root_categories = $DB->get_records_menu('course_categories', array('parent'=>0), '', 'id, name');
                    
                    $possible = get_string('possible', 'local_courselist');
                    $impossible = get_string('impossible', 'local_courselist');
                    
                    $rowno = $totalcount - ($page - 1) * $perpage;
                    $rowcount = 0;
                    foreach($courses as $course){
                        $paths = array_filter(explode('/', $course->category_path));
                        $categoryid = array_shift($paths);
                        $category_name = $root_categories[$categoryid];
                        
                        $rowcount += 1;
                        $disabled = 'disabled = "true"';
                        
                        $can_auditing = $impossible; 
                        if($course->can_auditing == 0 && $course->rolename == 'auditor' && $course->enroled == 0 && $course->timeregstart < time() && $course->timeregend > time()) {
                            $disabled = '';
                            $can_auditing = $possible;
                        }
                        
                        if($course->enroled != 0) {
                            $can_auditing = get_string('course:registered', 'local_courselist');
                        }
                        
                        $subjectids = explode('-', $course->subject_id);
     
                ?>
                    <tr>
                        <td class="mobile"><?php echo $category_name; ?></td>
                        <td><?php echo $course->subject_id; ?></td>
                        <td class="title">
                            <?php if($isadmin) { ?>
                            <!--<a href="javascript:showCourseInfo('<?php echo $course->id; ?>');">-->
                            <a href="<?php echo $CFG->wwwroot.'/course/view.php?id='.$course->id; ?>">
                                <?php echo $course->course_name; ?>
                            </a>
                            <?php }else{
                                echo $course->course_name;
                            } 
                            ?>
                        </td>
                        <td>
                            <?php
                                if(!empty($course->prof_name)) {
                                    $prof_arr = explode(',', $course->prof_name);
                                    foreach($prof_arr as $prof_name) {
                                        echo '<div class="prof_name" style="padding-bottom: 4px">'.$prof_name.'</div>'; 
                                    }
                                } else {
                                    echo '-';
                                }
                            ?>
                        </td>
                        <?php if($isadmin) { ?>
                        <td class="mobile"><a href="<?php echo $CFG->wwwroot.'/course/view.php?id='.$course->id; ?>"><img src="<?php echo $CFG->wwwroot;?>/local/courselist/pix/icon_incourse.png" alt="<?php echo get_string('viewcourse', 'local_courselist') ;?>"></a></td>
                        <?php } ?>
                    </tr>
                <?php
                    }
                } else {
                    if($isadmin) {
                        echo '<tr><td colspan="8">'.get_string('course:empty', 'local_courselist').'</td></tr>';
                    } else {
                        echo '<tr><td colspan="7">'.get_string('course:empty', 'local_courselist').'</td></tr>';
                    }
                }
                ?>
                </tbody>
            </table>
            <!-- Table End -->

        <div class="table-footer-area">
            <div class="btn-area btn-area-left">
                <!--<input type="button" value="수강신청" />-->
            </div>
<!--            <div class="btn-area btn-area-left"> 
                <input type="button" value="<?php echo get_string('sititon:apply', 'local_courselist'); ?>" onclick="javascript:applySititon();"/> 
            </div>-->
            <?php
             courselist_print_paging_navbar_script($totalcount, $page, $perpage, 'javascript:goto_page(:page);', 10);
             ?>
        </div>
