<?php
require(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once $CFG->dirroot . '/local/oklearning/lib.php';
require_once $CFG->libdir . '/formslib.php';

require_login();

$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_pagelayout('standard');

$PAGE->requires->jquery();
$PAGE->requires->jquery_plugin('ui');
$PAGE->requires->jquery_plugin('ui-css');
$PAGE->requires->jquery_plugin('migrate');

$PAGE->set_url('/local/oklearning/my.php');

$strplural = get_string("course:my", "local_oklearning");
$PAGE->navbar->add(get_string("pluginnameplural", "local_oklearning"), new moodle_url($CFG->wwwroot.'/local/oklearning/my.php'));
$PAGE->navbar->add($strplural);
$PAGE->set_title($strplural);
$PAGE->set_heading(get_string('mycommunity','local_oklearning'));

echo $OUTPUT->header();

$page = optional_param('page', 1, PARAM_INT);
$perpage = optional_param('perpage', 10, PARAM_INT);
$searchtext = optional_param('searchval', '', PARAM_RAW);
$search = optional_param('searchfield', 3, PARAM_INT);
$current_course = optional_param('courseid', 1, PARAM_INT);
$type = optional_param('type', 1, PARAM_INT); //1: 진행, 2: 대기, 3: 종료

$sql_select = "SELECT mc.id
     , mc.fullname AS course_name
     , lc.eng_lec_name AS course_name_eng
     , lc.subject_id, lc.year, lc.term 
     , lc.domain
     , lc.timestart 
     , lc.timeend 
     , lc.prof_userid
     , ca.name AS category_name 
     , ca.path AS category_path 
    , u.firstname, u.lastname, lu.eng_name 
    , en.id enrol_id";
$sql_from = " FROM {course} mc
JOIN {context} ctx ON ctx.instanceid = mc.id AND ctx.contextlevel = :contextlevel 
JOIN {lmsdata_class} lc ON lc.course = mc.id 
JOIN {course_categories} ca ON ca.id = mc.category 
JOIN {enrol} en ON en.courseid = mc.id 
JOIN {user_enrolments} ue ON ue.enrolid = en.id 
LEFT JOIN {user} u ON u.id = lc.prof_userid 
LEFT JOIN {lmsdata_user} lu ON lu.userid = u.id  ";

$sql_conditions = array('lc.isnonformal = :isnonformal','ue.userid = :userid');
$sql_params = array(
    'contextlevel' => CONTEXT_COURSE,
    'userid' => $USER->id,
    'isnonformal' => COURSE_TYPE);

$currentdate = time();
if($type == 1){
    //진행
    $sql_conditions[] = "lc.timestart <= :timestart and lc.timeend >= :timeend and ue.status = 0";    
}else if($type == 2){
    //대기
    $sql_conditions[] = "lc.timestart <= :timestart and lc.timeend >= :timeend and ue.status = 1";    
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

$totalcount = $DB->count_records_sql('SELECT COUNT(*) ' . $sql_from . $sql_where, $sql_params);
$courses = $DB->get_records_sql($sql_select . $sql_from . $sql_where . $sql_orderby, $sql_params, ($page - 1) * $perpage, $perpage);
$context = context_system::instance();
$isadmin = has_capability('moodle/site:config', $context);

//tab
$row = array();
$row[] = new tabobject('1', "$CFG->wwwroot/local/oklearning/my.php?type=1", get_string('ongoing', 'local_oklearning'));
$row[] = new tabobject('2', "$CFG->wwwroot/local/oklearning/my.php?type=2", get_string('standby', 'local_oklearning'));
$row[] = new tabobject('3', "$CFG->wwwroot/local/oklearning/my.php?type=3", get_string('finish', 'local_oklearning'));
$rows[] = $row;

print_tabs($rows, $type);
?>
<!-- Table Area Start -->
<form class="table-search-option" id="frm_course">
    <input type="text" title="course" name="searchval" placeholder="<?php echo get_string('search:coursename', 'local_oklearning'); ?>" value="<?php echo $searchtext; ?>"/>
    <input type="submit" value="<?php echo get_string('search', 'local_oklearning'); ?>"  class="board-search" onclick="javascript:course_all_select_submit();"/>
    <input type="hidden" name = "page" value="1">
    <input type="hidden" name = "type" value="<?php echo $type; ?>">
    <input type="hidden" name = "perpage" value="<?php echo $perpage; ?>">
</form>

<!-- Table Start -->
<table class="generaltable" id="table_courses">
    <caption class="hidden-caption"><?php echo get_string('course:name', 'local_oklearning'); ?></caption>
    <thead>
        <tr>
            <th scope="row"><?php echo get_string('course:name', 'local_oklearning'); ?></th>
            <th scope="row" width="25%"><?php echo get_string('course:date', 'local_oklearning'); ?></th>
            <th scope="row" width="15%"><?php echo get_string('course:applicant', 'local_oklearning'); ?></th>
            <th scope="row" width="15%"><?php echo get_string('note', 'local_oklearning'); ?></th>
        </tr>
    </thead>
    <tbody>
        <?php
        if ($totalcount > 0) {
            $root_categories = $DB->get_records_menu('course_categories', array('parent' => 0), '', 'id, name');

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
                    <td scope="col">
                        <?php
                        if($course->prof_userid == $USER->id){
                            echo '<input type="button" class="gray_btn_small" value="'.get_string('edit','local_oklearning').'" onclick="location.href=\'course_add.php?id='.$course->id.'\';"/>';
                            echo '<input type="button" id="course_delete" class="gray_btn_small" value="'.get_string('del_course','local_oklearning').'" onclick="javascript:course_delect('.$course->enrol_id.',1,'.$course->id.');"/>';
                        }else{
                            echo '<input type="button" id="course_delete" class="gray_btn_small" value="'.get_string('course:delete','local_oklearning').'" onclick="javascript:course_delect('.$course->enrol_id.',2);"/>';
                        }
                        ?>
                    </td>
                </tr>
                <?php
            }
        } else {
            echo '<tr><td scope="col" colspan="4">' . get_string('course:empty', 'local_oklearning') . '</td></tr>';
        }
        ?>
    </tbody>
</table>
<!-- Table End -->

<div class="table-footer-area">
    <?php
    local_oklearning_print_paging_navbar_script($totalcount, $page, $perpage, 'javascript:goto_page(:page);', 10);
    ?>
</div>

<script type="text/javascript">
    function course_delect(enrolid, status, courseid) {
        if (confirm("강좌를 삭제하시겠습니까?")) {
            $.ajax({
                url: '<?php echo $SITECFG->wwwroot . '/local/oklearning/course_delect.ajax.php'; ?>',
                type: 'POST',
                dataType: 'json',
                async: false,
                data: {
                    enrolid: enrolid,
                    status: status,
                    courseid: courseid,
                },
                success: function (data, textStatus, jqXHR) {
                    if (data.status == 'success') {
                        alert("<?php echo get_string('cancelapply', 'local_oklearning') ?>.");
                        location.reload();
                    } else {
                        alert(data.message);
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    alert(jqXHR.responseText);
                }
            });
        }

        return status;
    }
    
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