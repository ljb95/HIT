<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once $CFG->dirroot . '/siteadmin/lib/paging.php';
require_once $CFG->dirroot . '/siteadmin/lib.php';
require_once("$CFG->dirroot/enrol/locallib.php");
require_once $CFG->dirroot . '/local/courselist/lib.php';

//require_login();
$id = optional_param('id', 0, PARAM_INT);
$page = optional_param('page', 1, PARAM_INT);
$perpage = optional_param('perpage', 10, PARAM_INT);
$searchtext = optional_param('searchtext', '', PARAM_RAW);
$status = optional_param('status', 0, PARAM_INT);
$complete = optional_param('complete', 0, PARAM_INT);
// 현재 년도, 학기

$page_param = array(
    'id' => $id,
    'searchtext' => $searchtext,
    'status' => $status,
    'complete' => $complete
);
$year = get_config('moodle', 'haxa_year');
$term = get_config('moodle', 'haxa_term');

$context = context_course::instance($id);
$user = $DB->get_record('lmsdata_user', array('userid' => $USER->id));
if (!is_siteadmin($USER) && ($user->usergroup != 'pr') && ($user->usergroup != 'sa')) {
    redirect($CFG->wwwroot);
}

//역할코드 가져오기
$roleid_student = $DB->get_field('role', 'id', array('shortname' => 'student'));
$roleid_auditor = $DB->get_field('role', 'id', array('shortname' => 'auditor'));

//수강생 목록
$sql_select = "SELECT ue.id
      , en.enrol
      , ue.userid
      , ur.username
      , CONCAT(ur.firstname,ur.lastname) as fullname
      , ur.email
      , lu.major
      , ue.status as approval
      , CASE WHEN ra.id IS NULL THEN 0 ELSE 1 END AS assignment
      , cic.complete
      , cic.timecreated
      , cic.timemodified, ra.roleid ";
$sql_from = " FROM {user_enrolments} ue 
JOIN {enrol} en ON en.id = ue.enrolid
JOIN {user} ur ON ur.id = ue.userid
LEFT JOIN {lmsdata_user} lu ON lu.userid = ue.userid
LEFT JOIN {role_assignments} ra ON ra.userid = ue.userid
LEFT JOIN {course_irregular_complete} cic ON cic.courseid = en.courseid and cic.userid = ue.userid ";


$page_params = array();
$params = array(
    'courseid' => $id,
    'enabled' => 0,
    'contextlevel' => CONTEXT_COURSE,
    'contextid' => $context->id,
    'roleid1' => $roleid_student,
    'roleid2' => $roleid_auditor
);

$sql_where = array();
$sql_where[] = " en.courseid = :courseid and en.status = :enabled and ra.contextid = :contextid and (ra.roleid = :roleid1 or ra.roleid = :roleid2) ";

//수강여부
if (!empty($status)) {
    $sql_where[] = ' ue.status = :status';
    if ($status == 1) {
        $params['status'] = 0;
    } else if ($status == 2) {
        $params['status'] = 1;
    }
}

//이수여부
if (!empty($complete)) {
    if ($complete == 1) {
        $sql_where[] = ' cic.complete = :complete ';
        $params['complete'] = 1;
    } else {
        $sql_where[] = ' (cic.complete = :complete or cic.complete is null) ';
        $params['complete'] = 0;
    }
}

//검색어
if (!empty($searchtext)) {
    $like_name = $DB->sql_like('CONCAT(ur.firstname,ur.lastname)', ':fullname');
    $like_hakbun = $DB->sql_like('ur.username', ':username');
    $sql_where[] = '(' . $like_name . ' or ' . $like_hakbun . ')';
    $params['fullname'] = '%' . $searchtext . '%';
    $params['username'] = '%' . $searchtext . '%';
}

$sql_where = ' WHERE ' . implode(' AND ', $sql_where);
$sql_sort = ' order by CONCAT(ur.firstname,ur.lastname) asc ';

