<?php
require_once("../../config.php");

//http -> https 리다이렉트
//if(!isset($_SERVER['HTTPS'])) { header('Location: '.$SITECFG->wwwsroot . $_SERVER["REQUEST_URI"]); }
//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

$type = optional_param('type', "pass", PARAM_TEXT);
$id = optional_param('id', $USER->id, PARAM_INT);    // user id

require_login();

if(!is_siteadmin() && $USER->id != $id){
    print_error('cannotedityourprofile');
}
//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
if(!empty($id)){
    $userid = $id;
}else{
    $userid = $USER->id;
}
$usercase = get_usercase($userid);

$user = $DB->get_record('user', array('id'=>$userid));
$user_data = $DB->get_record('lmsdata_user', array('userid'=>$userid));

$PAGE->set_url('/local/lmsdata/user_password.php');
$PAGE->set_pagelayout('standard');

// Print the header
$strplural = get_string('password_change','local_lmsdata');

$PAGE->navbar->add(get_string('title:mypage', 'local_lmsdata'));
$PAGE->navbar->add($strplural);

$PAGE->set_heading($strplural);

echo $OUTPUT->header();

//tab
$row[] = new tabobject('info', "$CFG->wwwroot/local/lmsdata/user_info.php?id=".$userid, get_string('personal_information', 'local_lmsdata'));
if($USER->id == $userid || is_siteadmin($USER->id)){
    $row[] = new tabobject('change', "$CFG->wwwroot/local/lmsdata/user_info_edit.php?id=".$userid , get_string('personal_information_change', 'local_lmsdata'));
    if($usercase == 'temp') {
        $row[] = new tabobject('pass', "$CFG->wwwroot/local/lmsdata/user_password.php?id=".$userid , get_string('password_change', 'local_lmsdata'));
    }
    $row[] = new tabobject('message', "$CFG->wwwroot/local/lmsdata/user_message_edit.php?id=".$userid , get_string('personal_message_change', 'local_lmsdata'));
}
$rows[] = $row;
?>

<div id="tab_area">
    <?php
        print_tabs($rows, $type);
    ?>

    <div id="tab">
        <p>* <?php echo get_string('alert_content1', 'local_lmsdata'); ?><br/></p>

        <table cellpadding="0" cellspacing="0" class="detail">
            <tr>
                <td class="option"><?php echo get_string('user_id', 'local_lmsdata'); ?></td>
                <td class="value" style="padding-left:15px"><?php echo $user->username; ?></td>
            </tr>
            <tr>
                <td class="option">*<?php echo get_string('password_present', 'local_lmsdata'); ?></td>
                <td class="value"><input type="password" class="w_200" name="password" id="password" autocomplete="off" /></td>
            </tr>
            <tr>
                <td class="option">*<?php echo get_string('password_new', 'local_lmsdata'); ?></td>
                <td class="value"><input type="password" class="w_200" name="password1" id="password1" autocomplete="off" /><span class="form_desc"><?php echo get_string('alert_content3', 'local_lmsdata'); ?></span></td>
            </tr>
            <tr>
                <td class="option">*<?php echo get_string('password_renew', 'local_lmsdata'); ?></td>
                <td class="value"><input type="password" class="w_200" name="password2" id="password2" autocomplete="off" /></td>
            </tr>
        </table>

        <input type="button" class="gray_bg right" value="<?php echo get_string('cancel', 'local_lmsdata'); ?>" id="user_cancel" />
        <input type="button" class="red_bg right" value="<?php echo get_string('change_save', 'local_lmsdata'); ?>" id="user_submit" />
    </div> <!-- Tab End -->
</div> <!-- Tab Area end -->

<script type="text/javascript">
    $(document).ready(function () {
	$('#user_submit').click(function() {
		
		if(!($('#password').val())){
			alert("<?php echo get_string('pass_alert','local_lmsdata');?>");
			return false;
		}
		
		if(!(check_pass($('#password1').val()))){
			alert("<?php echo get_string('pass_alert2','local_lmsdata');?>");
			return false;
		}
		if($('#password1').val() != $('#password2').val()){
			alert("<?php echo get_string('pass_alert3','local_lmsdata');?>");
			return false;
		}
		
		$.ajax({
			url : '<?php echo $CFG->wwwroot . '/local/lmsdata/pass_submit.php'; ?>',
			type: "post",
			data : {
				curpass : $('#password').val(),
				newpass : $('#password1').val()
			},
			async: false,
			success: function(data){
				if(data==1){
					$('#password').val('');
					$('#password1').val('');
					$('#password2').val('');
					alert('<?php echo get_string('pass_alert4','local_lmsdata');?>');
				}else{
					alert('<?php echo get_string('pass_alert5','local_lmsdata');?>');
				}
			},
			error:function(e){
				//console.log(e.responseText);
				alert(e.responseText);
			}
		});
		
	});
	
	$('#user_cancel').click(function() {
		location.href='/local/lmsdata/user_info.php';
	});
	
});

function check_pass(str){
	var leng = str.length;
	var pattern = /(([a-zA-Z]+[0-9]+[~!@\#$%^&*\()\-=+_']+[a-zA-Z0-9~!@\#$%^&*\()\-=+_']*|[a-zA-Z]+[~!@\#$%^&*\()\-=+_']+[0-9]+[a-zA-Z0-9~!@\#$%^&*\()\-=+_']*|[0-9]+[a-zA-Z]+[~!@\#$%^&*\()\-=+_']+[a-zA-Z0-9~!@\#$%^&*\()\-=+_']*|[0-9]+[~!@\#$%^&*\()\-=+_']+[a-zA-Z]+[a-zA-Z0-9~!@\#$%^&*\()\-=+_']*|[~!@\#$%^&*\()\-=+_']+[0-9]+[a-zA-Z]+[a-zA-Z0-9~!@\#$%^&*\()\-=+_']*|[~!@\#$%^&*\()\-=+_']+[a-zA-Z]+[0-9]+[a-zA-Z0-9~!@\#$%^&*\()\-=+_']*)$)/;
	if((8 <= leng) && (leng < 15)){
		if (pattern.test(str)) {
			return true;
		}
	}
	return false;
}

</script>

<?php
echo $OUTPUT->footer();
