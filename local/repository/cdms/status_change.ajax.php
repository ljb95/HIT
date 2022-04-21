<?php
require(dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php');


$id = required_param('id', PARAM_INT);
$status = required_param('status', PARAM_INT);
$msg = optional_param('msg', '', PARAM_RAW); 
$DB->update_record('lcms_repository',array('id'=>$id,'status'=>$status,'delaymsg'=>$msg));