$users = $DB->get_records_sql($sql_select . $sql_from . $sql_where . $sql_sort, $params, ($page - 1) * $perpage, $perpage);
$count_users = $DB->count_records_sql("SELECT COUNT(*) " . $sql_from . $sql_where, $params);

//강의정보
$course_info = $DB->get_record_sql('SELECT lc.*, cc.name as category_name FROM {lmsdata_class} lc JOIN {course_categories} cc ON cc.id = lc.category WHERE lc.course =:course ', array('course' => $id));

//비교과과정인지 확인 1 = true
$isnonformal = $course_info->isnonformal;

// 교수 이름 목록
$prof_sql = " SELECT CONCAT(ur.firstname,ur.lastname) as fullname 
            FROM {context} co
            JOIN {role_assignments} ra ON co.id = ra.contextid
            JOIN {user} ur ON ur.id = ra.userid
            WHERE co.contextlevel = :contextlevel and co.instanceid = :courseid and ra.roleid = :roleid order by fullname asc";

$prof_params = array(
    'contextlevel' => CONTEXT_COURSE,
    'courseid' => $id,
    'roleid' => 3,
);

$prof_names = $DB->get_fieldset_sql($prof_sql, $prof_params);
$profs = implode(',', $prof_names);

$PAGE->set_context($context);

$PAGE->set_url('/local/courselist/course_students_list.php');
$PAGE->set_pagelayout('standard');

$PAGE->requires->jquery();
$PAGE->requires->jquery_plugin('ui');
$PAGE->requires->jquery_plugin('ui-css');
$PAGE->requires->jquery_plugin('migrate');
$PAGE->requires->css('/siteadmin/css/loading.css');
$PAGE->requires->js('/siteadmin/js/loading.js');
$PAGE->requires->css('/local/courselist/style.css');

$strplural = get_string("course:participants_list", "local_courselist");
$PAGE->navbar->add(get_string("course:manage", "local_courselist"), new moodle_url($CFG->wwwroot . '/local/courselist/course_manage.php'));
$coursetypetext = ($isnonformal == 1) ? get_string('irregular', 'local_courselist') : get_string('regular', 'local_courselist');
$PAGE->navbar->add($coursetypetext, new moodle_url($CFG->wwwroot . '/local/courselist/course_manage.php?coursetype=' . $isnonformal));
$PAGE->navbar->add($strplural);
$PAGE->set_title($strplural);
$PAGE->set_heading($strplural);
echo $OUTPUT->header();
?>

<div class="table-filter-area">
    <input type="button"  value="<?php echo get_string('return', 'local_courselist'); ?>" onclick="javascript:location.href = '<?php echo $CFG->wwwroot . "/local/courselist/course_manage.php?coursetype=" . $isnonformal; ?>'">
</div>

<form class="table-search-option" id="frm_participants" method="post" action="">
    <input type="hidden" name = "id" value="<?php echo $id; ?>">
    <input type="hidden" name = "page" value="<?php echo $page; ?>">
    <input type="hidden" name = "perpage" value="<?php echo $perpage; ?>">
    <select class="select" name="status" onchange="this.form.submit()">
        <option value="0" <?php echo $status == 0 ? "selected" : "" ?>> <?php echo get_string('course:enrol_check', 'local_courselist'); ?></option>
        <option value="1" <?php echo $status == 1 ? "selected" : "" ?>> <?php echo get_string('course:registered', 'local_courselist'); ?></option>
        <option value="2" <?php echo $status == 2 ? "selected" : "" ?>> <?php echo get_string('course:approval', 'local_courselist'); ?></option>
    </select>
    <select class="select complete" name="complete" onchange="this.form.submit()">
        <option value="0" <?php echo $complete == 0 ? "selected" : "" ?>> <?php echo get_string('user:complete_status', 'local_courselist'); ?></option>
        <option value="1" <?php echo $complete == 1 ? "selected" : "" ?>> <?php echo get_string('user:complete', 'local_courselist'); ?></option>
        <option value="2" <?php echo $complete == 2 ? "selected" : "" ?>> <?php echo get_string('user:incomplete', 'local_courselist'); ?></option>
    </select> 
    <input type="text" name="searchtext" class="search_text w-200" placeholder="<?php echo get_string('user:search', 'local_courselist'); ?>" value="<?php echo!empty($searchtext) ? $searchtext : ""; ?>">
    <input type="submit" value="<?php echo get_string('search', 'local_courselist'); ?>" class="board-search">
    <input type="button" value="<?php echo get_string('export:excel', 'local_courselist'); ?>" class="excel_export" onclick="course_students_list_export(this.form)">
