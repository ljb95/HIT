<?php

require_once dirname(dirname(dirname(__FILE__))) . '/config.php';

$id = optional_param('id', 0, PARAM_INT);
$names = required_param_array(name, PARAM_RAW);
$mode = optional_param('mode', "", PARAM_RAW);

$data = new stdClass();
if (!$id) {
    $data->userid = $USER->id;
    $data->timecreated = time();
    $authid = $DB->insert_record('menu_auth', $data);
    foreach ($names as $lang => $val) {
        $name_data = new stdClass();
        $name_data->lang = $lang;
        $name_data->name = $val;
        $name_data->authid = $authid;
        $name_data->timemodified = time();
        $DB->insert_record('menu_auth_name', $name_data);
    }
} else {
    $data->id = $id;
    $data->muserid = $USER->id;
    $data->timemodified = time();
    $DB->update_record('menu_auth', $data);
    foreach ($names as $lang => $val) {
        $name_data = $DB->get_record('menu_auth_name', array('authid' => $id,'lang'=>$lang));
        $name_data->lang = $lang;
        $name_data->name = $val;
        $name_data->timemodified = time();
        $DB->update_record('menu_auth_name', $name_data);
    }
}

redirect('menu_cap.php');
