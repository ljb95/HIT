<?php
require_once dirname(dirname(dirname(__FILE__))) . '/config.php';
require_once dirname(dirname(__FILE__)) . '/lib/paging.php';
require_once dirname(dirname(__FILE__)) . '/lib.php';
require_once $CFG->dirroot . '/local/haksa/lib.php';

// Check for valid admin user - no guest autologin
require_login(0, false);
if (isguestuser()) {
    $SESSION->wantsurl = (string) new moodle_url('/siteadmin/manage/course_list.php');
    redirect(get_login_url());
}

$LMSUSER = $DB->get_record('lmsdata_user', array('userid' => $USER->id));

if (($LMSUSER->usergroup != 'de' && $LMSUSER->usergroup != 'pr') || $LMSUSER->menu_auth == 9) {
    $roleadmin = true;
} else {
    $roleadmin = false;
}

$context = context_system::instance();
require_capability('moodle/site:config', $context);

$coursetype = optional_param('coursetype', 0, PARAM_INT); //0:교과, 1:비교과, 2:이러닝 
$currpage = optional_param('page', 1, PARAM_INT);
$perpage = optional_param('perpage', 20, PARAM_INT);
if ($coursetype == 0) {
    $year = optional_param('year', date('Y'), PARAM_INT);
    $term = optional_param('term', get_config('moodle', 'haxa_term'), PARAM_RAW);
    $hyear = optional_param('hyear', '', PARAM_RAW); //
}
$juya = optional_param('juya', '', PARAM_RAW);
$hyear = optional_param('hyear', '', PARAM_RAW);
$dept = optional_param('dept', '', PARAM_RAW);
$search = optional_param('search', 0, PARAM_INT);
$searchtext = optional_param('searchtext', '', PARAM_TEXT);
$tag_searchtext = optional_param('tag_searchtext', '', PARAM_TEXT);
$cata1 = optional_param('cata1', 0, PARAM_INT);
$cata3 = optional_param('cata3', 0, PARAM_INT); //3차분류는 과정과 동일

$sql_where = array();
$params = array();
$excel_params = array();

$params['coursetype'] = $coursetype;

if (!$roleadmin) {
    $cata1 = $DB->get_record('course_categories', array('idnumber' => 'oklass_regular'))->id;
    $cata2 = $DB->get_record('course_categories', array('idnumber' => 'HIT'))->id;
    $coursetype = 1;

    $cate = array();
    local_haksa_get_course_categories($cate);
    $path = array();
    $path[] = $DB->get_field_sql('SELECT name FROM {course_categories} WHERE idnumber = :idnumber', array('idnumber' => 'oklass_regular'));
    $path[] = $DB->get_field_sql('SELECT name FROM {course_categories} WHERE idnumber = :idnumber', array('idnumber' => 'HIT'));
    
    if(empty($LMSUSER->domain)){
        $path[] = 'undefine';
    } else {
        $path[] = $LMSUSER->domain;
    }
    
    $cata3 = local_haksa_find_or_create_category($path, $cate);
    $params['coursetype'] = 0;
}

switch ($coursetype) {
    case 0: $coursetypetext = get_string('regular_course', 'local_lmsdata');
        break;
    case 1: $coursetypetext = get_string('irregular_course', 'local_lmsdata');
        break;
    case 2: $coursetypetext = get_string('elearning_course', 'local_lmsdata');
        break;
}

$sql_select = "SELECT mc.id, mc.fullname, mc.shortname, 
                lc.timestart, lc.timeend, lc.timeregstart, lc.timeregend, 
                lc.subject_id, lc.year, lc.term, lc.isreged, lc.prof_userid, lc.ohakkwa, lc.day_tm_cd, lc.hyear ";

$sql_from = " FROM {course} mc
                 JOIN {lmsdata_class} lc ON lc.course = mc.id
                 JOIN {course_categories} ca ON ca.id = mc.category 
                 left join {context} ctx on ctx.contextlevel = 50 and ctx.instanceid = mc.id 
                left join {role_assignments} ra on ra.contextid = ctx.id 
                left join {role} r on r.id = ra.roleid and r.shortname = 'editingteacher' 
                left join {user} ur on ur.id = ra.userid ";
