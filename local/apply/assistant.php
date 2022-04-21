<?php
require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/my/lib.php');
require_once($CFG->dirroot . '/local/lmsdata/lib.php');
require_once($CFG->dirroot . '/siteadmin/lib.php');
//require_once($CFG->dirroot . '/local/coursepoint/index.php');
// TODO Add sesskey check to edit
$edit = optional_param('edit', null, PARAM_BOOL);    // Turn editing on and off
$reset = optional_param('reset', null, PARAM_BOOL);
$type = optional_param('type', 0, PARAM_INT); // 페이지 타입
$coursetype = optional_param('coursetype', 3, PARAM_INT); // 강좌타입
$year = optional_param('courseyear', '', PARAM_RAW); // 강의년도
$term = optional_param('courseterm', 0, PARAM_INT); // 학기
$searchvalue = optional_param('searchvalue', '', PARAM_RAW); // 강좌명
$searchtype = optional_param('searchtype', 0, PARAM_INT); // 검색타입
$perpage = optional_param('perpage', 10, PARAM_INT); // 보여줄 개수
$page = optional_param('page', 1, PARAM_INT); // 페이지

require_login();

$hassiteconfig = has_capability('moodle/site:config', context_system::instance());
if ($hassiteconfig && moodle_needs_upgrading()) {
    redirect(new moodle_url('/admin/index.php'));
}

$strmymoodle = $SITE->fullname;

$userid = $USER->id;  // Owner of the page
$context = context_user::instance($USER->id);
$PAGE->set_blocks_editing_capability('moodle/my:manageblocks');
$header = fullname($USER);
$pagetitle = $strmymoodle;

if (!$currentpage = my_get_page($userid, MY_PAGE_PRIVATE)) {
    print_error('mymoodlesetup');
}

$PAGE->requires->jquery();
$PAGE->requires->jquery_plugin('ui');
$PAGE->requires->jquery_plugin('ui-css');

// Start setting up the page
$params = array();
$PAGE->set_context($context);
$PAGE->set_url('/local/apply/assistant.php', $params);
$PAGE->set_pagelayout('standard');
$PAGE->blocks->add_region('content');
$PAGE->set_subpage($currentpage->id);
$PAGE->set_title($pagetitle);
$PAGE->set_heading($header);

echo $OUTPUT->header();
?>
<div id="mypage_course_area">
    <div id="frontpage-course-list"> 
        <h2><?php echo get_string('major_auditor', 'local_lmsdata'); ?></h2>
        <div class="course_search">
            <form method="get" class="table-search-option"> 
                <?php
