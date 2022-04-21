<?php
require_once dirname(dirname(dirname(__FILE__))) . '/config.php';
require_once dirname(dirname(__FILE__)) . '/lib.php';

// Check for valid admin user - no guest autologin

require_login(0, false);
if (isguestuser()) {
    $SESSION->wantsurl = (string) new moodle_url('/siteadmin/manage/admin_menu_add.php');
    redirect(get_login_url());
}
$context = context_system::instance();
require_capability('moodle/site:config', $context);

$id = optional_param('id', 0, PARAM_INT);

if ($id) {
    $admin_menu = $DB->get_record('admin_menu', array('id' => $id));
}
?>

<?php include_once (dirname(dirname(__FILE__)) . '/inc/header.php'); ?>
<div id="contents">
    <?php include_once (dirname(dirname(__FILE__)) . '/inc/sidebar_support.php'); ?>

    <div id="content">
        <h3 class="page_title"><?php echo empty($id) ? '메뉴 등록' : '메뉴 수정'; ?></h3>
        <div class="page_navbar"><a href="./category_list.php"><?php echo get_string('course_management', 'local_lmsdata'); ?></a> > <a href="./course_list.php"><?php echo get_string('opencourse', 'local_lmsdata'); ?></a> > <?php echo empty($courseid) ? get_string('create_course', 'local_lmsdata') : get_string('edit_course', 'local_lmsdata'); ?></div>
        <form name="" action="admin_menu_add.execute.php" method="post" enctype="multipart/form-data">
            <input type="hidden" name="current_step" value="<?php if(isset($admin_menu)){ echo $admin_menu->step; }?>" >
            <?php
            if (isset($admin_menu)) {
            ?>
            <input type="hidden" name="edit" value="<?php echo $admin_menu->id; ?>">
            <?php if($admin_menu->type == 1 ||  $admin_menu->type == 2 || $admin_menu->type == 3){ ?>
            <input type="hidden" name="type" value="<?php echo $admin_menu->type; ?>">
            <?php } } ?>
            <table cellpadding="0" cellspacing="0" class="detail">
                <tbody>
                    <tr>
                        <td class="field_title"><span class="required red">*</span>메뉴명</td>
                        <td class="field_value">
                            <?php
                            $languages = get_string_manager()->get_list_of_translations(); // Get languages for quick search later 
                            foreach ($languages as $k => $v) {
                                $lang = ($id) ? $DB->get_field('admin_menu_name', 'name', array('menuid' => $id, 'lang' => $k)) : '';
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
                                <option <?php if (isset($admin_menu) && $admin_menu->required == 2) {
                                echo 'selected';
                            } ?> value="2">선택</option>
                            </select>
                        </td>
                    </tr>
                    <tr class="">
                        <td class="field_title"><span class="required red">*</span>종류</td>
                        <td class="field_value">
                            <?php if(!isset($admin_menu)){ ?>
                            <select class="w_90 select_type" name="type" onchange="change_type()">
                                <option value="1">대분류</option>
                                <option value="2">중분류</option>
                                <option value="3">소분류</option>
                                <option value="4">링크</option>
                                <option value="5">팝업</option>
                            </select>
                            <p>상위메뉴는 하위메뉴를 추가하기위한 메뉴이며 링크,팝업은 하위메뉴를 추가 할 수 없습니다.</p>
                            <?php 
                            } else { 
                                switch($admin_menu->type){
                                    case 1: echo '대메뉴'; break;
                                    case 2: echo '중메뉴'; break;
                                    case 3: echo '소메뉴'; break;
                                    case 4: 
                                        echo '<select class="w_90 select_type" name="type" onchange="change_type()">
                                                <option value="4" selected>링크</option>
                                                <option value="5">팝업</option>
                                            </select>'; 
                                        break;
                                    case 5: 
                                        echo '<select class="w_90 select_type" name="type" onchange="change_type()">
                                                <option value="4">링크</option>
                                                <option value="5" selected>팝업</option>
                                            </select>'; 
                                        break;
                                }
                            } 
                            ?>
                        </td>
                    </tr>    
                    <tr class="parent_menu" style="display:none;">
                        <td class="field_title"><span class="required red">*</span>대메뉴</td>
                        <td class="field_value">
                <?php
                $parents = $DB->get_records('admin_menu', array('type' => 1),'step asc');
                if ($parents) {
                    echo '<select class="w_90" name="parent" onchange="parent_change()">';
                    foreach ($parents as $parent) {
                        $lang = $DB->get_field('admin_menu_name', 'name', array('menuid' => $parent->id, 'lang' => current_language()));
                        ?>
                        <option <?php if (isset($admin_menu) && $admin_menu->parent == $parent->id) {
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
                <tr class="parent_middle_menu" style="display:none;">
                        <td class="field_title"><span class="required red">*</span>중메뉴</td>
                        <td class="field_value">
                <?php
                $parents = $DB->get_records('admin_menu', array('type' => 2));
                if ($parents) {
                    echo '<select class="w_90" name="parent_mid" onchange="parent_change()">';
                    foreach ($parents as $parent) {
                        $lang = $DB->get_field('admin_menu_name', 'name', array('menuid' => $parent->id, 'lang' => current_language()));
                        ?>
                        <option <?php if (isset($admin_menu) && $admin_menu->parent == $parent->id) {
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
                    <td class="field_title"><span class="required red">*</span>메뉴 종류</td>
                    <td class="field_value">
                        <select class="w_90 no_last"  name="ispopup">
                            <option value="2">링크</option>
                            <option value="3" <?php if (isset($admin_menu) && $admin_menu->ispopup == 2) { echo 'selected'; } ?>>팝업</option>
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
                                  placeholder: "ui-state-highlight"
                              });
                              $( "#sortable" ).disableSelection();
                            } );
                            </script>
                        <ul id="sortable">
                          </ul>
                    </td>
                </tr>
                <tr class="url_tr">
                    <td class="field_title"><span class="required red">*</span>URL</td>
                    <td class="field_value">
                        <input type="text" class="w_300" name="url" <?php if (isset($admin_menu)) { echo 'value="'.$admin_menu->url.'"'; }   ?> placeholder="ex)http://www.naver.com">
                        <p class="description">http가 포함되지 않으면 <?php echo $CFG->wwwroot; ?>가 자동으로 포함됩니다. /login/index.php 식으로 입력해주세요.</p>
                    </td>
                </tr>
                <tr>
                    <td class="field_title">사용여부</td>
                    <td class="field_value"> 
                        <select name="isused" class="w_90">
                            <option value="1">사용</option>
                            <option value="2" <?php if (isset($admin_menu) && $admin_menu->isused == 2) { echo 'selected'; } ?>>미사용</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td class="field_title">권한</td>
                    <td class="field_value"> 
<?php
$query = 'select ma.id, man.name from {menu_auth} ma  '
        . 'join {menu_auth_name} man on man.authid = ma.id and man.lang = :lang';
$usergroups = $DB->get_records_sql($query,array('lang'=> current_language()));
?>
                        <select name="usergroup[]" class="w_90" multiple=""> 
<?php
foreach ($usergroups as $usergroup => $val) { 
    if(!isset($admin_menu)){
        $selected = 'selected';
    } else {
        $group = $DB->get_field('admin_menu_apply', 'id', array('menuid' => $admin_menu->id, 'admingroup' => $val->id));
        if($group){
            $selected = 'selected';
        } else {
            $selected = '';
        }
    }
    echo '<option '.$selected.' value="'.$val->id.'">' . $val->name . '</option>';
}
?>
                        </select>
                    </td>
                </tr>
                </tbody>
            </table>

            <div id="btn_area">
                <input type="submit" class="blue_btn" style="float:left; margin-right: 10px;" value="<?php echo get_string('save', 'local_lmsdata'); ?>" />
                <!--input type="button" class="normal_btn" style="float:left; margin-right: 10px;" value="<?php echo get_string('delete', 'local_lmsdata'); ?>" onclick="course_delete('<?php echo $courseid; ?>')"/-->
                <input type="button" class="normal_btn" style="float:left; margin-right: 10px;" value="<?php echo get_string('list2', 'local_lmsdata'); ?>" onclick="location.href = 'admin_menu.php';"/>
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
        var mid_parent = $('select[name=parent_mid] option:selected').val();
        var currentstep = $('input[name=current_step]').val();
        switch (type) {
            case '1':
                $('.parent_menu').hide();
                $('.parent_middle_menu').hide();
                $('.child_type').hide();
                break;
            case '2':
                $('.parent_menu').show();
                $('.parent_middle_menu').hide();
                $('.child_type').show();
                break;
            case '3':
                $('.parent_menu').show();
                $('.parent_middle_menu').show();
                $('.child_type').show();
                
                break;
            case '4':
                $('.parent_menu').hide();
                $('.parent_middle_menu').hide();
                $('.child_type').hide();
                break;
                case '5':
                $('.parent_menu').hide();
                $('.parent_middle_menu').hide();
                $('.child_type').hide();
                break;
        }
        $.ajax({
            url: '/siteadmin/support/admin_menu_step.ajax.php',
            method: 'POST',
            async: false,
            data: {
                type: type,
                parent: parent,
                currentstep: currentstep,
                mid_parent: mid_parent,
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
    // 상위메뉴 수정
    function parent_change(){
        var type = $('.select_type option:selected').val();           
        if(!type){
            var type = $('input[name=type]').val();
        }
        if(type == 3){
            var parent = $('select[name=parent_mid] option:selected').val();
        } else {
            var parent = $('select[name=parent] option:selected').val();
        }
        var currentstep = $('input[name=current_step]').val();
        $.ajax({
            url: '/siteadmin/support/admin_menu_step.ajax.php',
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
        
        if(type == 3){
           $.ajax({
            url: '/siteadmin/support/admin_get_mid.ajax.php',
            method: 'POST',
            async: false,
            data: {
                id : $('select[name=parent] option:selected').val() 
            },
            success: function (data) {
                 $('select[name=parent_mid]').html(data);
            },
            error: function (jqXHR, textStatus, errorThrown) {
                alert(jqXHR.responseText);
            }
        });
        }
    }
</script>
<?php
include_once ('../inc/footer.php');
