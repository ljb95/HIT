<?php
require_once dirname(dirname(dirname(__FILE__))) . '/config.php';

$type = required_param('type', PARAM_INT);      
$parent = optional_param('parent',0, PARAM_INT); // Type이 2일때 상위 객체의 아이디를 받아옴. => 해당 하위객체의 Step들을 가져오기위해 매칭된 Parent 의 Id를 통해 불러온다
$currentstep = optional_param('currentstep', null, PARAM_INT);
$first = optional_param('first', 0, PARAM_INT);
$id = optional_param('id', 0, PARAM_INT);
$mid_parent = optional_param('mid_parent', 0, PARAM_INT);
if(!$id){
?>
<li class="ui-state-default current"><span class="ui-icon ui-icon-arrowthick-2-n-s ui-drag"></span><input type="hidden" name="sort[]" value="0">추가 메뉴</li> 
<?php 
}
    if($type == 2){  
        $menus = $DB->get_records('admin_menu',array('depth'=>2,'parent'=>$parent),'step asc');
    } else if($type == 3){
        $menus = $DB->get_records('admin_menu',array('depth'=>3,'parent'=>$mid_parent),'step asc');
    } else { 
        $menus = $DB->get_records('admin_menu',array('depth'=>1),'step asc');
    }
    foreach($menus as $menu){
         $lang = $DB->get_field('admin_menu_name','name',array('menuid'=>$menu->id,'lang'=> current_language()));
?>
<li class="ui-state-default <?php if($currentstep == $menu->step && $first ==1 && $id){ echo 'current'; } ?>"><span class="ui-icon ui-icon-arrowthick-2-n-s ui-drag"></span><input type="hidden" name="sort[]" value="<?php echo $menu->id; ?>"><?php echo $lang ?></li> 
    <?php } ?>

