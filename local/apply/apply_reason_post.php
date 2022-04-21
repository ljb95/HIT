<?php

require_once(dirname(__FILE__) . '/../../config.php');

$id = required_param('id', PARAM_INT);
$applyid = required_param('applyid', PARAM_INT);
$apply_reason = required_param('apply_reason', PARAM_TEXT);

$data = new stdClass();
$data->id = $applyid;
$data->apply_reason = $apply_reason;
$DB->update_record('approval_reason',$data);

redirect('apply.php');