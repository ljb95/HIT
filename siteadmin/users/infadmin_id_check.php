<?php
require(dirname(dirname(dirname(__FILE__))) . '/config.php');
$userid = optional_param('userid', '', PARAM_RAW);

$check = $DB->get_record('user', array('username'=>$userid));

if(!empty($check)){
   echo false;
} else {
   echo true; 
}