</form>
<table class="generaltable" id="user_participants">
    <thead>
        <tr>
            <th><input type="checkbox" onclick="check_user_id(this, 'userid')"/></th>
            <th><?php echo get_string('user:number', 'local_courselist'); ?></th>
            <th><?php echo get_string('user:name', 'local_courselist'); ?></th>
            <th><?php echo get_string('user:major', 'local_courselist'); ?></th>
            <th><?php echo get_string('user:email', 'local_courselist'); ?></th>               
            <th><?php echo get_string('course:enrol', 'local_courselist'); ?></th>
<?php if ($isnonformal == 1) { ?><th><?php echo get_string('user:complete', 'local_courselist'); ?></th><?php } ?>
        </tr>
    </thead>
    <tbody>
<?php if ($count_users === 0) { ?>
            <tr>
                <td colspan="<?php echo $isnonformal ? 7 : 6 ?>"><?php echo get_string('user:empty', 'local_courselist'); ?></td>
            </tr>
            <?php
        } else {

            foreach ($users as $user) {
                ?>
                <tr>
                    <td><input type="checkbox" class="userid" name="userid" value="<?php echo $user->userid; ?>"/></td>
                    <td><?php echo $user->username; ?></td>
                    <td><?php echo $user->fullname; ?></td>
                    <td><?php echo $user->major; ?></td>
                    <td><?php echo $user->email; ?></td>
                    <td>
                        <?php
                        if ($user->approval == 0) {
                            echo get_string('course:registered', 'local_courselist');
                        } else if ($user->approval == 1) {
                            echo get_string('course:wait', 'local_courselist');
                        };
                        ?>
                    </td>
                    <?php
                    if ($isnonformal == 1) {
                        echo '<td>';
                        if (!empty($user->complete)) {
                            echo date('Y-m-d', $user->timemodified);
                        } else {
                            if ($user->approval == 0) {
                                echo get_string('user:incomplete', 'local_courselist');
                            } else if ($user->approval == 1) {
                                echo '-';
                            };
                        };
                        echo '</td>';
                    }
                    ?>
                </tr>
    <?php }
}
?>
    </tbody>
</table><!--Table End-->
<div class="table-footer-area">
    <div class="btn-area btn-area-left"> 
        <input type="button" class="red-form" style="float:left; margin-right: 10px;" value="<?php echo get_string('course:app', 'local_courselist'); ?>" onclick="approval_course(true);"/>
        <input type="button" class="red-form" style="float:left; margin-right: 10px;" value="<?php echo get_string('course:approval_cancel', 'local_courselist'); ?>" onclick="approval_course(false);"/>
<?php if ($isnonformal == 1) { ?><input type="button" class="red-form" style="float:left; margin-right: 10px;" value="<?php echo get_string('course:approval_delete', 'local_courselist'); ?>" onclick="course_unenrol();"/><?php } ?>
    </div>
