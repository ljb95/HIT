<?php
require(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once 'config.php';
require_once 'lib.php';

$con_id = required_param('contentid',PARAM_INT);
$type = required_param('ftype',PARAM_INT);

if($type == 1){
    $DB->delete_records('lcms_contents',array('id'=>$con_id));
    $DB->delete_records('lcms_repository',array('lcmsid'=>$con_id));
    $DB->delete_records('lcms_contents_file',array('con_seq'=>$con_id));
    echo '1';
} else {
    $DB->delete_records('lcms_contents_file',array('id'=>$con_id));
    echo '2';
}