// 학과 리스트 출력
$dept_sql = "select distinct ohakkwa from {lmsdata_class} ORDER BY ohakkwa ASC ";
$dept_lists = $DB->get_records_sql($dept_sql,array());

$cata_path = '';
if ($cata3) {
    $cata_path = $DB->get_field('course_categories', 'path', array('id' => $cata3));
    $excel_params['cata3'] = $cata3;
} else if ($cata2) {
    $cata_path = $DB->get_field('course_categories', 'path', array('id' => $cata2));
    $excel_params['cata2'] = $cata2;
} else if ($cata1) {
    $cata_path = $DB->get_field('course_categories', 'path', array('id' => $cata1));
    $excel_params['cata1'] = $cata1;
}

//교과,비교과,이러닝 구분
$sql_where[] = "lc.isnonformal = :coursetype";
$excel_params['coursetype'] = $coursetype;

if(!empty($hyear)) {
    $sql_where[] = ' lc.hyear = :hyear';
    $params['hyear'] = ''.$hyear.'';
}
if(!empty($juya)) {
    $sql_where[] = ' lc.day_tm_cd = :juya';
    $params['juya'] = ''.$juya.'';
}
if(!empty($dept)) {
    $sql_where[] = ' lc.ohakkwa like :dept';
    $params['dept'] = '%'.$dept.'%';
}

if (!empty($cata_path)) {
    $sql_where[] = $DB->sql_like('ca.path', ':category_path');
    $params['category_path'] = '%'.$cata_path . '%';
}

if (!empty($year) && $coursetype == 0) {
    $sql_where[] = " lc.year = :year ";
    $params['year'] = $year;
    $excel_params['year'] = $year;
}

if (!empty($term) && $coursetype == 0) {
    $sql_where[] = " lc.term = :term ";
    $params['term'] = $term;
    $excel_params['term'] = $term;
}

if (!empty($tag_searchtext)) {
    $sql_where[] = $DB->sql_like('lc.tag', ':tag');
    $params['tag'] = '%' . $tag_searchtext . '%';
    $excel_params['tag'] = $tag_searchtext;
}

if (!empty($searchtext)) {

    $excel_params['search'] = $search;
    $excel_params['searchtext'] = $searchtext;
    
    switch ($search) {
        case 0: // 전체
            $sql_where[] = '( ' . $DB->sql_like('lc.subject_id', ':subject_id') . ' or ' . $DB->sql_like('lc.kor_lec_name', ':kor_lec_name') . ' or ' . $DB->sql_like('ur.firstname', ':profname_kr') . ' or ' . $DB->sql_like('ur.lastname', ':profname_en') . ')';
            $params['subject_id'] = '%' . $searchtext . '%';
            $params['kor_lec_name'] = '%' . $searchtext . '%';
            $params['profname_kr'] = '%' . $searchtext . '%';
            $params['profname_en'] = '%' . $searchtext . '%';
            break;
        case 1: // 강의코드
            $sql_where[] = $DB->sql_like('lc.subject_id', ':subject_id');
            $params['subject_id'] = '%' . $searchtext . '%';
            break;
        case 2: // 강의명
            $sql_where[] = $DB->sql_like('lc.kor_lec_name', ':kor_lec_name');
            $params['kor_lec_name'] = '%' . $searchtext . '%';
            break;
        case 3: // 책임교수명
            $sql_where[] = '( ' . $DB->sql_like('ur.firstname', ':profname_kr') . ' or ' . $DB->sql_like('ur.lastname', ':profname_en') . ')';
            $params['profname_kr'] = '%' . $searchtext . '%';
            $params['profname_en'] = '%' . $searchtext . '%';
            break;
        default:
            break;
    }
}
if($coursetype != 2){
    $sql_groupby = " group by lc.subject_id ";
    $cnt = "SELECT COUNT(distinct lc.subject_id) ";
} else {
    $sql_groupby = " group by lc.id ";
    $cnt = "SELECT COUNT(distinct lc.id) "; 
}

$sql_orderby = " ORDER BY lc.ohakkwa ASC, mc.timecreated DESC ";

if (!empty($sql_where)) {
    $sql_where = ' WHERE ' . implode(' and ', $sql_where);
} else {
    $sql_where = '';
}

$term_arr = lmsdata_get_terms();

