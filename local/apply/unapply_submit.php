<?php
require_once(dirname(__FILE__) . '/../../config.php');

$id =  required_param('id',PARAM_INT);
$unapprove_reason =  required_param('unapprove_reason',PARAM_TEXT);

$apply = $DB->get_record('approval_reason',array('id'=>$id));

$apply->approval_status = 2;
$apply->unapprove_reason = $unapprove_reason;

$DB->update_record('approval_reason',$apply);

redirect('allow.php?id='.$apply->courseid);