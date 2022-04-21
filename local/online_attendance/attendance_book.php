<?php
$page = optional_param('page', 1, PARAM_INT);
$perpage = optional_param('perpage', 10, PARAM_INT);
$search = optional_param('search', '', PARAM_RAW);
$submit = optional_param('sub', 0, PARAM_RAW);

if ($submit) {
    $status = optional_param_array('status', array(), PARAM_RAW);
    $data = array();
    foreach ($status as $key => $sta) {
        list($userid, $section) = explode('_', $key);
        $statusdata = new Stdclass();
        $statusdata->userid = $userid;
        $statusdata->section = $section;
        $statusdata->status = $sta;
        $data[] = $statusdata;
    }
    $weeks_status = local_onattendance_week_status($id, $userlist);
    $weektstatus = array();
    foreach ($weeks_status as $wst) {
        $weektstatus[$wst->userid][$wst->sec] = $wst->status;
    }
    foreach ($data as $da) {
        $wstatus = $weektstatus[$da->userid][$da->section];
        if (is_null($wstatus)) {
            $weekstatus = new Stdclass();
            $weekstatus->courseid = $id;
            $weekstatus->section = $da->section;
            $weekstatus->userid = $da->userid;
            $weekstatus->status = $da->status;
            $weekstatus->timecreated = time();
            $weekstatus->timemodified = time();
            $weekstatus->fixstatus = 0;
            $DB->insert_record('local_onattend_week_status', $weekstatus);
        } else if ($wstatus != $da->status) {
            $oldstatus = $DB->get_record('local_onattend_week_status', array('courseid' => $id, 'section' => $da->section, 'userid' => $da->userid));
            $oldstatus->status = $da->status;
            $oldstatus->fixstatus = 1;
            $DB->update_record('local_onattend_week_status', $oldstatus);
        }
    }
}
//등록된 학생 목록
$fullname = $DB->sql_fullname('ur.firstname', 'ur.lastname');
$sql_select = "SELECT  ur.id,
                       ur.username, " . $DB->sql_fullname('ur.firstname', 'ur.lastname') . " AS fullname ";
$sql_from = " FROM {context} co 
              JOIN {role_assignments} ra ON ra.contextid = co.id and roleid = :roleid
              JOIN {user} ur ON ur.id = ra.userid ";

$sql_conditions[] = ' co.id = :contextid ';
$sql_conditions[] = ' ur.deleted = :deleted ';

$params['contextid'] = $context->id;
$params['roleid'] = $DB->get_field('role', 'id', array('archetype' => 'student'));
$params['deleted'] = 0;

//검색어
if (!empty($search)) {
    $like_fullname = $DB->sql_like($fullname, ':fullname');
    $like_name = $DB->sql_like('ur.username', ':username');
    $sql_conditions[] = '(' . $like_fullname . ' or ' . $like_name . ')';
    $params['fullname'] = '%' . $search . '%';
    $params['username'] = '%' . $search . '%';
}

$sql_where = ' WHERE ' . implode(' AND ', $sql_conditions);
$sql_order_by = ' ORDER BY loc.section, loc.cmid ASC ';

$totalcount = $DB->count_records_sql('SELECT COUNT(*) ' . $sql_from . $sql_where, $params);
$users = $DB->get_records_sql($sql_select . $sql_from . $sql_where . $sql_orderby, $params, ($page - 1) * $perpage, $perpage);

// user의 주차별 출석 데이터
if (!empty($users)) {
    $userlist = array();
    foreach ($users as $user) {
        $userlist[] = $user->id;
    }
}

$weeks_status = local_onattendance_week_status($id, $userlist);
$weektstatus = array();
foreach ($weeks_status as $wst) {
    $weektstatus[$wst->userid][$wst->sec] = $wst->status;
}
unset($weeks_status);
$sections = $DB->get_records_sql_menu('SELECT section FROM {local_onattend_cm_set} WHERE approval = 1 GROUP BY section order BY section', array('approval' => 1));
$sections = array_keys($sections);

//강의 출석 설정 정보
$setupdata = new online_attendance($id);
?>

<form id="form1" class="table-search-option" name = "form_setup">
    <input type="hidden" name="id" value="<?php echo $id; ?>">
    <input type="hidden" name="type" value="<?php echo $type; ?>">
    <input type="hidden" name="page" value="<?php echo $page; ?>">
    <input type="hidden" name="perpage" value="<?php echo $perpage; ?>">
    <input type="text" name="search" placeholder="<?php echo get_string('searchplaceholder', 'coursereport_statistics'); ?>" title="이름입력" class="search_name" value="<?php echo $search; ?>" />
    <input type="submit" value="<?php echo get_string('search', 'local_jinoboard'); ?>" class="board-search"/>