$courses = $DB->get_records_sql($sql_select . $sql_from . $sql_where . $sql_groupby .$sql_orderby, $params, ($currpage - 1) * $perpage, $perpage);
$count_courses = $DB->count_records_sql($cnt . $sql_from . $sql_where, $params);

$js = array(
    $CFG->wwwroot . '/siteadmin/manage/course_list.js'
);
?>

<?php include_once (dirname(dirname(__FILE__)) . '/inc/header.php'); ?>
<div id="contents"> 
    <?php
    include_once (dirname(dirname(__FILE__)) . '/inc/sidebar_manage.php');
    if (!$roleadmin) {
        $coursetype = 1;
    }
    ?>

    <div id="content">
        <h3 class="page_title">
            <?php
            if ($roleadmin) {
                $title = get_string('opencourse', 'local_lmsdata');
                echo $title . ' - ' . $coursetypetext;
            } else {
                $title = $LMSUSER->domain . ' 관리';
                echo $title;
            }
            ?>
        </h3>
        <div class="page_navbar"><a href="<?php echo $CFG->wwwroot . '/siteadmin/manage/course_list.php'; ?>"><?php echo get_string('course_management', 'local_lmsdata'); ?></a> > <a href="<?php echo $CFG->wwwroot . '/siteadmin/manage/course_list.php'; ?>"><?php echo get_string('opencourse', 'local_lmsdata'); ?></a> > <?php echo $coursetypetext; ?></div>
        <form name="course_search" id="course_search" class="search_area" action="course_list.php" method="get">
            <input type="hidden" name="page" value="1" />
            <input type="hidden" name="coursetype" value="<?php echo $coursetype; ?>" />
            <?php if ($roleadmin  && $coursetype == 0) { ?>
                <?php
                switch ($coursetype) {
                    case 0:
                        $cata1 = $DB->get_record('course_categories', array('idnumber' => 'oklass_regular'));
                        break;
                    case 1:
                        $cata1 = $DB->get_record('course_categories', array('idnumber' => 'oklass_irregular'));
                        break;
                    case 2: 
                        $cata1 = $DB->get_record('course_categories', array('idnumber' => 'oklass_selfcourse'));
                        break;
                }
                ?>
                <input type="hidden" id="cate1" value="<?php echo $cata1->id; ?>">
                <!--대전보건대-->
                <b>학과 : </b> 
                <select name="dept" title="category03" id="course_search_cata3" class="w_160">
                    <option value="">- <?php echo get_string('contents_all', 'local_lmsdata'); ?> -</option>
                    <?php
                        foreach ($dept_lists as $dept_list){
                            if($dept_list->ohakkwa == ''){
                                continue;
                            }
                            $select = '';
                            if($dept == $dept_list->ohakkwa || $dept_list->ohakkwa == null){
                                $select = 'selected'; 
                            }
                            echo '<option value="' . $dept_list->ohakkwa . '"' . $select . '> ' . $dept_list->ohakkwa . '</option>';
                        }
                    ?>
                    <?php
//                    if ($cata1 && $cata2) {
//                        $catagories = $DB->get_records('course_categories', array('visible' => 1, 'parent' => $cata2), 'sortorder', 'id, idnumber, name');
//                        if (!empty($path_arr[3])) {
//                            $cata3 = $path_arr[3];
//                        }
//                        foreach ($catagories as $catagory) {
//                            $selected = '';
//                            if ($catagory->id == $cata3) {
//                                $selected = ' selected';
//                            }
//                            echo '<option value="' . $catagory->id . '"' . $selected . '> ' . $catagory->name . '</option>';
//                        }
//                    }
                    ?>
                </select>
                <b>주야구분 : </b> 
                <select title="주야" name="juya" class="w_160">
                    <option value="">- <?php echo get_string('contents_all', 'local_lmsdata'); ?> -</option>
                    <option value = '10' <?php if($juya == 10) echo 'selected'; ?>>주간</option>
                    <option value = '20' <?php if($juya == 20) echo 'selected'; ?>>야간</option>
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
                <?php
            }
            if ($coursetype == 0) {
                $year_arr = lmsdata_get_years();
                ?>
                <select title="year" name="year" class="w_160">
                    <option value="0"  <?php echo $year == 0 ? 'selected' : '' ?>><?php echo get_string('all', 'local_lmsdata'); ?></option>
                    <?php
                    foreach ($year_arr as $tg_year) {
                        $selected = "";
                        if ($tg_year == $year) {
                            $selected = "selected";
                        }
                        echo '<option value="' . $tg_year . '"  ' . $selected . '>' . get_string('year', 'local_lmsdata', $tg_year) . '</option>';
                    }
                    ?>
                </select>
                <select title="term" name="term" class="w_160">
                    <option value="0" <?php echo $term == 0 ? 'selected' : '' ?>><?php echo get_string('all', 'local_lmsdata'); ?></option>
                    <?php
                    $term_arr = lmsdata_get_terms();
                    foreach ($term_arr as $term_key => $tg_term) {
                        $selected = "";
                        $term_key = (string) $term_key;
                        if ($term_key === $term) {
                            $selected = "selected";
                        }
                        echo '<option value="' . $term_key . '"  ' . $selected . '>' . $tg_term . '</option>';
                    }
                    ?>
                </select> 
            <?php } ?>
            <?php if ($coursetype == 1) { ?>
                <input type="text" title="tag_search" name="tag_searchtext" value="<?php echo $tag_searchtext; ?>" placeholder="<?php echo get_string('tag_search_placeholder', 'local_lmsdata'); ?>"  class="search-text"/>
            <?php } ?>
            <select title="lecture" name="search" class="w_160">
                <option value="0" <?php echo (!empty($search) && ($search == 0)) ? 'selected' : '' ?> ><?php echo get_string('all', 'local_lmsdata'); ?></option>
                <?php if ($coursetype != 2) { ?>
                    <option value="1" <?php echo (!empty($search) && ($search == 1)) ? 'selected' : '' ?> ><?php echo get_string('course_code', 'local_lmsdata'); ?></option>
                <?php } ?>
                <option value="2" <?php echo (!empty($search) && ($search == 2)) ? 'selected' : '' ?>><?php echo get_string('course_name', 'local_lmsdata'); ?></option>
                <option value="3" <?php echo (!empty($search) && ($search == 3)) ? 'selected' : '' ?>><?php echo get_string('teacher', 'local_lmsdata'); ?></option>
            </select> 
            <input type="text" title="search" name="searchtext" size="50" value="<?php echo $searchtext; ?>" placeholder="<?php echo get_string('search_placeholder', 'local_lmsdata'); ?>"  class="search-text"/>
            <input type="submit" class="search_btn" value="<?php echo get_string('search', 'local_lmsdata'); ?>"/>          
        </form><!--Search Area2 End-->

        <table>
            <caption class="hidden-caption">교과과정</caption>
            <thead>
                <tr>
                    <!--<th><input type="checkbox" onclick="check_course_id(this, 'courseid')"/></th>-->
                    <th scope="row" width="8%"><?php echo get_string('number', 'local_lmsdata'); ?></th>
                    <?php if ($coursetype == 0 || $coursetype == 1) { ?><th scope="row" width="10%"><?php echo get_string('course_code', 'local_lmsdata'); ?></th><?php } ?>
                    <?php if ($coursetype == 1) { ?><th scope="row" width="8%"><?php echo get_string('course_bunban', 'local_lmsdata'); ?></th><?php } ?>
                    <th scope="row"><?php echo get_string('course_name', 'local_lmsdata'); ?></th>
                    <?php if ($coursetype == 0) { 
                        echo '<th scope="row" width="10%">'.get_string('major', 'local_lmsdata').'</th>';  
                        echo '<th scope="row" width="5%">'.get_string('class', 'local_lmsdata').'</th>';  
                        echo '<th scope="row" width="5%">'.get_string('dayandnight', 'local_lmsdata').'</th>';  
                    } ?>
                    <th scope="row" width="15%"><?php echo get_string('teacher', 'local_lmsdata'); ?></th>
                    <?php if ($coursetype == 0) { ?><th scope="row" width="15%"><?php echo get_string('year_term', 'local_lmsdata'); ?></th><?php } ?>
                    <?php if ($coursetype != 0) { ?><th scope="row" width="20%"><?php echo get_string('opencourse_term', 'local_lmsdata'); ?></th><?php } ?>
                    <?php if ($coursetype != 0) { ?><th scope="row" width="10%"><?php echo get_string('status', 'local_lmsdata'); ?></th><?php } ?>
                    <th scope="row" width="10%"><?php echo get_string('role_count', 'local_lmsdata'); ?></th>
                    <th scope="row"><?php echo get_string('class_enter', 'local_lmsdata'); ?></th>
                </tr>
            </thead>
            <?php if ($count_courses === 0) { ?>
                <tr>
                    <td colspan="10"><?php echo get_string('empty_course', 'local_lmsdata'); ?></td>
                </tr>
                <?php
            } else {
                $startnum = $count_courses - (($currpage - 1) * $perpage);
                foreach ($courses as $course) {
                    $rolesqlfrom = 'from {context} mc 
                        join {role_assignments} ra on ra.contextid = mc.id and (roleid = 5 or roleid = 9) 
                        join {lmsdata_user} lu on ra.userid = lu.userid 
                        where mc.instanceid = :instanceid and mc.contextlevel = 50';
                    $role_count = $DB->count_records_sql("SELECT COUNT(ra.id) " . $rolesqlfrom, array('instanceid' => $course->id));

                    $user_query = "select u.* from {course} c
                        join {context} ctx on ctx.contextlevel = 50 and ctx.instanceid = c.id 
                        join {role_assignments} ra on ra.contextid = ctx.id 
                        join {role} r on r.id = ra.roleid and r.shortname = 'editingteacher' 
                        join {user} u on u.id = ra.userid 
                    where c.id = :courseid 
                       ";
                    $teachers = $DB->get_records_sql($user_query, array('courseid' => $course->id));
                    ?>
                    <tr>
                        <!--<td><input type="checkbox" class="courseid" name="courseid" value="<?php echo $course->id; ?>"/></td>-->
                        <td><?php echo $startnum--; ?></td>
                        <?php if ($coursetype == 0 || $coursetype == 1) { ?><td><?php echo $course->subject_id; ?></td><?php } ?>
                        <?php if ($coursetype == 1) { ?><td><?php echo $course->bunban ? $course->bunban : '-'; ?></td><?php } ?>
                        <td class="text-left"><a href="<?php echo $CFG->wwwroot . '/siteadmin/manage/course_list_add.php?id=' . $course->id . '&coursetype=' . $coursetype; ?>"><?php echo $course->fullname; ?></a></td>
                        <?php if ($coursetype == 0) { 
                            if($course->ohakkwa == null){
                                $course->ohakkwa = '-';
                            }
                            echo '<td>'.$course->ohakkwa.'</td>'; 
                            if($course->hyear == 0 || $course->hyear == NULL || $course->hyear == ''){
                                $course->hyear = '-';
                            }else{
                                $course->hyear .= '학년';
                            }
                            echo '<td>'.$course->hyear.'</td>'; 
                            if($course->day_tm_cd == '0' || $course->day_tm_cd == null || $course->day_tm_cd == ''){ $course->day_tm_cd = '-'; 
                            }else if($course->day_tm_cd == '10'){$course->day_tm_cd = '주간';
                            }else if($course->day_tm_cd == '20'){$course->day_tm_cd = '야간';}
                            echo '<td>'.$course->day_tm_cd.'</td>'; 
                        } ?>
                        <td>
                            <?php
                            foreach ($teachers as $teacher) {
                                echo fullname($teacher);
                            }
                            ?>
                        </td>
                        <?php if ($coursetype == 0) { ?><td><?php echo get_string('year', 'local_lmsdata', $course->year) . '/' . $term_arr[$course->term]; ?></td><?php } ?>
                        <?php if ($coursetype != 0) { ?><td><?php echo date('Y-m-d', $course->timestart) . ' ~ ' . date('Y-m-d', $course->timeend); ?></td><?php } ?>
                        <?php
                        if ($coursetype != 0) {
                            if ($course->isreged == 1) {
                                if ($course->timeregstart <= time() && $course->timeregend >= time()) {
                                    $regedstatustext = get_string('status1', 'local_lmsdata');
                                } else if ($course->timeregstart > time()) {
                                    $regedstatustext = get_string('status2', 'local_lmsdata');
                                } else if ($course->timeregend < time()) {
                                    $regedstatustext = get_string('status3', 'local_lmsdata');
                                }
                            } else {
                                $regedstatustext = '-';
                            }
                            ?>
                            <td><?php echo $regedstatustext; ?></td>    
                        <?php } ?>

                        <td>
                            <?php echo $role_count; ?>
                            <?php if ($coursetype == 1) { ?>
                                <input type="button" style="margin-left:10px;" value="<?php echo get_string('okay', 'local_lmsdata'); ?>" class="blue_btn" onclick="click_merger('<?php echo $course->id; ?>')">
                            <?php } else { ?>
                                <input type="button" style="margin-left:10px;" value="<?php echo get_string('okay', 'local_lmsdata'); ?>" class="blue_btn" onclick="role_list('<?php echo $course->id; ?>')">
                            <?php } ?>
                        </td>

                        <td>
                            <input type="button" value="강의실입장"  onclick="window.open('<?php echo $CFG->wwwroot . '/course/view.php?id=' . $course->id; ?>', '', '')">
                            </span>
                        </td>
                    </tr>
                    <?php
                }
            }
            ?>    
        </table><!--Table End-->

        <div id="btn_area">

            <div style="float:right;">
                <input type="submit" class="blue_btn" style="margin-right: 10px;" value="<?php echo get_string('excell_down', 'local_lmsdata'); ?>" onclick="course_list_excel();"/>  
                <input type="submit" class="blue_btn" style="margin-right: 10px;" value="<?php echo get_string('create_course', 'local_lmsdata'); ?>" onclick="javascript:location.href = 'course_list_add.php?coursetype=<?php echo $coursetype; ?>';"/> 
            </div>
        </div>
        <?php
        print_paging_navbar_script($count_courses, $currpage, $perpage, 'javascript:cata_page(:page);');

        $query_string = '';
        if (!empty($excel_params)) {
            $query_array = array();
            foreach ($excel_params as $key => $value) {
                $query_array[] = urlencode($key) . '=' . urlencode($value);
            }
            $query_string = '?' . implode('&', $query_array);
        }
        ?>            
    </div><!--Content End-->    
