<?php
require_once("../../config.php");
require_once 'send_form.php';
require_once 'lib.php';
require_once $CFG->dirroot . '/lib/formslib.php';
require_once $CFG->dirroot . '/lib/form/filemanager.php';
require_once($CFG->dirroot . '/siteadmin/support/smsconfig.php');

$id = required_param('id', PARAM_INT);

$course = $DB->get_record('course', array('id' => $id), '*', MUST_EXIST);
require_login($course);

$context = context_course::instance($course->id);


$url = new moodle_url($CFG->wwwroot . '/local/attendance/send.php?id='.$id);

$PAGE->set_context($context);
$PAGE->set_url('/local/attendance/send.php?id=' . $id);

$PAGE->set_course($course);
$PAGE->set_pagelayout('popup');

$mformdata = array('context'=>$context);

$mform = new local_attendance_form($url,$mformdata);


if ($mform->is_cancelled()) {
    redirect("index.php?id=$id"); 
} else if ($fromform = $mform->get_data()) {
    
    $send_type = optional_param_array('send_type', array(), PARAM_RAW);
    $sms_content = optional_param('sms_content', '', PARAM_RAW);
    $smssender = optional_param('smssender', '010-0000-0000', PARAM_RAW);
    $title = $fromform->name;
    $newdata = new stdClass();
    $newdata->contents = file_save_draft_area_files($fromform->contents['itemid'], $context->id, 'local_attendance', 'contents', 0,
            local_attendance_editor_options($context, null), $fromform->contents['text']);
    if($send_type['sms'] == true) {
     $smsdata = new stdClass();
            $smsdata->subject = $title;
            $smsdata->contents = $sms_content;
            $code = set_smssend_local_attendance($smsdata);
            if(!$code){
                echo 'Set Sms Error';
                die();
            }
    }
    $users = optional_param_array('users', array(), PARAM_RAW);
            foreach ($users as $user => $uid) {
              $user = $DB->get_record('user', array('id' => $uid));
                if ($send_type['mail'] == true) {
                    $mailresult = email_to_user($user, $USER, $title, html_to_text($newdata->contents), $newdata->contents);
                }
                if ($send_type['message'] == true) {
                    $eventdata = new stdClass();
                    $eventdata->name = 'message';
                    $eventdata->component = 'local_attendance';
                    $eventdata->userfrom = $USER;
                    $eventdata->userto = $user;
                    $eventdata->subject = $title;
                    $eventdata->fullmessage = html_to_text($newdata->contents);
                    $eventdata->fullmessageformat = FORMAT_PLAIN;
                    $eventdata->fullmessagehtml = $newdata->contents;
                    $eventdata->smallmessage = '';
                    $good = $good && message_send($eventdata);
                }
                if ($send_type['sms'] == true) {
                    $smsdata->callback = $smssender;
                   send_sms_local_attendance($user, $smsdata, $code);
                }
            }
    
   echo '<script>window.close();</script>';
    die();
}

echo $OUTPUT->header();

$mform->display();

?>
<script>
       function fnChkByte_att(obj, maxByte){
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
            function delete_user(userid) {
            $('.user' + userid).remove();
            delete selected_users[userid];
            get_users($('input[name=search]').val());
        }
    </script>
<?php
echo $OUTPUT->footer();
