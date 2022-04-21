<?php

$page = optional_param('page', 1, PARAM_INT);
$perpage = optional_param('perpage', 10, PARAM_INT);
$searchtext = optional_param('searchval', '', PARAM_RAW);
$search = optional_param('searchfield', 2, PARAM_INT);

$currentlang = current_language();
        
$sql_select = "SELECT mc.id
     , mc.fullname AS course_name
     , lc.eng_lec_name AS course_name_eng
     , lc.subject_id 
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
LEFT JOIN {role} r ON r.id = en.roleid AND r.shortname = 'student' ";


$sql_conditions[] = "(ca.idnumber = :irregular OR ca.idnumber = :oklass_self)";
$sql_params = array(
            'contextlevel' => CONTEXT_COURSE,
            'userid' => $USER->id,
            'irregular' => SELF_COURSE_CATEGORY,
            'oklass_self' => SELF_OKLASS_CATEGORY
            );


if(!empty($searchtext)) {
    switch($search) {
        case 1: // 교수명
            $sql_conditions[] = $DB->sql_like("case '".$currentlang."' when 'ko' then u.firstname else u.lastname END", ':prof_name');
            $sql_params['prof_name'] = '%'.$searchtext.'%';
            break;
        case 2; // 강의명
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

$sql_conditions[] = '((lc.year = :year AND (lc.term = :term or lc.term = 0)) OR lc.year = :self_course)';
$sql_params['year'] = $syear;
$sql_params['term'] = $sterm;
$sql_params['self_course'] = "9999";

$sql_where = ' WHERE '.implode(' AND ', $sql_conditions);

$sql_orderby = ' ORDER BY lc.timemodified DESC ';

$totalcount = $DB->count_records_sql('SELECT COUNT(*) '.$sql_from.$sql_where, $sql_params);
$courses = $DB->get_records_sql($sql_select.$sql_from.$sql_where.$sql_orderby, $sql_params, ($page-1)*$perpage, $perpage);
$context = context_system::instance();
$isadmin = has_capability('moodle/site:config', $context);
?>    
        <form class="table-search-option" id="frm_course">
            <!--<span class="table-search-option-label"><?php echo get_string('search', 'local_courselist');?></span>-->
            <select name="searchfield">
                <option value="1" <?php echo ($search==1)? 'selected':'';?>><?php echo get_string('professor', 'local_courselist');?></option>
                <option value="2" <?php echo ($search==2)? 'selected':'';?>><?php echo get_string('course:name', 'local_courselist');?></option>
            </select>
            <input type="text" name="searchval" placeholder="<?php echo get_string('search:keywords', 'local_courselist');?>" value="<?php echo $searchtext;?>"/>
            <input type="submit" value="<?php echo get_string('search', 'local_courselist');?>" class="board-search"/>
            <input type="hidden" name = "type" value="<?php echo $type; ?>">
            <input type="hidden" name = "page" value="1">
            <input type="hidden" name = "perpage" value="<?php echo $perpage; ?>">
        </form>
        <!-- Table Search Option -->
        <div class="table-filter-area">
            <form method="get" action="#" id="frm_perpage">
                <div>
                    <?php if(is_siteadmin()) { ?>
                        <input type="button" value="<?php echo get_string('course:add', 'local_courselist');?>" class="board-search" onclick='location.href="./course_add.php"'/>
                        
                    <?php } ?>
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
        <table class="generaltable">
            <thead>
                <tr>
                    <th class="mobile col-1 board-fix" width="5%"></th>
                    <th class="mobile col-2" width="15%"><?php echo get_string('course:category', 'local_courselist'); ?></th>
                    <th class="col-3" width="20%"><?php echo get_string('course:subjectid', 'local_courselist'); ?></th>
                    <th class="col-4"><?php echo get_string('course:name', 'local_courselist'); ?></th>
                    <th class="col-5" width="15%"><?php echo get_string('course:professor', 'local_courselist'); ?></th>
                    <!--<th class="col-6" width="10%"><?php echo get_string('course:plan', 'local_courselist'); ?></th>-->
                    <?php if($isadmin) { ?>
                    <th class="mobile col-7" width="15%"><?php echo get_string('viewcourse', 'local_courselist'); ?></th>
                    <?php } ?>
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
                            $rowcount += 1;
                            $disabled = 'disabled = "true"';
                            $can_registration = $impossible;
                            if($course->can_auditing == 0 && $course->rolename == 'student' && $course->enroled == 0 && $course->timeregstart < time() && $course->timeregend > time()) {
                                $disabled = '';
                                $can_registration = $possible;
                            }
                            
                            $paths = array_filter(explode('/', $course->category_path));
                            $categoryid = array_shift($paths);
                            $category_name = $root_categories[$categoryid];
                            
                    ?>
                <tr>
                    <td class="mobile col-1"><input type="checkbox" name="id[]" value="<?php echo $course->id;?>" <?php echo $disabled;?>/></td>
                    <td class="mobile col-2"><?php echo $category_name; ?></td>
                    <td class="col-3"><?php echo $course->subject_id; ?></td>
                    <td class="col-4 title">
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
                    <td class="col-5">
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
                    <!--<td class="col-6"><a href="javascript:showSyllabus('<?php echo $course->id; ?>');"><img src="<?php echo $CFG->wwwroot;?>/local/courselist/pix/icon_syllabus.gif" alt="<?php echo get_string('syllabus', 'local_courselist') ;?>"></a></td>-->
                    <?php if($isadmin) { ?>
                    <td class="mobile col-7"><a href="<?php echo $CFG->wwwroot.'/course/view.php?id='.$course->id; ?>"><a href="<?php echo $CFG->wwwroot.'/course/view.php?id='.$course->id; ?>"><img src="<?php echo $CFG->wwwroot;?>/local/courselist/pix/icon_incourse.png" alt="<?php echo get_string('viewcourse', 'local_courselist') ;?>"></a></td>
                    <?php } ?>
                </tr>
                <?php 
                
                        }
                    } else {
                        if($isadmin) {
                            echo '<tr><td colspan="7">'.get_string('course:empty', 'local_courselist').'</td></tr>';
                        } else {
                            echo '<tr><td colspan="6">'.get_string('course:empty', 'local_courselist').'</td></tr>';
                        }
                    }
                ?>
            </tbody>
        </table>
        <!-- Table End -->

        <div class="table-footer-area">
            <div class="btn-area btn-area-left"> 
                <input type="button" value="<?php echo get_string('course:apply', 'local_courselist'); ?>" onclick="javascript:applyEnrol();"/> 
            </div>
            <div class="btn-area btn-area-right"> 
                <!-- <input type="button" value="청강신청"/> --> 
            </div>
            <?php
                courselist_print_paging_navbar_script($totalcount, $page, $perpage, 'javascript:goto_page(:page);', 10);
            ?>
            <!-- Breadcrumbs End -->
        </div>
