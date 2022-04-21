<?php
echo "<script>history.back();</script>";
die();
require_once dirname(dirname(dirname (__FILE__))).'/config.php';

/* 
 * type 유형
 * change   -   순서 변경
 * reset    -   메뉴초기화
 * add      -   메뉴추가
 * update   -   메뉴 세부 내용 변경 저장
 * delete   -   메뉴<?php echo get_string('delete', 'local_lmsdata'); ?>
 * view     -   메뉴 세부 내용 호출
 */
$type = required_param('type', PARAM_RAW);

$menu_main = get_config('moodle', 'siteadmin_menu_main_set');
$menu_main = unserialize($menu_main);

if($type === 'change') {
    
    $orderArray = required_param_array('order', PARAM_INT);
    
    $update_menu = Array();
    foreach($orderArray as $key => $order) {
        $menu_main[$order]->sortorder = $key;
        array_push($update_menu, $menu_main[$order]);
    }
    
    $update_menu = serialize($update_menu);
    set_config('siteadmin_menu_main_set', $update_menu);
    
} else if($type === 'reset') {
    
    $menu_main = get_config('moodle', 'siteadmin_menu_main');
    set_config('siteadmin_menu_main_set', $menu_main);
    
} else if($type === 'add') {
    $menu = new Stdclass();
    $menu->sortorder = count($menu_main);
    $menu->default = false;
    $menu->koname = '추가메뉴';
    $menu->enname = 'NEW MENU';
    $menu->url = '';
    $menu->target = '';
    $menu->disable = false;
    $menu->role = array('sa'=> false, 'pr'=>false, 'ad'=>false, 'rs'=>false, 'gs'=>false);
    $menu->icon = $menu_main[0]->icon;
    $menu_main[$menu->sortorder] = $menu;
    
    $menu_main = serialize($menu_main);
    set_config('siteadmin_menu_main_set', $menu_main);
    
} else if($type === 'update') {
    $sortorder = required_param('sortorder', PARAM_INT);
    $koname = required_param('koname', PARAM_RAW);
    $enname = required_param('enname', PARAM_RAW);
    $url = required_param('url', PARAM_RAW);
    $target = required_param('target', PARAM_INT);
    $disable = required_param('disable', PARAM_INT);
    $role = optional_param_array('role', array(), PARAM_RAW);
    $icon = required_param('icon', PARAM_RAW);
    
    $menu = $menu_main[$sortorder];
    $menu->sortorder = $sortorder;
    $menu->koname = $koname; 
    $menu->enname = $enname;
    $menu->url = $url;
    $menu->target = $target;
    $menu->disable = $disable;
    $menu->icon = $icon;
    
    foreach($menu->role as $key => $value) {
        if(in_array($key, $role)) {
            $menu->role[$key] = true;
        } else {
            $menu->role[$key] = false;
        }
    }
    
    $menu_main[$menu->sortorder] = $menu;
    $menu_main = serialize($menu_main);
    set_config('siteadmin_menu_main_set', $menu_main);
    
} else if($type === 'delete') {
    $number = required_param('number', PARAM_INT);
    
    unset($menu_main[$number]);
    $menu_main = serialize($menu_main);
    set_config('siteadmin_menu_main_set', $menu_main);
    
} else if($type === 'view') {
    $number = required_param('number', PARAM_INT);
    $menu_main[$number]->target = empty($menu_main[$number]->target) ? 0 : 1;
    $menu_main[$number]->disable = empty($menu_main[$number]->disable) ? 0 : 1;
    $menu_main[$number]->default = empty($menu_main[$number]->default) ? 0 : 1;
    @header('Content-type: application/json; charset=utf-8');
    echo json_encode($menu_main[$number]);
}
?>