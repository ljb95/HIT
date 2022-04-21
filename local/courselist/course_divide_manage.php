<?php
require(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once $CFG->dirroot . '/siteadmin/lib/paging.php';
require_once $CFG->dirroot . '/siteadmin/lib.php';
require_once $CFG->dirroot . '/local/courselist/lib.php';

require_login();
$currpage = optional_param('page', 1, PARAM_INT);
$perpage = optional_param('perpage', 99999, PARAM_INT);
// 현재 년도, 학기

$year = get_config('moodle', 'haxa_year');
$term = get_config('moodle', 'haxa_term');

$context = context_system::instance();
$user = $DB->get_record('lmsdata_user', array('userid' => $USER->id));
if (($user->usergroup != 'pr') && ($user->usergroup != 'sa') && !is_siteadmin($USER)) {
    redirect($CFG->wwwroot);
}

$sql_select = "SELECT mc.id, mc.fullname, mc.shortname
     , lc.subject_id, lc.sbb, lc.isnonformal, lc.year, lc.term
     , (SELECT COUNT(*) 
        FROM {role_assignments} ra
        JOIN {role} ro ON ra.roleid = ro.id
        JOIN {context} ctx ON ra.contextid = ctx.id
        JOIN {course} co ON ctx.instanceid = co.id AND contextlevel = :contextlevel1
        WHERE co.id = mc.id
          AND ro.shortname = 'student') AS students ";
$sql_from = " FROM {course} mc
JOIN {lmsdata_class} lc ON lc.course = mc.id
JOIN {course_categories} ca ON ca.id = mc.category
JOIN {context} ctx ON mc.id = ctx.instanceid 
JOIN {role_assignments} ra ON ra.contextid = ctx.id and ra.roleid = 3 ";

$sql_where = " WHERE ((lc.year = :year 
  AND lc.term = :term ) or (lc.year= 9999)) and ctx.contextlevel =:contextlevel2 and ra.userid =:userid ";

$page_params = array();
$params = array(
    'year' => $year,
    'term' => $term,
    'contextlevel1' => CONTEXT_COURSE,
    'contextlevel2' => CONTEXT_COURSE,
    'userid' => $USER->id
);


$courses = $DB->get_records_sql($sql_select . $sql_from . $sql_where, $params, ($currpage - 1) * $perpage, $perpage);
$count_courses = $DB->count_records_sql("SELECT COUNT(*) " . $sql_from . $sql_where, $params);

$PAGE->set_context($context);

$PAGE->set_url('/local/courselist/course_divide_manage.php');
$PAGE->set_pagelayout('standard');

$PAGE->requires->jquery();
$PAGE->requires->jquery_plugin('ui');
$PAGE->requires->jquery_plugin('ui-css');
$PAGE->requires->jquery_plugin('migrate');
$PAGE->requires->css('/siteadmin/css/loading.css');
$PAGE->requires->js('/siteadmin/js/loading.js');

$strplural = get_string("merge:manage", "local_courselist");
$PAGE->navbar->add($strplural);
$PAGE->set_title($strplural);
$PAGE->set_heading($COURSE->fullname);
echo $OUTPUT->header();

//tab
$tabmenu = trim(basename($_SERVER['PHP_SELF']), '.php');
if ($tabmenu === 'course_divide_manage') {
    $currenttab = 'manage';
} else if ($tabmenu === 'course_list_drive') {
    $currenttab = 'list_drive';
} else if ($tabmenu === 'course_list_restore') {
    $currenttab = 'list_restore';
}

$rows = array(
    new tabobject('manage', "$CFG->wwwroot/local/courselist/course_divide_manage.php", get_string('merge:manage', 'local_courselist')),
    new tabobject('list_drive', "$CFG->wwwroot/local/courselist/course_list_drive.php", get_string('course:classes_drive_log', 'local_courselist')),
    new tabobject('list_restore', "$CFG->wwwroot/local/courselist/course_list_restore.php", get_string('course:classes_restore_log', 'local_courselist'))
);
print_tabs(array($rows), $currenttab);
?>
<h2><?php echo $strplural; ?></h2>
<table class="general" id="course_divide_manage">
    <thead>
        <tr>
            <th class="col-1"><input type="checkbox" onclick="check_course_id(this, 'courseid')"/></th>
            <th class="col-2"><?php echo get_string('no', 'local_courselist'); ?></th>
            <th class="col-3"><?php echo get_string('course:subjectid', 'local_courselist'); ?></th>
            <th class="col-4"><?php echo get_string('section', 'local_courselist'); ?></th>
            <th class="col-5"><?php echo get_string('course:name', 'local_courselist'); ?></th>
            <th class="col-6"><?php echo get_string('regular:short', 'local_courselist') . '/' . get_string('irregular:short', 'local_courselist'); ?></th>
            <th class="col-7"><?php echo get_string('course:credit', 'local_courselist'); ?></th>
            <th class="col-8"><?php echo get_string('students', 'local_courselist'); ?></th>
            <th class="col-9"><?php echo get_string('course:delete', 'local_courselist'); ?></th>
        </tr>
    </thead>
    <tbody>
        <?php if ($count_courses === 0) { ?>
            <tr>
                <td colspan="11"><?php echo get_string('course:empty', 'local_courselist'); ?></td>
            </tr>
        <?php
        } else {
            $startnum = $count_courses - (($currpage - 1) * $perpage);
            foreach ($courses as $course) {
                $sub_exname = explode("-", $course->subject_id);
                $sub_name = "";
                if (!empty($course->subject_id)) {
                    if (!empty($sub_exname[0])) {
                        $sub_name = $sub_exname[0];
                    } else {
                        $sub_name = $course->subject_id;
                    }
                } else {
                    $sub_name = "-";
                }

                if (($course->year != "9999") && ($course->term != "0")) {

                    if ($course->term == 1 || $course->term == 2) {
                        $term = $i . get_string('term', 'local_lmsdata');
                    } else if ($course->term == 3 || $course->term == 4) {
                        $term = str_replace(array(3, 4), array(get_string('summer', 'local_okregular')), $course->term);
                    } else {
                        $term = '-';
                    }

                    $year_term = get_string('year', 'local_courselist', $course->year) . $term;
                } else {
                    $year_term = get_string("constant", "local_courselist");
                }
                ?>
                <tr>
                    <td class="col-1"><input type="checkbox" class="courseid" name="courseid" value="<?php echo $course->id; ?>"/></td>
                    <td class="col-2"><?php echo $startnum--; ?></td>
                    <?php if (!empty($course->sbb) && $course->sbb != '00') { ?>
                        <td class="col-3"><?php echo $course->subject_id; ?>-<?php echo $course->sbb; ?></td>
                    <?php } else { ?>
                        <td class="col-3"><?php echo $course->subject_id; ?></td>
                    <?php } ?>
                    <td class="col-4"><?php echo $year_term; ?></td>
                    <td class="left col-5"><a href="<?php echo $CFG->wwwroot . '/course/view.php?id=' . $course->id; ?>"><?php echo $course->fullname; ?></a></td>
                    <td class="col-6"><?php echo $course->isnonformal == 1 ? get_string('irregular:short', 'local_courselist') : get_string('regular:short', 'local_courselist') ?></td>
                    <td class="col-7">-</td>
                    <td class="col-8"><?php echo $course->students; ?></td>
                    <td class="col-9"><?php if ($course->isnonformal) {
                echo '<input type="button" class="red_btn" value="' . get_string('course:delete', 'local_courselist') . '" onclick="course_delete(' . $course->id . ')"/>';
            } ?></td>
                </tr>
    <?php }
} ?>
    </tbody>
