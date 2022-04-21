<?php
require_once dirname(dirname(dirname(__FILE__))) . '/config.php';

$type = required_param('type', PARAM_INT);       // 1 = 상위 2= 하위 3 = 링크 4 = 팝업    =>  1,3,4 번의 depth는 같기때문에 순번을 공유한다.
$parent = optional_param('parent',0, PARAM_INT); // Type이 2일때 상위 객체의 아이디를 받아옴. => 해당 하위객체의 Step들을 가져오기위해 매칭된 Parent 의 Id를 통해 불러온다
$currentstep = optional_param('currentstep', null, PARAM_INT);
$first = optional_param('first', 0, PARAM_INT);
$id = optional_param('id', 0, PARAM_INT);
if(!$id){
?>
<li class="ui-state-default ui-sortable-handle current"><span class="ui-icon ui-icon-arrowthick-2-n-s ui-drag"></span><input type="hidden" name="sort[]" value="0">추가 메뉴</li> 
<?php  
}
    if($type == 2){  //  2번만 상위 객체에 따라 순번이 달라지고 1,3,4 번의 depth는 같기때문에 순번을 공유한다.
        $menus = $DB->get_records('main_menu',array('depth'=>2,'parent'=>$parent),'step asc');
    } else {
        $menus = $DB->get_records('main_menu',array('depth'=>1),'step asc');
    }
    foreach($menus as $menu){
         $lang = $DB->get_field('main_menu_name','name',array('menuid'=>$menu->id,'lang'=> current_language()));
?>
<li class="ui-state-default ui-sortable-handle <?php if($currentstep == $menu->step && $first ==1 && $id){ echo 'current'; } ?>"><span class="ui-icon ui-icon-arrowthick-2-n-s ui-drag"></span><input type="hidden" name="sort[]" value="<?php echo $menu->id; ?>"><?php echo $lang ?></li> 
    <?php } ?>

