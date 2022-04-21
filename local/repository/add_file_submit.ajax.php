<?php

require(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once 'config.php';
require_once 'lib.php';

$con_id = required_param('con_id', PARAM_INT);

$context = context_system::instance();
require_login();
$PAGE->set_context($context);

$contents = $DB->get_record('lcms_contents', array('id' => $con_id));

$extarr = $LCFG->allowextword;
$n = 0;
$filecount_cnt = lcms_temp_dir_allow_filecount($extarr, $n, $contents->con_type);

if ($filecount_cnt == 1) {
    lcms_temp_dir_fileupload($extarr, $contents->con_type, $con_id, $contents->data_dir);
} else {
    echo $filecount_cnt;
    exit;
}
