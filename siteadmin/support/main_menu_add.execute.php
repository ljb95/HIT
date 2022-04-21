<?php

require_once dirname(dirname(dirname(__FILE__))) . '/config.php';
require_once dirname(dirname(__FILE__)) . '/lib.php';

// Check for valid admin user - no guest autologin

require_login(0, false);

$names = required_param_array('name', PARAM_RAW);
$required = required_param('required', PARAM_INT);
$type = required_param('type', PARAM_INT);
$sort = optional_param_array('sort', array(), PARAM_RAW);
$isused = required_param('isused', PARAM_INT);
$url = optional_param('url', '', PARAM_RAW);
$parent = optional_param('parent', 0, PARAM_INT);
$icon = optional_param('icon', '', PARAM_RAW);
$usergroups = required_param_array('usergroup', PARAM_RAW);
$current_step = optional_param('current_step', 0, PARAM_INT);
$edit = optional_param('edit', 0, PARAM_INT);
$ispopup = optional_param('ispopup', 1, PARAM_INT);

$data = new stdClass();

if (!$edit) { // 수정이아니면.
    if ($type == 2) { // 하위메뉴를 제외하고 depth는 1
        $data->depth = '2';
        $data->parent = $parent;
    } else { 
       $data->depth = '1';
    }
    $data->type = $type; // type 1 = 상위메뉴 2 = 하위메뉴 3 = 링크 4 = 팝업
    $data->icon = $icon;
    $data->required = $required;
    $data->url = $url;
    $data->step = 0;
    $data->userid = $USER->id;
    $data->edituserid = $USER->id; 
    $data->isused = $isused;
    $data->ispopup = $ispopup;
    $data->timecreated = time();
    $data->timemodified = time();

    $new_menu = $DB->insert_record('main_menu', $data);
    if ($type != 2) {
        $DB->set_field('main_menu', 'parent', $new_menu, array('id' => $new_menu));
    }

    foreach ($names as $lang => $name) {
        $name_languge = new stdClass();
        $name_languge->menuid = $new_menu;
        $name_languge->lang = $lang;
        $name_languge->name = $name;
        $name_languge->timemodified = time();
        $name_by_languge = $DB->insert_record('main_menu_name', $name_languge);
    }
    foreach ($usergroups as $usergroup => $val) {
        $menu_usergroup = new stdClass();
        $menu_usergroup->menuid = $new_menu;
        $menu_usergroup->usergroup = $val;
        $menu_usergroup->timecreated = time();
        $menu_usergroup->timemodified = time();
        $name_by_languge = $DB->insert_record('main_menu_apply', $menu_usergroup);
    }
    
    foreach($sort as $step => $mid){
        if($mid == 0){
            $mid = $new_menu;
        }
        $DB->update_record('main_menu',array('id'=>$mid,'step'=>$step));
    }
    

    redirect('main_menu.php');
} else {
    $menu = $DB->get_record('main_menu', array('id' => $edit));
    $data->id = $edit;
    $data->edituserid = $USER->id; 
    $current_groups = $DB->get_records('main_menu_apply', array('menuid' => $edit));

    $ug = array(); // make new apply and change key,value
    foreach ($usergroups as $k => $v) {
        $ug[$v] = $k;
        if ($group_apply = $DB->get_record('main_menu_apply', array('menuid' => $edit, 'usergroup' => $v))) {
            continue;
        } else {
            $menu_usergroup = new stdClass();
            $menu_usergroup->menuid = $edit;
            $menu_usergroup->usergroup = $v;
            $menu_usergroup->timecreated = time();
            $menu_usergroup->timemodified = time();
            $name_by_languge = $DB->insert_record('main_menu_apply', $menu_usergroup);
        }
    }
    /* Delete Undefine Usergroups */
    foreach ($current_groups as $k => $v) {
        if (!isset($ug[$v->usergroup])) {
            $DB->delete_records('main_menu_apply', array('menuid' => $edit, 'usergroup' => $v->usergroup));
        }
    }

    $data->icon = $icon;
    $data->required = $required;
    $data->url = $url;
    $data->type = $type;
    $data->userid = $USER->id;
    $data->ispopup = $ispopup;
    $data->isused = $isused;
    $data->timemodified = time();
    $DB->update_record('main_menu', $data);


    foreach ($names as $lang => $name) {
        if (!$name) {
            $DB->delete_records('main_menu_name', array('menuid' => $edit, 'lang' => $lang));
        } else if (!$lang_name = $DB->get_record('main_menu_name', array('menuid' => $edit, 'lang' => $lang))) {
            $name_languge = new stdClass();
            $name_languge->menuid = $edit;
            $name_languge->lang = $lang;
            $name_languge->name = $name;
            $name_languge->timemodified = time();
            $name_by_languge = $DB->insert_record('main_menu_name', $name_languge);
        } else {
            $name_languge = new stdClass();
            $name_languge->id = $lang_name->id;
            $name_languge->name = $name;
            $name_languge->timemodified = time();
            $name_by_languge = $DB->update_record('main_menu_name', $name_languge);
        }
    }
    
    foreach($sort as $step => $mid){
        $DB->update_record('main_menu',array('id'=>$mid,'step'=>$step));
    }

    




    redirect('main_menu_add.php?id=' . $edit);
}