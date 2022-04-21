<?php
require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/my/lib.php');
require_once($CFG->dirroot . '/siteadmin/lib.php');


$id = required_param('id',  PARAM_INT); 


require_login();

$PAGE->requires->jquery();
$PAGE->requires->jquery_plugin('ui');
$PAGE->requires->jquery_plugin('ui-css');


$params = array();

$course = $DB->get_record('course',array('id'=>$id));
$context = context_course::instance($course->id);
$PAGE->set_context($context);
$PAGE->set_course($course);
//$PAGE->set_url('/my/allcourse.php', $params);
$strplural = get_string("major_auditor", "local_lmsdata");
$PAGE->navbar->add($course->fullname,$CFG->wwwroot.'/course/view.php?id='.$id);
$PAGE->navbar->add($strplural);
$PAGE->navbar->add(get_string('application', 'local_lmsdata'));
$PAGE->set_title($strplural);
$PAGE->set_pagelayout('incourse');
if(!has_capability('moodle/course:update', $context)){ 
    redirect($CFG->wwwroot,'Wrong Connection');
}

echo $OUTPUT->header();
?>

<div class="table_group">
    <b style="font-size:30px; float:left;"><?php echo get_string('application', 'local_lmsdata') ?></b><br><br>
</div>

<div class="table_group" style="clear:both;">
    <b><?php echo get_string('assistant_application', 'local_lmsdata') ?></b> <br>
</div>
<div class="table_group">
    <table class="generaltable regular-courses">
        <thead>
            <tr>
                <th scope="row" width="5%">번호</th>
                <th scope="row" width="5%">이름 (학번)</th>
                <th scope="row" width="5%">이메일 주소</th>
                <th scope="row" width="5%">휴대 전화</th>
                <th scope="row" width="5%">신청일 / 처리일</th>
                <th scope="row" width="5%">상태</th>
                <th scope="row" width="5%">관리</th>
            </tr>   
        </thead>
        <tbody>
            <?php
            $applies = $DB->get_records('approval_reason', array('application_type' => 'assistant', 'courseid' => $id));
            foreach ($applies as $apply) {
                $user = $DB->get_record('user', array('id' => $apply->userid));
                ?>
                <tr>
                    <td>1</td>
                    <td><?php echo fullname($user) . '(' . $user->username . ')' ?></td>
                    <td><?php echo $user->email; ?></td>
                    <td><?php echo $user->phone2 ?></td>
                    <td><?php echo date('Y-m-d', $apply->apply_date); ?> / <?php if ($apply->processing_date) {
                echo date('Y-m-d', $apply->processing_date);
            } else {
                echo '-';
            } ?></td>
                    <td>
                        <?php
                        switch ($apply->approval_status) {
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
                        <?php if ($apply->approval_status == 0) { ?>
                            <input type="button" onclick="approval('<?php echo $apply->id; ?>')" class="btn_st01" value="승인">
                            <input type="button" onclick="unapproval('<?php echo $apply->id; ?>')" class="btn_st01" value="미승인">
                        <?php } else if ($apply->approval_status == 1) { ?>
                            <input type="button" onclick="cancelval('<?php echo $apply->id; ?>')" class="btn_st01" value="승인취소">
                <?php } else { ?>
                            -
                <?php } ?>
                    </td>
                </tr>
                <?php
            }
            if (!$applies) {
                echo '<tr><td colspan="8">신청된 내역이 없습니다.</td></tr>';
            }
            ?>
        </tbody>
    </table>
</div>  


<div class="table_group">
    <b><?php echo get_string('auditor_application', 'local_lmsdata') ?></b> <br>
</div>
<div class="table_group">
    <table class="generaltable regular-courses">
        <thead>
            <tr>
                <th scope="row" width="5%">번호</th>
                <th scope="row" width="5%">이름 (학번)</th>
                <th scope="row" width="5%">이메일 주소</th>
                <th scope="row" width="5%">휴대 전화</th>
                <th scope="row" width="5%">신청일 / 처리일</th>
                <th scope="row" width="5%">상태</th>
                <th scope="row" width="5%">관리</th>
            </tr>   
        </thead>
        <tbody>
            <?php
            $applies = $DB->get_records('approval_reason', array('application_type' => 'auditor', 'courseid' => $id));
            foreach ($applies as $apply) {
                $user = $DB->get_record('user', array('id' => $apply->userid));
                ?>
                <tr>
                    <td>1</td>
                    <td><?php echo fullname($user) . '(' . $user->username . ')' ?></td>
                    <td><?php echo $user->email; ?></td>
                    <td><?php echo $user->phone2 ?></td>
                    <td><?php echo date('Y-m-d', $apply->apply_date); ?> / <?php if ($apply->processing_date) {
                echo date('Y-m-d', $apply->processing_date);
            } else {
                echo '-';
            } ?></td>
                    <td>
                        <?php
                        switch ($apply->approval_status) {
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
                        <?php if ($apply->approval_status == 0) { ?>
                            <input type="button" onclick="approval('<?php echo $apply->id; ?>')" class="btn_st01" value="승인">
                            <input type="button" onclick="unapproval('<?php echo $apply->id; ?>')" class="btn_st01" value="미승인">
                        <?php } else if ($apply->approval_status == 1) { ?>
                            <input type="button" onclick="cancelval('<?php echo $apply->id; ?>')" class="btn_st01" value="승인취소">
                <?php } else { ?>
                            -
                <?php } ?>
                    </td>
                </tr>
                <?php
            }
            if (!$applies) {
                echo '<tr><td colspan="8">신청된 내역이 없습니다.</td></tr>';
            }
            ?>
        </tbody>
    </table>
</div>

<script type="text/javascript">
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
    function cancelval(applyid, status) {
    if(!status){
        status = 0;
    }
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

</script>
<?php
echo $OUTPUT->footer();
?>