//                if (!$year) {
//                    $year = date('Y');
//                }
//검색으로 나오는 년도, 학기
                $syear = optional_param('syear', $year, PARAM_INT);
                $sterm = optional_param('sterm', $term, PARAM_INT);
                $sql_conditions[] = 'lc.year = :year';
                $sql_params['year'] = $syear;
                ?>

                <div style="clear:both;" class="course_search">                          
                    <label><?php echo get_string('year_term', 'local_lmsdata'); ?></label>
                    <select name="courseyear">
                        <option value=""><?php echo get_string('all', 'local_lmsdata'); ?></option>
                        <?php
                        $currenyear = date('Y');
                        for ($i = $currenyear; $i >= 2015; $i--) {
                            $params = array('value' => $i);
                            if ($year == $i) {
                                $params['selected'] = 'selected';
                            }
                            echo html_writer::tag('option', $i . get_string('contents_year', 'local_lmsdata'),$params);
                        }
                        ?>
                    </select>   

                    <select name="courseterm" >
                        <option value="0"><?php echo get_string('all', 'local_lmsdata'); ?></option>
                        <?php
                                $terms = array(
                                    '1' => '1학기',
                                    '3' => '여름학기',
                                    '2' => '2학기',
                                    '4' => '겨울학기'
                                );
                                
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
                    <?php
                    //echo html_writer::tag('h6', get_string('coursename','local_lmsdata'), array('class'=>'courseselecter'));
                    echo html_writer::start_tag('select', array('name' => 'searchtype', 'class' => 'courseselecter', "title" => "searchtype"));
                    $params = array('value' => 0);
                    if ($searchtype == 0) {
                        $params['selected'] = 'selected';
                    }
                    echo html_writer::tag('option', get_string('coursename', 'local_lmsdata'), $params);
                    $params = array('value' => 1);
                    if ($searchtype == 1) {
                        $params['selected'] = 'selected';
                    }
                    echo html_writer::tag('option', get_string('teachername', 'local_lmsdata'), $params);
                    echo html_writer::end_tag('select');
                    echo html_writer::tag('input', '', array("type" => "text", 'value' => $searchvalue, "name" => "searchvalue", 'class' => 'courseselecter', "title" => "searchvalue"));
                    echo html_writer::tag('input', '', array("type" => "submit", 'value' => get_string('search', 'moodle'), 'class' => 'courseselecter', "title" => "courseselecter"));
                    ?>

                </div>
            </form>
            <div class="options">
                <input type="button" class="btn_st01" value="<?php echo get_string('application', 'local_lmsdata'); ?>" onclick="location.href = 'apply.php';">
            </div>

        </div><!--course_search--> 
        <div class="table_group">
            <table class="generaltable regular-courses">
                <thead>
                    <tr>
                        <th width="7%"><?php echo get_string('stats_years', 'local_lmsdata'); ?></th>
                        <th width="7%"><?php echo get_string('stats_terms', 'local_lmsdata'); ?></th>
                        <th width="7%"><?php echo get_string('department', 'local_lmsdata'); ?></th>
                        <th width=""><?php echo get_string('stats_coursename', 'local_lmsdata'); ?></th>
                        <th width="20%"><?php echo get_string('teacher', 'local_lmsdata'); ?></th>
                        <th width="7%"><?php echo get_string('assistant_apply', 'local_lmsdata'); ?></th>
                        <th width="7%"><?php echo get_string('auditor_apply', 'local_lmsdata'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $where = 'where apy.id is null ';
                    $on = '';
                    $param = array();
                    if ($year) {
                        $on = ' and lc.year  = :year ';
                        $param['year'] = $year;
                    }

                    if ($sterm != 0) {
                        $on .= ' and lc.term = :term ';
                        $param['term'] = $sterm;
                    }
                    $query = 'select c.id,c.fullname,lc.year,lc.term, u.firstname , u.lastname , lc.ohakkwa from {course} c '
                            . 'join {lmsdata_class} lc on lc.course = c.id ' . $on
                            . 'join {user} u on u.id = lc.prof_userid '
                            . 'left join {approval_reason} apy on apy.courseid = c.id ';
                    if ($searchvalue) {
                        switch ($searchtype) {
                            case 0: $where = ' where  c.fullname like :search';
                                $param['search'] = '%' . $searchvalue . '%';
                                break;
                            case 1: $where = ' where  (u.firstname like :search or u.lastname like :search2 or CONCAT(u.firstname,u.lastname) like :search3)';
                                $param['search'] = '%' . $searchvalue . '%';
                                $param['search2'] = '%' . $searchvalue . '%';
                                $param['search3'] = '%' . $searchvalue . '%';
                                break;
                        }
                    }


                    $cnt_query = 'select count(c.id) from {course} c '
                            . 'join {lmsdata_class} lc on lc.course = c.id ' . $on
                            . 'join {user} u on u.id = lc.prof_userid '
                            . 'left join {approval_reason} apy on apy.courseid = c.id ';
                    $offset = ($page - 1) * $perpage;

                    $courses = $DB->get_records_sql($query . $where . ' order by id desc', $param, $offset, $perpage);
                    $total_count = $DB->count_records_sql($cnt_query . $where, $param);
                    $num = $total_count - $offset;
                    $terms = lmsdata_get_terms();
                   
                    foreach ($courses as $course) {
                        $role = $DB->get_record('role', array('shortname' => 'editingteacher'));
                        $course_context = context_course::instance($course->id);
                        $teachers = get_role_users($role->id, $course_context);
                        $name = '';

                        foreach ($teachers as $teacher) {
                            $name .= fullname($teacher) . ',';
                        }
                        $name = rtrim($name, ',');
                        ?>
                        <tr>
                            <td><?php echo $course->year; ?></td>
                            <td><?php echo isset($terms[$course->term]) ? $terms[$course->term] : '-'; ?></td>
                            <td><?php echo $course->ohakkwa ? $course->ohakkwa : '-';  ?></td>
                            <td style="text-align: left; cursor: pointer;" onclick="course_preview('<?php echo $course->id; ?>')"><?php echo $course->fullname; ?></td>
                            <td><?php echo $name; ?></td>
                            <td><input type="button"  class="btn_st01" value="신청" onclick="location.href = 'apply_view.php?id=<?php echo $course->id ?>&role=assistant'"></td>
                            <td><input type="button" class="btn_st01" value="신청" onclick="location.href = 'apply_view.php?id=<?php echo $course->id ?>&role=auditor'"></td>
                        </tr>
                        <?php
                    }

                    if (!$courses) {
                        echo '<tr><th colspan="7">신청가능한 강좌가 없습니다.</th></tr>';
                    }
                    ?>
                </tbody>
            </table>
            <div>
                <?php
                $baseurl = '/local/apply/assistant.php';
                $params = array('searchtype' => $searchtype, 'searchvalue' => $searchvalue, 'year' => $year, 'term' => $term);
                echo my_print_paging_navbar($total_count, $page, $perpage, $baseurl, $params);
                ?>
            </div>
        </div><!--table_group-->
    </div><!--frontpage-course-list-->
</div><!--mypage_course_area-->
<script type="text/javascript">
    function course_preview(courseid) {
        var tag = $("<div id='course_preview'></div>");
        $.ajax({
            url: '<?php echo $CFG->wwwroot . '/local/apply/assistance_ajax.php'; ?>',
            method: 'POST',
            data: {
                id: courseid
            },
            success: function (data) {
                tag.html(data).dialog({
                    title: '<?php echo get_string('course_preview', 'local_lmsdata') ?>',
                    modal: true,
                    width: 800,
                    resizable: false,
                    height: 500,
                    buttons: [
                        {id: 'close',
                            text: '닫기',
                            disable: true,
                            click: function () {
                                $(this).dialog("close");
                            }}

                    ],
                    close: function () {
                        $(this).dialog('destroy').remove();
                    }
                }).dialog('open');
            }
        });
    }

</script>

<?php
echo $OUTPUT->footer();
?>