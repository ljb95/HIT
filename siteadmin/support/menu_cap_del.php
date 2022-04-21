<?php

require_once dirname(dirname(dirname(__FILE__))) . '/config.php';

$id = required_param('id', PARAM_INT);
$change_id = required_param('changeid', PARAM_INT); 
$users = $DB->get_records('lmsdata_user',array('menu_auth'=>$id));
foreach($users as $user){
    $user->menu_auth = $change_id;
    $DB->update_record('lmsdata_user',$user);
}

$DB->delete_records('menu_auth',array('id'=>$id));
$DB->delete_records('menu_auth_name',array('authid'=>$id));
