<?php
    require(dirname(dirname(dirname(__FILE__))) . '/config.php');
    require_once dirname(dirname(__FILE__)) . '/lib/contents_lib.php';
     
    $id = required_param('id', PARAM_INT);

    $DB->delete_records('jinoboard',array('id'=>$id));
    $DB->delete_records('jinoboard_category',array('board'=>$id));
    $DB->delete_records('jinoboard_contents',array('board'=>$id));

    redirect('./list.php');
   