<?php if ($isnonformal == 1) { ?>
<!--        <div class="btn-area btn-area-right"> 
            <input type="button" class="red-form" style="float:right; margin-right: 10px;" value="<?php echo get_string('user:complete_cancel', 'local_courselist'); ?>" onclick="course_complete(false);"/>
            <input type="button" class="red-form" style="float:right; margin-right: 10px;" value="<?php echo get_string('user:complete_success', 'local_courselist'); ?>" onclick="course_complete(true);"/>
        </div>-->
        <?php
    }
    courselist_print_paging_navbar_script($count_users, $page, $perpage, 'javascript:goto_page(:page);', 10);
    ?>
</div>

<?php
echo $OUTPUT->footer();
?>

<script type="text/javascript">
    function checkbox_value_return() {
        var check_list = [];

        $(".userid").each(function (index, element) {
            if ($(this).is(":checked")) {
                check_list.push($(this).val());
            }
        });

        return check_list;
    }

    function check_user_id(check, checkClass) {
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

    function approval_course(approval) {
        var check_list = checkbox_value_return();
        var id = $('input:hidden[name=id]').val()

        if (check_list.length == 0) {
            alert("<?php echo get_string('user:sel_empty', 'local_courselist'); ?>");
            return false;
        }
            if (confirm(approval?'<?php echo get_string('enrol_yes', 'local_courselist'); ?>':'<?php echo get_string('enrol_no', 'local_courselist'); ?>')) {        
                $.ajax({
                    url: '<?php echo $CFG->wwwroot . "/local/courselist/course_approval.ajax.php" ?>',
                    method: 'POST',
                    data: {
                        id: id,
                        approval: approval,
                        user_list: check_list,
                    },
                    success: function (data) {
                        if (data == 0) {
                            document.location.href = "<?php echo $CFG->wwwroot . "/local/courselist/course_students_list.php?id=" . $id ."&status=".$status?>";
                        }
                    }
                });
            }
    }

    function course_unenrol() {
        var check_list = checkbox_value_return();
        var id = $('input:hidden[name=id]').val()

        if (check_list.length == 0) {
            alert("<?php echo get_string('user:sel_empty', 'local_courselist'); ?>");
            return false;
        }

        $.ajax({
            url: '<?php echo $CFG->wwwroot . "/local/courselist/course_unenrol.ajax.php" ?>',
            method: 'POST',
            data: {
                id: id,
                user_list: check_list
            },
            success: function (data) {
                alert(data + "<?php echo get_string('user:unenrol', 'local_courselist'); ?>");
                document.location.href = "<?php echo $CFG->wwwroot . "/local/courselist/course_students_list.php?id=" . $id ?>";
            }
        });
    }

    function course_complete(complete) {
        var check_list = checkbox_value_return();
        var id = $('input:hidden[name=id]').val()

        if (check_list.length == 0) {
            alert("<?php echo get_string('user:sel_empty', 'local_courselist'); ?>");
            return false;
        }

        $.ajax({
            url: '<?php echo $CFG->wwwroot . "/local/courselist/course_complete.ajax.php" ?>',
            method: 'POST',
            data: {
                id: id,
                complete: complete,
                user_list: check_list
            },
            success: function (data) {
                if (complete) {
                    alert("<?php echo get_string('user:complete_success', 'local_courselist'); ?> : " + data);
                } else {
                    alert("<?php echo get_string('user:complete_cancel', 'local_courselist'); ?> : " + data);
                }
                document.location.href = "<?php echo $CFG->wwwroot . "/local/courselist/course_students_list.php?id=" . $id  ?>";
            }
        });
    }



    function goto_page(page) {
        $('[name=page]').val(page);
        $('#frm_participants').submit();
    }

    function course_students_list_export(frm) {
<?php
$query_string = '';
if (!empty($page_param)) {
    $query_array = array();
    foreach ($page_param as $key => $value) {
        $query_array[] = urlencode($key) . '=' . urlencode($value);
    }
    $query_string = '?' . implode('&', $query_array);
}
?>
        var url = "course_students_list.excel.php<?php echo $query_string; ?>";

        document.location.href = url;
    }

</script>    
