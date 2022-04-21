<?php
require(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once $CFG->dirroot . '/local/sendpush/sendpush_form.php';
require_once($CFG->dirroot . '/local/sendpush/lib.php');
require_once $CFG->dirroot . '/message/lib.php';

$id = optional_param('id', 0, PARAM_INT);  // Course ID
$search = optional_param('search', '', PARAM_CLEAN);  // Course ID

$context = get_context_instance(CONTEXT_COURSE, $id);

require_login();

$PAGE->set_context($context);
$PAGE->set_url('/local/sendpush/index.php?id=' . $id);
$PAGE->set_pagelayout('incourse');

$course = get_course($id);
$PAGE->set_course($course);

$dept_sql = "select distinct ohakkwa from {lmsdata_class} order by ohakkwa asc ";
$dept_lists = $DB->get_records_sql($dept_sql,array());


if (!has_capability('moodle/course:manageactivities', $context)) {
    return;
}

$strplural = get_string("pluginname", "local_sendpush");
$PAGE->navbar->add($strplural);
$PAGE->set_title($strplural);
$PAGE->set_heading($strplural);

$mform = new sendpush_form(null);

if ($mform->is_cancelled()) {
    redirect($CFG->wwwroot . "/course/view.php?id=$id");
} else if ($fromform = $mform->get_data()) {
    $users = optional_param_array('users', '', PARAM_CLEAN);
  //if (preg_match("/^(010|011|016|017|018|019|070|02|031)-\d{3,4}-\d{4}$/u", $fromform->sphone) || preg_match('/^[0-9]{10,11}$/', $fromform->sphone)) {
    $userfrom = $DB->get_record('user', array('id' => $USER->id));
        $push = new stdClass();

        $push->subject       = $fromform->subject;
        $push->contents      = $fromform->contents;
        $push->sendtime      = time();
        $push->sender        = fullname($USER);
        $push->username      = $userfrom->username;
        $push->callback      = str_replace("-","",$userfrom->phone1);
        $push->timecreated   = time();
        $push->schedule_type = 0;
        
        $newpush = $DB->insert_record('lmsdata_sms', $push);
        
        foreach ($users as $user => $uid) {
            $userto = $DB->get_record('user', array('id' => $uid));
            $userto->phone2 = str_replace("-","",trim($userto->phone2));
            if (preg_match('/^[0-9]{10,11}$/', $userto->phone2)) { 

                $push_user->push           = $newpush;
                $push_user->userid        = $userto->id;
                $push_user->phone         = $userto->phone2;
                $push_user->fullname      = fullname($userto);
                $push_user->timecreated   = time();
                
                send_push_local($userto, $push,$msg_no);
                $DB->insert_record('lmsdata_sms_data', $push_user);
            } 
        }
        
        redirect($CFG->wwwroot . "/course/view.php?id=$id");
//    } else {
//        redirect($CFG->wwwroot . "/course/view.php?id=$id",'발신자 번호를 정확히 입력해주세요.',100);
//    }
} else {
    
    echo $OUTPUT->header();
    ?>
    <div class="userlist">
        <form id="usersearch_form" class="table-search-option stat_form">
            <div class="stat_search_area">
                <b>주야구분</b> <select name = 'juya'>
                    <option value = ''>전체</option>
                    <option value = '10'>주간</option>
                    <option value = '20'>야간</option>
                </select>&nbsp;
                <b>학과</b> <select name = 'dept'>
                    <option value = ''>전체</option>
                    <?php
                        foreach($dept_lists as $dept_list){
                            if($dept_list->ohakkwa == ''){ continue; }
                            echo '<option value="'.$dept_list->ohakkwa.'" >'.$dept_list->ohakkwa.'</option>';
                        }
                    ?>
                </select>&nbsp;
                <b>학년</b> <select name = 'hyear'>
                    <option value = ''>전체</option>
                    <option value = '1'>1학년</option>
                    <option value = '2'>2학년</option>
                    <option value = '3'>3학년</option>
                    <option value = '4'>4학년</option>
                </select>&nbsp;
                <input type="text" title="search" name="search" value="<?php echo $search; ?>" class="search-text" placeholder="<?php echo get_string('searchplaceholder', 'coursereport_statistics'); ?>">
                <input type="button" onclick="get_users($('input[name=search]').val())" value="<?php echo get_string('search'); ?>" class="board-search"/>
            </div>
        </form>
        <div class ="nextline"><h3>검색 결과</h3></div>
        <div id="userlist_tbody">
            <div  class="searching">Searching...</div>
        </div>
        <div class="table-bottom-btnarea buttons"> 
            <input type="button" id="allcheck" value="<?php echo get_string('all_check', 'local_sendpush') ?>">
            <input type="button" id="alluncheck" value="<?php echo get_string('all_uncheck', 'local_sendpush') ?>">
            <input type="submit" id="adduser" value="<?php echo get_string('apply', 'local_sendpush') ?>">
        </div>
    </div>
    <?php echo $mform->display(); ?>
    <script type="text/javascript">
        function fnChkByte(obj, maxByte){
            var str = obj.value;
            var str_len = str.length;

            var rbyte = 0;
            var rlen = 0;
            var one_char = "";
            var str2 = "";

            for(var i=0; i<str_len; i++){
            one_char = str.charAt(i);
            if(escape(one_char).length > 4){
                rbyte += 2;                                         //한글2Byte
            }else{
                rbyte++;                                            //영문 등 나머지 1Byte
            }

            if(rbyte <= maxByte){
                rlen = i+1;                                          //return할 문자열 갯수
            }
            }

            if(rbyte > maxByte){
                alert("한글 "+(maxByte/2)+"자 / 영문 "+maxByte+"자를 초과 입력할 수 없습니다.");
                str2 = str.substr(0,rlen);                                  //문자열 자르기
                obj.value = str2;
                fnChkByte(obj, maxByte);
            }else{
                document.getElementById('byteInfo').innerText = rbyte;
            }
            }

        var selected_users = new Array();
        function get_users(search) {
            $.ajax({
                url: '<?php echo $CFG->wwwroot . "/local/sendpush/ajax/userlist.php" ?>',
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
            if (confirm('Are you want send PUSH?')) {
                return true;
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
            get_users($('input[name=search]').val());
            if($('.selected_user').length == 0){
                $('#user_selected').show();
                $('input[name=user]').val('');
            }
        }
    </script>
    <?php
    echo $OUTPUT->footer();
}
?>