<?php
require_once(dirname(__FILE__) . '/../../config.php');

require_login();
$id = required_param('id', PARAM_INT);
$role = required_param('role', PARAM_RAW);
$PAGE->requires->jquery();
$PAGE->requires->jquery_plugin('ui');
$PAGE->requires->jquery_plugin('ui-css');

if (isguestuser()) {  // Force them to see system default, no editing allowed
    // If guests are not allowed my moodle, send them to front page.
    if (empty($CFG->allowguestmymoodle)) {
        redirect(new moodle_url('/', array('redirect' => 0)));
    }

    $userid = null;
    $USER->editing = $edit = 0;  // Just in case
    $context = context_system::instance();
    $PAGE->set_blocks_editing_capability('moodle/my:configsyspages');  // unlikely :)
    $header = "$SITE->shortname: $strmymoodle (GUEST)";
    $pagetitle = $header;
} else {        // We are trying to view or edit our own My Moodle page
    $userid = $USER->id;  // Owner of the page
    $context = context_user::instance($USER->id);
    $PAGE->set_blocks_editing_capability('moodle/my:manageblocks');
    $header = fullname($USER);
    $pagetitle = $strmymoodle;
}

$params = array();
$PAGE->set_context($context);

//$PAGE->set_url('/local/courselist/course_manage.php');
$PAGE->set_pagelayout('standard');



$strplural = get_string('major_auditor', 'local_lmsdata');
$PAGE->navbar->add(get_string('major_auditor', 'local_lmsdata'));
$PAGE->navbar->add(get_string('assistant_apply', 'local_lmsdata'));
$PAGE->set_title($strplural);
$PAGE->set_heading(get_string("assistant_apply", 'local_lmsdata'));


echo $OUTPUT->header();


$sql_select = "SELECT mc.id, mc.fullname, mc.shortname, 
                lc.timestart, lc.timeend, lc.timeregstart, lc.timeregend, lc.ohakkwa ,
                lc.subject_id, lc.year, lc.term, lc.isreged, lc.prof_userid,
                ur.firstname, ur.lastname";

$sql_from = " FROM {course} mc
                 JOIN {lmsdata_class} lc ON lc.course = mc.id
                 JOIN {course_categories} ca ON ca.id = mc.category 
                 LEFT JOIN {user} ur ON ur.id = lc.prof_userid 
                 where mc.id = :courseid";
$course = $DB->get_record_sql($sql_select . $sql_from, array('courseid' => $id));


$lang = current_language();
?>
<div class="table_group">
    <h3><?php echo get_string('assistant_apply', 'local_lmsdata'); ?></h3>
</div>

<div class="table_group explain"> 
    <?php echo get_string('assistant_policy', 'local_lmsdata'); ?>
</div>

<div class="table_group">
    <h3>강좌정보</h3>
</div>
<div class="table_group">
    <table class="generaltable regular-courses">
        <thead>
            <tr>
                <th ><?php echo get_string('stats_prof', 'local_lmsdata'); ?>
                <td>
                    <?php echo fullname($course); ?>
                </td>
                </th>    
                <th ><?php echo get_string('stats_coursename', 'local_lmsdata'); ?>
                <td><?php echo $course->fullname; ?></td>
                </th>   
            </tr> 
            <tr>
                <th><?php echo get_string('department', 'local_lmsdata'); ?>
                <td><?php echo $course->ohakkwa ? $course->ohakkwa : '-'; ?></td>
                </th>   
<!--                        <th><?php echo get_string('stats_terms', 'local_lmsdata'); ?>
                    <td><?php echo $course->term ? $course->term : '-'; ?></td>
                </th>    -->
                <th><?php echo get_string('stats_terms', 'local_lmsdata'); ?>
                <td>
                    <?php
                    if ($course->term == 1 || $course->term == 2) {
                        $term = $i . get_string('term', 'local_lmsdata');
                    } else if ($course->term == 3 || $course->term == 4) {
                        $term = str_replace(array(3, 4), array(get_string('summer', 'local_okregular')), $course->term);
                    } else {
                        $term = '-';
                    }
                    echo $term;
                    ?>
                </td>
                </th>    
            </tr>
        </thead>
    </table>
