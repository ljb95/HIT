<?php
require_once dirname(dirname(dirname(__FILE__))) . '/config.php';

$id = optional_param('id', 0, PARAM_INT);

$menus = $DB->get_records('admin_menu',array('depth'=>2,'parent'=>$id),'step asc');
if(!$menus){
?>
<option value="">등록된 중메뉴가 없습니다.</option>
<?php 
}

    foreach($menus as $menu){
         $lang = $DB->get_field('admin_menu_name','name',array('menuid'=>$menu->id,'lang'=> current_language()));
?>
<option value="<?php echo $menu->id; ?>"><?php echo $lang; ?></option> 
    <?php } ?>

