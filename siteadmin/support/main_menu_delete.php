<?php

require_once dirname(dirname(dirname(__FILE__))) . '/config.php';
require_once dirname(dirname(__FILE__)) . '/lib.php';

$delete = required_param('id', PARAM_INT);

$menu = $DB->get_record('main_menu',array('id'=>$delete));

$DB->execute("update {main_menu} set step = step - 1 where parent = ? and depth = ? and step >= ?", array($menu->parent, $menu->depth, $menu->step));

$DB->delete_records('main_menu_apply', array('menuid' => $delete));
$DB->delete_records('main_menu_name', array('menuid' => $delete));
$DB->delete_records('main_menu', array('id' => $delete));

if($menu->type == 1){
    $submenus = $DB->get_records('main_menu',array('parent'=>$delete));
    foreach($submenus as $submenu){
        $DB->delete_records('main_menu_apply', array('menuid' => $submenu->id));
        $DB->delete_records('main_menu_name', array('menuid' => $submenu->id));
        $DB->delete_records('main_menu', array('id' => $submenu->id));
    }
    
}
    

redirect('main_menu.php');