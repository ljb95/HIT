<?php

require_once dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php';
require_once dirname(dirname(dirname(__FILE__))) . '/lib/paging.php';
require_once dirname(dirname(dirname(__FILE__))) . '/lib.php';

$id = required_param('id', PARAM_INT);
$status = required_param('status', PARAM_INT);

$DB->update_record('jinoboard',array('id'=>$id,'status'=>$status));

;
echo ($status == 1)?'<span class="pointer" onclick="change_status(' . $id . ',2);">'. get_string('siteadmin_act', 'local_lmsdata') .'</span>':'<span class="pointer" onclick="change_status(' . $id . ',1);">'. get_string('siteadmin_noact', 'local_lmsdata') .'</span>';