</div>  
<br> 
<div class="table_group">
    <table class="generaltable">
        <thead>
            <tr>
                <th scope="row" width="5%">이름</th>
                <th scope="row" width="5%">신청일/처리일</th>
                <th scope="row" width="5%">승인여부</th>
                <th scope="row" width="5%">비고</th>
            </tr>   
        </thead>
        <tbody>



            <?php
            $query = 'select ar.*,lc.timeregstart,lc.timeregend from {approval_reason} ar JOIN {lmsdata_class} lc on ar.id = lc.id WHERE ar.courseid = :courseid';

            $datas = $DB->get_records_sql($query, array('courseid' => $id));

            //$totalcount = $DB->count_records_sql('SELECT COUNT(*) '.$sql_from);


            foreach ($datas as $data) {
                ?>

                <tr>
                    <td  scope="col"><?php echo fullname($USER); ?></td>
                    <td  scope="col">
                        <?php
                        $timestart = date('Y-m-d', $data->timestart);
                        $timeend = date('Y-m-d', $data->timeend);
                        echo $timestart . ' / ' . $timeend;
                        ?>
                    </td>
                    <td scope="col">
                        <?php
                        switch ($data->approval_status) {
                            case 0:
                                echo '승인대기';
                                break;
                            case 1:
                                echo '승인';
                                break;
                            case 2:
                                echo '미승인';
                                break;
                            case 3:
                                echo '승인취소';
                                break;
                        }
                        ?>
                    </td>
                    <td> 
                        <?php if ($data->approval_status == 0) { ?>
                            <input type="button" onclick="approval('<?php echo $data->id; ?>')" class="btn_st01" value="승인">
                            <input type="button" onclick="unapproval('<?php echo $data->id; ?>')" class="btn_st01" value="미승인">
                        <?php } else if ($data->approval_status == 1) { ?>
                            <input type="button" onclick="cancelval('<?php echo $data->id; ?>')" class="btn_st01" value="승인취소">
                        <?php } else { ?>
                            -
                        <?php } ?>
                    </td>
                <?php } ?>
            </tr>
            <?php
            if (!$datas) {
                echo '<tr><td colspan="8">' . "신청된 내역이 존재하지 않습니다" . '</td></tr>';
            }
            ?>
        </tbody>
        <tfoot></tfoot>
    </table> 
</div>
<?php
$lmsdata_user = $DB->get_record('lmsdata_user', array('userid' => $USER->id));

