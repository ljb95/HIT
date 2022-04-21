<?php
    require(dirname(dirname(dirname(__FILE__))) . '/config.php');
    require_once dirname(dirname(__FILE__)) . '/lib/contents_lib.php';
    
    $maketime = time();
    $id = optional_param("id", 0, PARAM_INT);
    
    
    $data = new stdClass();
    $data->userid = $USER->id;
    $data->board = $id;
    $data->name = $_POST['name'];
    $data->engname = $_POST['engname'];
    $data->isused = $_POST['isused'];
    $data->sortorder = $_POST['sortorder'];
    $data->timemodified = time();

    $DB->insert_record('jinoboard_category',$data);
     redirect('./category.php?id='.$id);