</table><!--Table End-->
<div class="table-footer-area">
    <div class="btn-area btn-area-left"> 
        <input type="submit" class="red-form" style="float:left; margin-right: 10px;" value="<?php echo get_string('merge:sel', 'local_courselist'); ?>" onclick="split_course_dialog();"/>
    </div>
</div>     
<?php
echo $OUTPUT->footer();
?>

<script type="text/javascript">
    function split_course_dialog() {

        var tag = $("<div id='split_course_dialog'></div>");
        var split_list = [];
        var count = 0;
        var standardId;
        $(".courseid").each(function (index, element) {
            if ($(this).is(":checked")) {
                split_list.push($(this).val());
                count++;
            }
        });

        if (count < 2) {
            alert("<?php echo get_string('course:sel_more', 'local_courselist'); ?>");
            return false;
        }

        $.ajax({
            url: '<?php echo $CFG->wwwroot . "/local/courselist/course_list_sel.course.ajax.php" ?>',
            method: 'POST',
            data: {
                data: split_list
            },
            success: function (data) {
                tag.html(data).dialog({
                    title: '<?php echo get_string('course:classes_drive', 'local_courselist'); ?>',
                    modal: true,
                    width: 600,
                    resizable: false,
                    height: 400,
                    buttons: [{id: 'drive_course',
                            text: '<?php echo get_string('course:classes_drive', 'local_courselist'); ?>',
                            disable: true,
                            click: function () {
                                if (jQuery("input[name=course_standard]:radio:checked").length == 0) {
                                    alert("");
                                } else {
                                    standardId = $("input:radio[name=course_standard]:checked").val();
                                    split_course_execute(standardId, split_list);
                                    $('#drive_course_select').remove();
                                    $(this).dialog('destroy').remove();
                                }
                            }},
                        {id: 'close',
                            text: '<?php echo get_string('cancel', 'local_courselist'); ?>',
                            disable: true,
                            click: function () {
                                $(this).dialog("close");
                            }}
                    ],
                    close: function () {
                        $('#drive_course_select').remove();
                        $(this).dialog('destroy').remove()
                    }
                }).dialog('open');
            }
        });
    }

    function split_course_execute(standard, split_list) {
        $.ajax({
            url: '<?php echo $CFG->wwwroot . "/local/courselist/course_list_drive.execute.php" ?>',
            method: 'POST',
            data: {
                standard: standard,
                split_list: split_list,
            },
            success: function (data) {
                if (data == 0) {
                    alert("<?php echo get_string('user:nomove', 'local_courselist'); ?>");
                } else {
                    alert(data + "<?php echo get_string('user:move', 'local_courselist'); ?>");
                }
                document.location.href = "<?php echo $CFG->wwwroot . "/local/courselist/course_divide_manage.php" ?>";
            }
        });
    }

    function course_delete(courseid) {
        if (confirm("<?php echo get_string('deletecoursecheck'); ?>") == true) {
            $.ajax({
                url: '<?php echo $CFG->wwwroot . "/local/courselist/course_delete.execute.php" ?>',
                method: 'POST',
                data: {
                    id: courseid,
                },
                success: function (data) {
                    document.location.href = "<?php echo $CFG->wwwroot . "/local/courselist/course_divide_manage.php" ?>";
                }
            });
        }
    }

    function check_course_id(check, checkClass) {
        if ($(check).is(":checked")) {
            $("." + checkClass).each(function () {
                this.checked = true;
            });
        } else {
            $("." + checkClass).each(function () {
                this.checked = false;
            });
        }
    }

    function radio_check(courseid) {
        $("input:checkbox[name=course_invisible]").each(function (index, element) {
            if ($(this).val() != courseid) {
                this.checked = true;
                this.disabled = false;
            } else {
                this.checked = false;
                this.disabled = true;
            }
        });
    }


</script>    