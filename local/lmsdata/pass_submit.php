<?php
require_once("../../config.php");

$curpass = optional_param('curpass', 0, PARAM_TEXT );
$newpass = optional_param('newpass', 0, PARAM_TEXT );
$userid = required_param('id', PARAM_INT);

if( empty($curpass) || !isset($curpass) || empty($newpass) || !isset($newpass) ) {
	echo 0;
	exit;
}

if($USER->id == $userid || is_siteadmin($USER->id)){

    $user = $DB->get_record('user', array("id"=>$userid));
    $hashedpassword = $user->password;
    
    if (crypt($curpass, $hashedpassword) == $hashedpassword) {
            $hashednewpassword = hash_internal_user_password($newpass);
            $user_newpass = new stdClass();
            $user_newpass->id = $USER->id;
            $user_newpass->password = $hashednewpassword;
            $user_newpass->timemodified = time();
            $DB->update_record('user', $user_newpass);
            echo 1;
    }else{
            echo 0;
    }

} else {
    echo 0;
}
