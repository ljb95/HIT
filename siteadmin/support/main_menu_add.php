<?php
require_once dirname(dirname(dirname(__FILE__))) . '/config.php';
require_once dirname(dirname(__FILE__)) . '/lib.php';

// Check for valid admin user - no guest autologin

require_login(0, false);
if (isguestuser()) {
    $SESSION->wantsurl = (string) new moodle_url('/siteadmin/manage/main_menu_add.php');
    redirect(get_login_url());
}
$context = context_system::instance();
require_capability('moodle/site:config', $context);

$id = optional_param('id', 0, PARAM_INT);
if ($id) {
    $cu_menu = $DB->get_record('main_menu', array('id' => $id));
}
?>

<?php include_once (dirname(dirname(__FILE__)) . '/inc/header.php'); ?>
<div id="contents">
    <?php include_once (dirname(dirname(__FILE__)) . '/inc/sidebar_support.php'); ?>

    <div id="content">

        <h3 class="page_title"><?php echo get_string('menu_manage', 'local_lmsdata'); ?></h3>
<!--        <div class="page_navbar"><a href="./popup.php"><?php echo get_string('site_management', 'local_lmsdata'); ?></a> > <a href="./course_list.php"><?php echo get_string('menu_manage', 'local_lmsdata'); ?></a></div>-->
        <div class="page_navbar"><a href="./notices.php"><?php echo get_string('site_management', 'local_lmsdata'); ?></a> > <?php echo get_string('menu_manage', 'local_lmsdata'); ?></div>

        <h3 class="page_title"><?php echo empty($id) ? '메뉴 등록' : '메뉴 수정'; ?></h3>
        <div class="page_navbar"><a href="./category_list.php"><?php echo get_string('course_management', 'local_lmsdata'); ?></a> > <a href="./course_list.php"><?php echo get_string('opencourse', 'local_lmsdata'); ?></a> > <?php echo empty($courseid) ? get_string('create_course', 'local_lmsdata') : get_string('edit_course', 'local_lmsdata'); ?></div>

        <form name="" action="main_menu_add.execute.php" method="post" enctype="multipart/form-data">
            <input type="hidden" name="current_step" value="<?php if(isset($cu_menu)){ echo $cu_menu->step; }?>" >
            <?php
            if (isset($cu_menu)) {
            ?>
            <input type="hidden" name="edit" value="<?php echo $cu_menu->id; ?>">
            <?php if($cu_menu->type == 1 ||  $cu_menu->type == 2){ ?>
            <input type="hidden" name="type" value="<?php echo $cu_menu->type; ?>">
            <?php } } ?>
            <table cellpadding="0" cellspacing="0" class="detail">
                <tbody>
                    <tr>
                        <td class="field_title"><span class="required red">*</span>메뉴명</td>
                        <td class="field_value">
                            <?php
                            $languages = get_string_manager()->get_list_of_translations(); // Get languages for quick search later 
                            foreach ($languages as $k => $v) {
                                $lang = ($id) ? $DB->get_field('main_menu_name', 'name', array('menuid' => $id, 'lang' => $k)) : '';
                                echo '<p>' . '<input type="text" value="' . $lang . '" name="name[' . $k . ']">' . $v . '</p>';
                            }
                            ?>
                        </td>
                    </tr>
                    <tr class="">
                        <td class="field_title"><span class="required red">*</span>필수여부</td>
                        <td class="field_value">
                            <select class="w_90" name="required">
                                <option value="1">필수</option>
                                <option <?php if (isset($cu_menu) && $cu_menu->required == 2) {
                                echo 'selected';
                            } ?> value="2">선택</option>
                            </select>
                        </td>
                    </tr>
                    <tr class="">
                        <td class="field_title"><span class="required red">*</span>종류</td>
                        <td class="field_value">
                            <?php if(!isset($cu_menu)){ ?>
                            <select class="w_90 select_type" name="type" onchange="change_type()">
                                <option value="1">상위메뉴</option>
                                <option value="2">하위메뉴</option>
                                <option value="3">링크</option>
                                <option value="4">팝업</option>
                            </select>
                            <p>상위메뉴는 하위메뉴를 추가하기위한 메뉴이며 링크,팝업은 하위메뉴를 추가 할 수 없습니다.</p>
                            <?php 
                            } else {                       
                                switch($cu_menu->type){
                                    case 1: echo '상위메뉴'; break;
                                    case 2: echo '하위메뉴'; break;
                                    case 3: 
                                        echo '<select class="w_90 select_type" name="type" onchange="change_type()">
                                                <option value="3" selected>링크</option>
                                                <option value="4">팝업</option>
                                            </select>'; 
                                        break;
                                    case 4: 
                                        echo '<select class="w_90 select_type" name="type" onchange="change_type()">
                                                <option value="3">링크</option>
                                                <option value="4" selected>팝업</option>
                                            </select>'; 
                                        break;
                                }
                            } 
                            ?>
                        </td>
                    </tr>    
                    <tr class="parent_menu" style="display:none;">
                        <td class="field_title"><span class="required red">*</span>상위메뉴</td>
                        <td class="field_value">
                <?php
                $parents = $DB->get_records('main_menu', array('type' => 1));
                if ($parents) {
                    echo '<select class="w_90" name="parent" onchange="parent_change()">';
                    foreach ($parents as $parent) {
                        $lang = $DB->get_field('main_menu_name', 'name', array('menuid' => $parent->id, 'lang' => current_language()));
                        ?>
                        <option <?php if (isset($cu_menu) && $cu_menu->parent == $parent->id) {
                            echo 'selected';
                        } ?> value="<?php echo $parent->id; ?>"><?php echo $lang; ?></option>
        <?php
    }
    echo '</select>';
} else {
    ?>
                    <p>등록된 상위메뉴가 없습니다. 상위메뉴를 등록 후 하위메뉴를 추가해주세요.</p>
<?php } ?>
                </td>
                </tr>
                <tr class="child_type" style="display:none;">
                    <td class="field_title"><span class="required red">*</span>하위메뉴 종류</td>
                    <td class="field_value">
                        <select class="w_90" name="ispopup">
                            <option value="1">링크</option>
                            <option value="2" <?php if (isset($cu_menu) && $cu_menu->ispopup == 2) { echo 'selected'; } ?>>팝업</option>
                        </select>
                    </td>
                </tr>
                <tr class="">
                    <td class="field_title"><span class="required red">*</span>위치</td>
                    <td class="field_value">
                        <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
                          <script>
                            $( function() {
                              $( "#sortable" ).sortable({
                                  placeholder: "ui-state-highlight",
                              });
                              $( "#sortable" ).disableSelection();
                            } );
                            </script>
                            <ul id="sortable" class="menu_sort">
                          </ul>
                    </td>
                </tr>
                <tr class="url_tr" style="display:none;">
                    <td class="field_title">URL</td>
                    <td class="field_value">
                        <input type="text" class="w_300" name="url" <?php if (isset($cu_menu)) { echo 'value="'.$cu_menu->url.'"'; }   ?> placeholder="ex)http://www.naver.com">
                        <p class="description">http가 포함되지 않으면 <?php echo $CFG->wwwroot; ?>가 자동으로 포함됩니다. /login/index.php 식으로 입력해주세요.</p>
                    </td>
                </tr>
                <tr class="icon_tr"> 
                    <td class="field_title">아이콘</td>
                    <td class="field_value">
                        <input type="text" name="icon" <?php if (isset($cu_menu)) { echo 'value="'.$cu_menu->icon.'"'; }   ?> placeholder="ex)fa-address-book-o">
                        <p class="description">참조 <a target="_blank" href="http://fontawesome.io/icons/">http://fontawesome.io/icons/</a> => 원하는 아이콘을 클릭 후 예재 i class="fa fa-address-book-o" 중 <strong>fa-address-book-o</strong></p>
                    </td>
                </tr>
                <tr>
                    <td class="field_title">사용여부</td>
                    <td class="field_value"> 
                        <select name="isused" class="w_90">
                            <option value="1">사용</option>
                            <option value="2" <?php if (isset($cu_menu) && $cu_menu->isused == 2) { echo 'selected'; } ?>>미사용</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td class="field_title">권한</td>
                    <td class="field_value"> 