</form>

<div class="manage-status-submit">
    <div class="buttons">
        <div class="left">
            <select class="perpage" name="perpage"title="페이지" onchange="change_perpage(this.options[this.selectedIndex].value, 'form_setup');">
<?php
$nums = array(5, 10, 15, 20);
foreach ($nums as $num) {
    $selected = '';
    if ($num == $perpage) {
        $selected = ' selected';
    }
    echo '<option value="' . $num . '"' . $selected . '>' . get_string('showperpage', 'local_courselist', $num) . '</option>';
}
?>
            </select>
        </div>
        <div class="right">
            <input type="button" value="출석정보저장" class="btn" onclick="attendance_submit();"/>
            <input type="button" value="<?php print_string('book:grades', 'local_offline_attendance'); ?>" class="btn-primary"  onclick="attendance_book_grading();"/>
            <input type="button" value="Excel 다운로드" class="btn" onclick="excel_export();"/>
        </div>
    </div>
</div>

<form id="grade_submit" name="grade_submit" method="post">
    <input type="hidden" name="sub" value="0">
    <input type="hidden" name="id" value="<?php echo $id; ?>">
    <input type="hidden" name="type" value="<?php echo $type; ?>">
    <input type="hidden" name="page" value="<?php echo $page; ?>">
    <input type="hidden" name="perpage" value="<?php echo $perpage; ?>">
    <input type="hidden" name="search"  value="<?php echo $search; ?>"/>
    <table class="generaltable">
        <colgroup>
            <col width="50px" />
            <col width="10%" />
            <col width="10%" />
            <col width="/" />
        </colgroup>
        <thead>
            <tr>
                <th>번호</th>
                <th>이름</th>
                <th>학번</th>
<?php
foreach ($sections as $section) {
    echo '<th>' . $section . '주차</th>';
}
?>
                <th>출석</th>
                <th>지각</th>
                <th>결석</th>
                <th>점수</th>

            </tr>
        </thead>

        <tbody>
<?php
if ($totalcount > 0) {
    $startnum = $totalcount - (($page - 1) * $perpage);
    foreach ($users as $user) {
        $status_count = array('0' => 0, '1' => 0, '2' => 0);
        echo '<tr>';
        echo '<td>' . $startnum-- . '</td>';
        echo '<td>' . $user->fullname . '</td>';
        echo '<td>' . $user->username . '</td>';
        foreach ($sections as $sect) {
            $weekdata = $weektstatus[$user->id][$sect];
            if (empty($weekdata)) {
                $weekdata = 0;
            }
            echo '<td>' . local_onattendance_week_selectbox($user->id, $sect, $weekdata) . '</td>';

            $status_count[$weekdata] ++;
        }

        $absent = 0;
        $absent += $status_count[0];
        $absent += floor($status_count[2] / $setupdata->late);
        $grade = floor($setupdata->maxscore) + ($absent * $setupdata->absent);
        $minscore = floor($setupdata->minscore);
        if ($grade < $minscore) {
            $grade = $minscore;
        }
        echo '<td>' . $status_count[1] . '</td>';
        echo '<td>' . $status_count[2] . '</td>';
        echo '<td>' . $status_count[0] . '</td>';
        echo '<td>' . $grade . '</td>';
        echo '<tr>';
    }
} else {
    echo '<tr><td colspan="7">데이터가 없습니다.</td></tr>';
}
?>
        </tbody>
    </table>
</form>
<div class="table-footer-area">
<?php
onattendance_print_paging_navbar_script($totalcount, $page, $perpage, 'javascript:goto_page(:page, \'form_setup\');', 10);
?>
</div>

<?php
echo $OUTPUT->footer();
?>
<script type="text/javascript">
    function excel_export() {
        $('form[name=form_setup]').attr('action', './attendance_book_excel.php');
        $('form[name=form_setup]').submit();
    }
    function attendance_submit() {
        $('input[name=sub]').val('1');
        $('form[name=grade_submit]').attr('action', './index.php');
        $('form[name=grade_submit]').submit();
    }
    function attendance_book_grading() {
        if (confirm('<?php print_string('book:alert1', 'local_offline_attendance'); ?>')) {
            $.ajax({
                url: '<?php echo $CFG->wwwroot . '/local/online_attendance/attendance_book_grade.php'; ?>',
                method: 'POST',
                dataType: 'json',
                data: {
                    id: <?php echo $id ?>
                },
                success: function (data) {
                    if (data.status == 'success') {
                        alert(data.text);
                    }
                }
            });
        }
    }
</script>