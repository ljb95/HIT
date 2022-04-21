<?php
require(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once $CFG->dirroot . '/local/okregular/lib.php';
require_once $CFG->libdir . '/formslib.php';

require_login();

$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_pagelayout('standard');

$PAGE->requires->jquery();
$PAGE->requires->jquery_plugin('ui');
$PAGE->requires->jquery_plugin('ui-css');
$PAGE->requires->jquery_plugin('migrate');

$PAGE->set_url('/local/okregular/my.php');

$strplural = get_string("pluginnameplural", "local_okregular");
$PAGE->navbar->add($strplural, new moodle_url($CFG->wwwroot.'/local/okregular/my.php'));
$PAGE->navbar->add(get_string('course:my','local_okregular'));
$PAGE->set_title($strplural);
$PAGE->set_heading(get_string('course:my','local_okregular'));

echo $OUTPUT->header();

$page = optional_param('page', 1, PARAM_INT);
$perpage = optional_param('perpage', 10, PARAM_INT);
$searchtext = optional_param('searchval', '', PARAM_RAW);
$search = optional_param('searchfield', 3, PARAM_INT);
$current_course = optional_param('courseid', 1, PARAM_INT);

$sql_select = "SELECT distinct mc.id
     , mc.fullname AS course_name
     , lc.eng_lec_name AS course_name_eng
     , lc.subject_id, lc.year, lc.term 
     , lc.domain
     , lc.timestart 
     , lc.timeend 
     , lc.prof_userid
     , ca.name AS category_name 
     , ca.path AS category_path 
    , u.firstname, u.lastname, lu.eng_name, ca.sortorder ,lc.bunban ";
$sql_from = " FROM {course} mc
JOIN {context} ctx ON ctx.instanceid = mc.id AND ctx.contextlevel = :contextlevel 
JOIN {lmsdata_class} lc ON lc.course = mc.id 
JOIN {course_categories} ca ON ca.id = mc.category 
JOIN {enrol} en ON en.courseid = mc.id AND en.enrol = 'manual' and en.status = 0 
JOIN {user_enrolments} ue ON ue.enrolid = en.id 
LEFT JOIN {user} u ON u.id = lc.prof_userid 
LEFT JOIN {lmsdata_user} lu ON lu.userid = u.id  ";

$sql_conditions = array('lc.isnonformal = :isnonformal','ue.userid = :userid','ue.status = 0');
$sql_params = array(
    'contextlevel' => CONTEXT_COURSE,
    'userid' => $USER->id,
    'isnonformal' => COURSE_TYPE);

if (!empty($searchtext)) {
    switch ($search) {
        case 1: // 강의코드
            $sql_conditions[] = $DB->sql_like('lc.subject_id', ':subject_id');
            $sql_params['subject_id'] = '%' . $searchtext . '%';
            break;
        case 2: // 교수명
            $sql_conditions[] = $DB->sql_like("case '" . $currentlang . "' when 'ko' then u.firstname else u.lastname END", ':prof_name');
            $sql_params['prof_name'] = '%' . $searchtext . '%';
            break;
        case 3; // 강의명
            $sql_conditions[] = $DB->sql_like('mc.fullname', ':course_name');
            $sql_params['course_name'] = '%' . $searchtext . '%';
            break;
        default:
            break;
    }
}

$year = get_config('moodle', 'haxa_year');
$term = get_config('moodle', 'haxa_term');

//년도가 없을 경우
if (!$year)
    $year = date('Y');

//검색으로 나오는 년도, 학기
$syear = optional_param('syear', $year, PARAM_INT);
$sterm = optional_param('sterm', $term, PARAM_INT);
$sql_conditions[] = 'lc.year = :year';
$sql_params['year'] = $syear;
if ($sterm != 0) {
    $sql_conditions[] = 'lc.term = :term';
    $sql_params['term'] = $sterm;
}

$sql_where = ' WHERE ' . implode(' AND ', $sql_conditions);

$sql_orderby = ' ORDER BY ca.sortorder asc, lc.subject_id, mc.fullname asc';

$totalcount = $DB->count_records_sql('SELECT COUNT(distinct mc.id) ' . $sql_from . $sql_where, $sql_params);
$courses = $DB->get_records_sql($sql_select . $sql_from . $sql_where . $sql_orderby, $sql_params, ($page - 1) * $perpage, $perpage);
$context = context_system::instance();
$isadmin = has_capability('moodle/site:config', $context);

//tab
$row = array();
$row[] = new tabobject(1, "$CFG->wwwroot/local/okregular/my.php", get_string('pluginname', 'local_okregular'));
$row[] = new tabobject(2, "$CFG->wwwroot/local/okregular/my_irreg.php", get_string('pluginname', 'local_okirregular'));
$rows[] = $row;

print_tabs($rows, 1);


?>


<!-- Table Area Start -->
<form class="table-search-option" id="frm_course">
    <select name="syear" title="year">
        <?php
        for ($y = $year; $y >= 2015; $y--) {
            if ($y == $syear) {
                $selecte_y = "selected";
            } else {
                $selecte_y = "";
            }
            echo '<option value="' . $y . '" ' . $selecte_y . '>' . get_string('year', 'local_okregular', $y) . '</option>';
        }
        ?>
    </select>
    <select name="sterm" title="term">
        <?php
        $terms = local_okregular_get_terms();
        foreach ($terms as $k => $t) {
            if ($k == $sterm) {
                $selecte_t = "selected";
            } else {
                $selecte_t = "";
            }
            echo '<option value="' . $k . '" ' . $selecte_t . '>' . $t . '</option>';
        }
        ?>
    </select>
    <input type="submit" value="<?php echo get_string('search', 'local_okregular'); ?>"  class="board-search" onclick="javascript:course_all_select_submit();"/>
    <input type="hidden" name = "page" value="1">
    <input type="hidden" name = "perpage" value="<?php echo $perpage; ?>">
</form>

<!-- Table Start -->
<table class="generaltable" id="table_courses">
    <caption class="hidden-caption">Course</caption>
    <thead>
        <tr>
            <th scope="row" width="10%"><?php echo get_string('year:sel', 'local_okregular'); ?></th>
            <th scope="row" width="10%"><?php echo get_string('term:sel', 'local_okregular'); ?></th>
            <th scope="row" width="15%"><?php echo get_string('course:subjectid', 'local_okregular'); ?></th>
            <th scope="row"><?php echo get_string('course:name', 'local_okregular'); ?></th>
            <th scope="row" width="15%"><?php echo get_string('course:professor', 'local_okregular'); ?></th>
            <th scope="row" width="15%"><?php echo get_string('division', 'local_lmsdata'); ?></th>
        </tr>
    </thead>
    <tbody>
        <?php
        if ($totalcount > 0) {
            $root_categories = $DB->get_records_menu('course_categories', array('parent' => 0), '', 'id, name');

            $possible = get_string('possible', 'local_okregular');
            $impossible = get_string('impossible', 'local_okregular');

            $rowno = $totalcount - ($page - 1) * $perpage;
            $rowcount = 0;
            foreach ($courses as $course) {
                $paths = array_filter(explode('/', $course->category_path));
                $categoryid = array_shift($paths);
                $category_name = $root_categories[$categoryid];

                $rowcount += 1;
                $disabled = 'disabled = "true"';

                $subjectids = explode('-', $course->subject_id);
                ?>
                <tr>
                    <td scope="col"><?php echo get_string('year','local_okregular',$course->year); ?></td>
                    <td  scope="col">
                        <?php
                            if ($course->term == 10) {
                              $term = '1' . get_string('stats_terms', 'local_lmsdata');
                            } else if ($course->term == 11) {
                                $term = '여름' . get_string('stats_terms', 'local_lmsdata');
                            } else if ($course->term == 20) {
                                $term = '2' . get_string('stats_terms', 'local_lmsdata');
                            } else if ($course->term == 21) {
                                $term = '겨울' . get_string('stats_terms', 'local_lmsdata');
                            } else {
                                $term = '-';
                            }
                            echo ($course->term == 0)? get_string('term:all','local_okregular'):$term; 
                        ?>
                    </td>
                    <td  scope="col"><?php echo (!$course->subject_id)? '-':$course->subject_id;?></td>
                    <td  scope="col" class="title">
                    <a href="<?php echo $CFG->wwwroot . '/course/view.php?id=' . $course->id; ?>">
                        <?php echo $course->course_name; ?>
                    </a>
                    </td>
                    <td  scope="col">
                        <?php
                        if (!empty($course->prof_userid)) {
                            $prof_name = (current_language()=='ko')? fullname($course):$course->eng_name;
                            echo '<div class="prof_name" style="padding-bottom: 4px">' . $prof_name . '</div>';
                        } else {
                            echo '-';
                        }
                        ?>
                    </td>
                    <td scope="col" >
                        <?php
                            if($course->bunban == '' || $course->bunban == null){
                                echo '-';
                            }else{
                                echo $course->bunban;
                            }
                        ?>
                    </td>
                </tr>
                <?php
            }
        } else {
            echo '<tr><td colspan="6">' . get_string('course:empty', 'local_okregular') . '</td></tr>';
        }
        ?>
    </tbody>
</table>
<!-- Table End -->

<div class="table-footer-area">
    <?php
    local_okregular_print_paging_navbar_script($totalcount, $page, $perpage, 'javascript:goto_page(:page);', 10);
    ?>
</div>

<script type="text/javascript">
    
    function goto_page(page) {
        $('[name=page]').val(page);
        $('#frm_course').submit();
    }
    
    function change_perpage(perpage) {
        $('[name=perpage]').val(perpage);
        $('#frm_course').submit();
    }
    
</script>

<?php
echo $OUTPUT->footer();
?>