<?php
require(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once $CFG->dirroot . '/local/okirregular/lib.php';
require_once $CFG->libdir . '/formslib.php';

require_login();

$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_pagelayout('standard');

$PAGE->requires->jquery();
$PAGE->requires->jquery_plugin('ui');
$PAGE->requires->jquery_plugin('ui-css');
$PAGE->requires->jquery_plugin('migrate');

$PAGE->set_url('/local/okregular/my_irreg.php');

$strplural = get_string("pluginnameplural", "local_okirregular");
$PAGE->navbar->add($strplural);
$PAGE->navbar->add(get_string('course:my','local_okirregular'));
$PAGE->set_title($strplural);
$PAGE->set_heading(get_string('course:my','local_okirregular'));

echo $OUTPUT->header();

$page = optional_param('page', 1, PARAM_INT);
$perpage = optional_param('perpage', 10, PARAM_INT);
$searchtext = optional_param('searchval', '', PARAM_RAW);
$search = optional_param('searchfield', 3, PARAM_INT);
$current_course = optional_param('courseid', 1, PARAM_INT);
$type = optional_param('type', 1, PARAM_INT); //1: 진행, 2: 대기, 3: 종료

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
    , u.firstname, u.lastname, lu.eng_name, ca.sortorder ";
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

$currentdate = time();
if($type == 1){
    //진행
    $sql_conditions[] = "lc.timestart <= :timestart and lc.timeend >= :timeend";    
}else if($type == 2){
    //대기
    $sql_conditions[] = "lc.timestart > :timestart";    
}else if($type == 3){
    //종료
    $sql_conditions[] = "lc.timeend < :timeend"; 
}

$sql_params['timestart'] = $currentdate;
$sql_params['timeend'] = $currentdate;

//강의명 검색
if($searchtext){
    $sql_conditions[] = $DB->sql_like('mc.fullname', ':course_name');
    $sql_params['course_name'] = '%' . $searchtext . '%';
}
$sql_where = ' WHERE ' . implode(' AND ', $sql_conditions);

$sql_orderby = ' ORDER BY ca.sortorder asc, lc.subject_id, mc.fullname asc';

$totalcount = $DB->count_records_sql('SELECT COUNT(distinct mc.id) ' . $sql_from . $sql_where, $sql_params);
$courses = $DB->get_records_sql($sql_select . $sql_from . $sql_where . $sql_orderby, $sql_params, ($page - 1) * $perpage, $perpage);
$context = context_system::instance();
$isadmin = has_capability('moodle/site:config', $context);

//tab
$row = $rows =  array();
$row[] = new tabobject(1, "$CFG->wwwroot/local/okregular/my.php", get_string('pluginname', 'local_okregular'));
$row[] = new tabobject(2, "$CFG->wwwroot/local/okregular/my_irreg.php", get_string('pluginname', 'local_okirregular'));
$rows[] = $row;

print_tabs($rows, 2);


//tab
$row = $rows =  array();
$row[] = new tabobject('1', "$CFG->wwwroot/local/okregular/my_irreg.php?type=1", get_string('ongoing', 'local_okirregular'));
$row[] = new tabobject('2', "$CFG->wwwroot/local/okregular/my_irreg.php?type=2", get_string('standby', 'local_okirregular'));
$row[] = new tabobject('3', "$CFG->wwwroot/local/okregular/my_irreg.php?type=3", get_string('finish', 'local_okirregular'));
$rows[] = $row;

print_tabs($rows, $type);
?>
<!-- Table Area Start -->
<form class="table-search-option" id="frm_course">
    <input type="text" title="search" name="searchval" placeholder="<?php echo get_string('search:coursename', 'local_okirregular'); ?>" value="<?php echo $searchtext; ?>"/>
    <input type="submit" value="<?php echo get_string('search', 'local_okirregular'); ?>"  class="board-search" onclick="javascript:course_all_select_submit();"/>
    <input type="hidden" name = "page" value="1">
    <input type="hidden" name = "type" value="<?php echo $type; ?>">
    <input type="hidden" name = "perpage" value="<?php echo $perpage; ?>">
</form>

<!-- Table Start -->
<table class="generaltable" id="table_courses">
    <caption class="hidden-caption"><?php echo get_string('course:name', 'local_okirregular'); ?></caption>
    <thead>
        <tr>
            <th scope="row"><?php echo get_string('course:name', 'local_okirregular'); ?></th>
            <th scope="row" width="25%"><?php echo get_string('course:open', 'local_okirregular'); ?></th>
            <th scope="row" width="15%"><?php echo get_string('course:professor', 'local_okirregular'); ?></th>
        </tr>
    </thead>
    <tbody>
        <?php
        if ($totalcount > 0) {
            $root_categories = $DB->get_records_menu('course_categories', array('parent' => 0), '', 'id, name');

            $possible = get_string('possible', 'local_okirregular');
            $impossible = get_string('impossible', 'local_okirregular');

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
                    <td scope="col" class="title">
                    <a href="<?php echo $CFG->wwwroot . '/course/view.php?id=' . $course->id; ?>">
                        <?php echo $course->course_name; ?>
                    </a>
                    </td>
                    <td scope="col">
                        <?php
                        $timestart = date('Y-m-d',$course->timestart);
                        $timeend = date('Y-m-d',$course->timeend);
                        echo $timestart.' ~ '.$timeend;
                        ?>
                    </td>
                    <td scope="col">
                        <?php
                        if (!empty($course->prof_userid)) {
                            $prof_name = (current_language()=='ko')? fullname($course):$course->eng_name;
                            echo '<div class="prof_name" style="padding-bottom: 4px">' . $prof_name . '</div>';
                        } else {
                            echo '-';
                        }
                        ?>
                    </td>
                </tr>
                <?php
            }
        } else {
            if($type == 3){
            echo '<tr><td colspan="3">' . get_string('course:empty', 'local_okirregular') . '</td></tr>';
            } else {
            echo '<tr><td colspan="3">' . get_string('course:empty1', 'local_okirregular') . '</td></tr>';
            }
        }
        ?>
    </tbody>
</table>
<!-- Table End -->

<div class="table-footer-area">
    <?php
    local_okirregular_print_paging_navbar_script($totalcount, $page, $perpage, 'javascript:goto_page(:page);', 10);
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