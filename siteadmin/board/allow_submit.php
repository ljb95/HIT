<?php
    require(dirname(dirname(dirname(__FILE__))) . '/config.php');
    require_once dirname(dirname(__FILE__)) . '/lib/contents_lib.php';
    
    $maketime = time();
    $id = optional_param("id", 0, PARAM_INT);
    
    
    $DB->update_record('jinoboard_allowd',$_POST['pr']);
    $DB->update_record('jinoboard_allowd',$_POST['ad']);
    $DB->update_record('jinoboard_allowd',$_POST['rs']);
    $DB->update_record('jinoboard_allowd',$_POST['gu']);
    $DB->update_record('jinoboard_allowd',$_POST['sa']);

    redirect('./allow.php?id='.$id);