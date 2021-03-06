<?php
require(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once $CFG->dirroot . '/local/sendmessage/sendmessage_form.php';
require_once $CFG->dirroot . '/message/lib.php';
$id = optional_param('id', 0, PARAM_INT);  // Course ID
$search = optional_param('search', '', PARAM_CLEAN);  // Course ID

$context = get_context_instance(CONTEXT_COURSE, $id);

require_login();

$PAGE->set_context($context);
$PAGE->set_url('/local/sendmessage/index.php?id=' . $id);
$PAGE->set_pagelayout('incourse');

$PAGE->requires->jquery();
$PAGE->requires->jquery_plugin('ui');
$PAGE->requires->jquery_plugin('ui-css');

$course = get_course($id);
$PAGE->set_course($course);

$dept_sql = "select distinct ohakkwa from {lmsdata_class} order by ohakkwa asc ";
$dept_lists = $DB->get_records_sql($dept_sql,array());

if (!has_capability('moodle/course:manageactivities', $context)) {
    return;
}

$strplural = get_string("pluginname", "local_sendmessage");
$PAGE->navbar->add($strplural);
$PAGE->set_title($strplural);
$PAGE->set_heading($strplural);

$mform = new sendmessage_form(null);

if ($mform->is_cancelled()) {
    redirect($CFG->wwwroot . "/course/view.php?id=$id");
} else if ($fromform = $mform->get_data()) {
    $users = optional_param_array('users', array(), PARAM_RAW);
    $history = new stdClass();
    $history->subject = $fromform->subject;
    $history->text = $fromform->contents['text'];
    $history->targets = '';
    $history->userid = $USER->id;
    $history->timecreated = time();
    $historyid = $DB->insert_record('messagesend_history', $history);

    $itemid = $fromform->contents['itemid'];
    $history->text = file_save_draft_area_files($itemid, $context->id, 'local_sendmessage', 'text', $historyid, $mform->editor_options($context, $historyid), $fromform->contents['text']);

    $DB->set_field('messagesend_history', 'text', $history->text, array('id' => $historyid));



    $history->text = file_rewrite_pluginfile_urls($history->text, 'pluginfile.php', $context->id, 'local_sendmessage', 'text', $historyid);
    $targets = '';
    foreach ($users as $user => $uid) {
        $userto = $DB->get_record('user', array('id' => $uid));
        $targets .= $uid . ',';
        $mailresult = message_post_message($USER, $userto, $history->text, FORMAT_HTML);
    }
    $DB->set_field('messagesend_history', 'targets', rtrim($targets, ','), array('id' => $historyid));
    redirect($CFG->wwwroot . "/course/view.php?id=$id");
} else {

    echo $OUTPUT->header();
    ?>
    <div class="userlist">
        <form id="usersearch_form" class="table-search-option stat_form">
            <div class="stat_search_area">
                <b>????????????</b> <select name = 'juya'>
                    <option value = ''>??????</option>
                    <option value = '10'>??????</option>
                    <option value = '20'>??????</option>
                </select>&nbsp;
                <b>??????</b> <select name = 'dept'>
                    <option value = ''>??????</option>
                    <?php
                        foreach($dept_lists as $dept_list){
                            if($dept_list->ohakkwa == ''){ continue; }
                            echo '<option value="'.$dept_list->ohakkwa.'" >'.$dept_list->ohakkwa.'</option>';
                        }
                    ?>
                </select>&nbsp;
                <b>??????</b> <select name = 'hyear'>
                    <option value = ''>??????</option>
                    <option value = '1'>1??????</option>
                    <option value = '2'>2??????</option>
                    <option value = '3'>3??????</option>
                    <option value = '4'>4??????</option>
                </select>&nbsp;
                <input type="text" title="search" name="search" value="<?php echo $search; ?>" class="search-text" placeholder="<?php echo get_string('searchplaceholder', 'coursereport_statistics'); ?>">
                <input type="button" onclick="get_users($('input[name=search]').val())" value="<?php echo get_string('search'); ?>" class="board-search"/>
            </div>
        </form>
        <div class ="nextline"><h3>?????? ??????</h3></div>
        <div id="userlist_tbody">
            <div  class="searching">Searching...</div>
        </div>
        <div class="table-bottom-btnarea buttons"> 
            <input type="button" id="allcheck" value="<?php echo get_string('all_check', 'local_sendmessage') ?>">
            <input type="button" id="alluncheck" value="<?php echo get_string('all_uncheck', 'local_sendmessage') ?>">
            <input type="submit" id="adduser" value="<?php echo get_string('apply', 'local_sendmessage') ?>">
        </div>
    </div>
    <?php echo $mform->display(); ?>
    <script type="text/javascript">
        var selected_users = new Array();
        function get_users(search) {
//            alert($('select[name="bunban"]').val());
            $.ajax({
                url: '<?php echo $CFG->wwwroot . "/local/sendmessage/ajax/userlist.php" ?>',
                method: 'POST',
                data: {
                    id: <?php echo $id; ?>,
                    search: search,
                    selected_users: selected_users,
                    dept : $('select[name="dept"]').val(),
                    hyear : $('select[name="hyear"]').val(),
                    juya : $('select[name="juya"]').val()
                },
                success: function (data) {
                    $('#userlist_tbody').html(data);
                }
            });
        }
        $('#usersearch_form').submit(function () {
            get_users($('input[name=search]').val());
            return false;
        });
        $('.mform').submit(function () {
            if (confirm('???????????? ??????????????????????')) {
                return true;
            } else {
                return false;
            }
        });
        $('#cancel_btn').click(function () {
            if (confirm('????????? ????????? ???????????? ????????????. ?????????????????????????')) {
                location.href = '<?php echo $CFG->wwwroot . "/course/view.php?id=$id"; ?>';
            } else {
                return false;
            }
        });
        window.onload = function () {
            get_users('<?php echo $search; ?>');
        };
        $('#allcheck').click(function () {
            $('.usercheck').each(function () {
                $('.usercheck').prop('checked', true);
            });
        });
        $('#alluncheck').click(function () {
            $('.usercheck').prop('checked', false);
        });
        $('#adduser').click(function () {
            $('.usercheck:checked').each(function () {
                selected_users[$(this).val()] = $(this).val();
                $('#user_selected').hide();
                $('input[name=user]').val(1);
                $('#utd' + $(this).val()).remove();
                $('#selected_users').html($('#selected_users').html() + '<div class="selected_user user' + $(this).val() + '">' + $(this).attr('username') + '<input type="hidden" name="users[]" value="' + $(this).val() + '"><span class="deleteX" onclick="delete_user(' + $(this).val() + ')">X</span></div>');
            });
            get_users($('input[name=search]').val());
        });
        function delete_user(userid) {
            $('.user' + userid).remove();
            delete selected_users[userid];
                        if($('.selected_user').length == 0){
                $('#user_selected').show();
                $('input[name=user]').val('');
            }
            get_users($('input[name=search]').val());
        }
    </script>
    <?php
    echo $OUTPUT->footer();
}
?>