</div> <!--Contents End-->

<?php include_once ('../inc/footer.php'); ?>

<script type="text/javascript">
    function course_list_excel() {
        var url = "course_list.excel.php<?php echo $query_string; ?>";

        document.location.href = url;
    }

    function click_merger(id) {
        var tag = $("<div id='merger_list'></div>");
        $.ajax({
            url: "course_merger_list.php?merger_course_id=" + id,
            method: 'POST',
            success: function (data) {
                tag.html(data).dialog({
                    title: '수강생 목록',
                    modal: true,
                    width: 800,
                    resizable: false,
                    height: 600,
                    buttons: [{id: 'excel_down',
                            text: '<?php echo get_string('excell_down', 'local_lmsdata'); ?>',
                            click: function () {
                                location.href = 'course_list_excel.php?id=' + id;
                            }},
                        {id: 'close',
                            text: '<?php echo get_string('cancle', 'local_lmsdata'); ?>',
                            disable: true,
                            click: function () {
                                $(this).dialog("close");
                            }}],
                    close: function () {
                        $('#merger_list').remove();
                        $(this).dialog('destroy').remove()
                    }
                }).dialog('open');
            }
        });
    }

    function role_list(id) {
        var tag = $("<div id='role_list'></div>");
        $.ajax({
            url: "role_list.php?courseid=" + id,
            method: 'POST',
            success: function (data) {
                tag.html(data).dialog({
                    title: '<?php echo get_string('role_list', 'local_lmsdata'); ?>',
                    modal: true,
                    width: 800,
                    resizable: false,
                    height: 600,
                    buttons: [{id: 'excel_down',
                            text: '<?php echo get_string('excell_down', 'local_lmsdata'); ?>',
                            click: function () {
                                location.href = 'course_list_excel.php?id=' + id;
                            }},
                        {id: 'close',
                            text: '<?php echo get_string('okay', 'local_lmsdata'); ?>',
                            disable: true,
                            click: function () {
                                $(this).dialog("close");
                            }}
                    ],
                    close: function () {
                        $('#role_list').remove();
                        $(this).dialog('destroy').remove()
                    }
                }).dialog('open');
            }
        });
    }
</script>    