$apply = $DB->get_record('approval_reason', array('courseid' => $id, 'userid' => $USER->id));
if (!$apply) {
    ?>

    <form method="post" action="apply_post.php" name="apply">
        <input type="hidden" name="courseid" value="<?php echo $id; ?>">
        <input type="hidden" name="role" value="<?php echo $role; ?>">
        <div class="table_group">
            <table class="generaltable">
                <tbody>
                    <tr>
                        <th>
                            <?php echo get_string('name', 'local_lmsdata'); ?>
                        </th>   
                        <td class="text-left">
                            <?php echo fullname($USER); ?>
                        </td>
                    </tr>
                    <tr>
                        <th>
                            <?php echo get_string('student_number', 'local_lmsdata'); ?>
                        </th>
                        <td class="text-left"><?php echo $USER->username; ?></td>
                    </tr>
                    <tr>
                        <th>
                            <?php echo get_string('email', 'local_lmsdata'); ?>
                        </th>
                        <td class="text-left"><?php echo $USER->email ?></td>
                    </tr>
                    <tr>
                        <th>
                            <?php echo get_string('phone', 'local_lmsdata'); ?><span class="red">*</span>                   
                        </th>
                        <td class="text-left">
                            <input type="text" title="search" class="w-250" value="<?php echo $USER->phone2; ?>" id="phone" name="phone">
                        </td>
                    </tr>
                    <tr>
                        <th>
                            <?php echo get_string('apply_reason', 'local_lmsdata'); ?><span class="red">*</span>                   
                        </th>
                        <td class="text-left">
                            <textarea id="apply_reason" name="apply_reason" title="신청사유" class="w100p" rows="5" ></textarea>
                        </td>
                    </tr>
                </tbody>
            </table>
            <div class="button-group text-center">
                <input type="submit" class="btn_st01"  value="신청하기" onclick="return check_onclick();" />
                <input type="button" class="btn_st01" value="<?php echo get_string('cancel', 'local_lmsdata'); ?>" onclick="location.href = 'assistant.php'" />
            </div>

        </div>
    </form>    
    <?php
}
echo $OUTPUT->footer();
?>
<script type="text/javascript">
    function course_delect(enrolid) {
        if (confirm("<?php echo get_string('cancelalert', 'local_oklearning') ?>")) {
            $.ajax({
                url: '<?php echo $SITECFG->wwwroot . '/local/oklearning/course_delect.ajax.php'; ?>',
                type: 'POST',
                dataType: 'json',
                async: false,
                data: {
                    enrolid: enrolid
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

    function approval(applyid) {
        var tag2 = $("<div id='appval'></div>");
        var btn = [
            {id: 'save',
                text: '승인',
                disable: true,
                click: function () {
                    $('#apply_reason').submit();
                }},
            {id: 'close',
                text: '닫기',
                disable: true,
                click: function () {
                    $(this).dialog("close");
                }}

        ];
        $.ajax({
            url: '<?php echo $CFG->wwwroot . '/local/apply/apply_form.php'; ?>',
            method: 'POST',
            data: {
                id: applyid
            },
            success: function (data) {
                tag2.html(data).dialog({
                    title: '승인',
                    modal: true,
                    width: 400,
                    resizable: false,
                    height: 257,
                    buttons: btn,
                    close: function () {
                        $(this).dialog('destroy').remove()
                    }
                }).dialog('open');
            }
        });
    }

    function unapproval(applyid) {
        var tag2 = $("<div id='appval'></div>");

        var btn = [
            {id: 'save',
                text: '미승인',
                disable: true,
                click: function () {
                    $('#apply_reason').submit();
                }},
            {id: 'close',
                text: '닫기',
                disable: true,
                click: function () {
                    $(this).dialog("close");
                }}

        ];

        $.ajax({
            url: '<?php echo $CFG->wwwroot . '/local/apply/unapply_form.php'; ?>',
            method: 'POST',
            data: {
                id: applyid
            },
            success: function (data) {
                tag2.html(data).dialog({
                    title: '미승인',
                    modal: true,
                    width: 400,
                    resizable: false,
                    height: 257,
                    buttons: btn,
                    close: function () {
                        $(this).dialog('destroy').remove()
                    }
                }).dialog('open');
            }
        });
    }
    function cancelval(applyid, status = 0) {
        var tag2 = $("<div id='appval'></div>");

        var btn = [
            {id: 'save',
                text: '승인취소',
                disable: true,
                click: function () {
                    $('#apply_reason').submit();
                }},
            {id: 'close',
                text: '닫기',
                disable: true,
                click: function () {
                    $(this).dialog("close");
                }}

        ];

        $.ajax({
            url: '<?php echo $CFG->wwwroot . '/local/apply/cancel_form.php'; ?>',
            method: 'POST',
            data: {
                id: applyid
            },
            success: function (data) {
                tag2.html(data).dialog({
                    title: '승인취소',
                    modal: true,
                    width: 400,
                    resizable: false,
                    height: 257,
                    buttons: btn,
                    close: function () {
                        $(this).dialog('destroy').remove()
                    }
                }).dialog('open');
            }
        });
    }
    function check_onclick() {
        theForm = document.apply;
        if (theForm.phone.value == "") {
            alert("휴대폰 번호란을 입력해 주세요");

            return false;
        } else if (theForm.apply_reason.value == "") {
            alert("신청사유란 을 입력해 주세요");

            return false;
        }
    }

</script>