<?php
$query = 'select distinct usergroup from {lmsdata_user}';
$usergroups = $DB->get_records_sql($query);
?>
                        <select name="usergroup[]" class="w_90" multiple="">
<?php
foreach ($usergroups as $usergroup => $val) {
    if($usergroup == ''){
        continue;
    }
    if(!isset($cu_menu)){
        $selected = 'selected';
    } else {
        $group = $DB->get_field('main_menu_apply', 'id', array('menuid' => $cu_menu->id, 'usergroup' => $usergroup));
        if($group){
            $selected = 'selected';
        } else {
            $selected = '';
        }
    }
    echo '<option '.$selected.' value="'.$usergroup.'">' . get_string('role:'.$usergroup,'local_lmsdata') . '</option>';
}
?>
                        </select>
                    </td>
                </tr>
                </tbody>
            </table>

            <div id="btn_area">
                <input type="submit" class="blue_btn" style="float:left; margin-right: 10px;" value="<?php echo get_string('save', 'local_lmsdata'); ?>" />
                <input type="button" class="normal_btn" style="float:left; margin-right: 10px;" value="<?php echo get_string('delete', 'local_lmsdata'); ?>" onclick="menu_delete('<?php echo $cu_menu->id; ?>')" />
                <input type="button" class="normal_btn" style="float:left; margin-right: 10px;" value="<?php echo get_string('list2', 'local_lmsdata'); ?>" onclick="location.href = 'main_menu.php';"/>
            </div>
        </form><!--Search Area2 End-->
    </div><!--Content End-->

</div> <!--Contents End-->
<script type="text/javascript">
    change_type();
    function change_type() {
        var type = $('.select_type option:selected').val();           
        if(!type){
            var type = $('input[name=type]').val();
        }
        var parent = $('select[name=parent] option:selected').val();
        var currentstep = $('input[name=current_step]').val();
        switch (type) {
            case '1':
                $('.url_tr').hide();
                $('.parent_menu').hide();
                $('.icon_tr').show();
                $('.child_type').hide();
                break;
            case '2':
                $('.url_tr').show();
                $('.parent_menu').show();
                $('.icon_tr').hide();
                $('.child_type').show();
                break;
            case '3':
                $('.url_tr').show();
                $('.parent_menu').hide();
                $('.icon_tr').show();
                $('.child_type').hide();
                break;
            case '4':
                $('.url_tr').show();
                $('.parent_menu').hide();
                $('.icon_tr').show();
                $('.child_type').hide();
                break;
        }
        $.ajax({
            url: '/siteadmin/support/main_menu_step.ajax.php',
            method: 'POST',
            async: false,
            data: {
                type: type,
                parent: parent,
                currentstep: currentstep,
                id : <?php echo $id; ?> ,
                first: 1
            },
            success: function (data) {
                $('#sortable').html(data);
            },
            error: function (jqXHR, textStatus, errorThrown) {
                alert(jqXHR.responseText);
            }
        });
    }
    function parent_change(){
        var type = $('.select_type option:selected').val();           
        if(!type){
            var type = $('input[name=type]').val();
        }
        var parent = $('select[name=parent] option:selected').val();
        var currentstep = $('input[name=current_step]').val();
        $.ajax({
            url: '/siteadmin/support/main_menu_step.ajax.php',
            method: 'POST',
            async: false,
            data: {
                type: type,
                parent: parent,
                currentstep: currentstep
            },
            success: function (data) {
                $('#sortable').html(data);
            },
            error: function (jqXHR, textStatus, errorThrown) {
                alert(jqXHR.responseText);
            }
        });
    }
    function menu_delete(id){
        if(confirm('메뉴를 삭제하시겠습니까? 상위메뉴의 경우 하위 메뉴도 같이 삭제 됩니다.')){
            location.href='main_menu_delete.php?id='+id;
        }
    }
</script>
<?php
include_once ('../inc/footer.php');
