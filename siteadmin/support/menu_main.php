<?php 

require_once dirname(dirname (dirname (__FILE__))).'/config.php';
require_once $CFG->dirroot.'/siteadmin/lib/paging.php';
require_once $CFG->dirroot.'/siteadmin/lib.php';
// Check for valid admin user - no guest autologin
require_login(0, false);
if (isguestuser()) {
    $SESSION->wantsurl = (string)new moodle_url('/siteadmin/support/menu_main.php');
    redirect(get_login_url());
}
$context = context_system::instance();
require_capability('moodle/site:config', $context);

$menu_number = optional_param('number', 0, PARAM_INT);

$menu_main = get_config('moodle', 'siteadmin_menu_main_set');
if(empty($menu_main)) {
    $menu_main = get_config('moodle', 'siteadmin_menu_main');
    set_config('siteadmin_menu_main_set', $menu_main);
}
$menu_main = unserialize($menu_main);

include_once  ($CFG->dirroot.'/siteadmin/inc/header.php'); ?>
<div id="contents">
    <?php include_once  ($CFG->dirroot.'/siteadmin/inc/sidebar_support.php');?>
    <div id="content">
        <h3 class="page_title"><?php echo get_string('menu_manage','local_lmsdata'); ?></h3>
        <div class="siteadmin_tabs">
            <a href="<?php echo $CFG->wwwroot; ?>/siteadmin/support/menu_main.php"><p class="black_btn black_btn_selected"><?php echo get_string('main_menu','local_lmsdata'); ?></p></a>
            <a href="<?php echo $CFG->wwwroot; ?>/siteadmin/support/menu_footer.php"><p class="black_btn"><?php echo get_string('menu_footer','local_lmsdata'); ?></p></a>
            <a href="<?php echo $CFG->wwwroot; ?>/siteadmin/support/menu_related.php"><p class="black_btn"><?php echo get_string('famliy','local_lmsdata'); ?></p></a>
        </div>
        <div class="page_navbar">
            <a href="<?php echo $CFG->dirroot.'/support/notices.php' ;?>" ><?php echo get_string('site_management', 'local_lmsdata'); ?></a> > 
            <a href="<?php echo $CFG->dirroot.'/support/menu_main.php' ;?>" ><?php echo get_string('menu_manage','local_lmsdata'); ?></a> > 
            <strong><?php echo get_string('main_menu','local_lmsdata'); ?></strong>
        </div>
        <div class="support-menu-main">
            <div class="support-menu-main-list">
                <div class="menu-box-area">
                    <?php 
                        foreach($menu_main as $menu) {
                            $current = $menu->sortorder;
                            $addclass = '';
                            if($current === $menu_number) {
                                $addclass = 'menu-selected';
                            }
                            if(current_language() == 'ko'){
                                echo '<div class="menu-content '.$addclass.'" id = "sortorder-'.$current.'" data-ordering = "'.$current.'"><p>'.$menu->koname.'</p></div>';
                            } else {
                                echo '<div class="menu-content '.$addclass.'" id = "sortorder-'.$current.'" data-ordering = "'.$current.'"><p>'.$menu->enname.'</p></div>';
                            }
                        }
                    ?>
                </div>
                <div class="menu-button-area">
                    <button id="menu_reset_button" class="gray_btn"><?php echo get_string('reset','local_lmsdata'); ?></button>
                    <button id="menu_add_button" class="gray_btn"><?php echo get_string('add_menu','local_lmsdata'); ?>+</button>
                </div>
            </div>
            <div class="support-menu-main-option">
                <?php 
                    $menu = $menu_main[$menu_number];
                    $role = $menu->role;
                    
                    // 아이콘 목록 가져옴
                    $path = $CFG->dirroot.'/pix/jino/mainmenu/';
                    $iconfolder = '/jino/mainmenu/';
                    $ext = '/.png$/u';
                    $ignored = array('.', '..', 'svn');
                    $icons = array_values(array_diff(scandir($path), $ignored));
                    $tmpfiles = array();
                    foreach($icons as $icon) {
                        if(is_file($path.$icon) && preg_match($ext, $icon)) {
                            $filename = preg_replace($ext, '', $icon);
                            array_push($tmpfiles, $filename);
                        }
                        
                    }
                ?>
                <table cellpadding="0" cellspacing="0" class="detail">
                    <tbody>
                    <tr>
                        <td class="field_title"><?php echo get_string('menu1','local_lmsdata'); ?></td>
                        <td class="field_value">
                            <input type="text" title="koreaname" name="koname" placeholder="<?php echo get_string('placeholder4','local_lmsdata'); ?>" size="60" value="<?php echo $menu->koname; ?>"/>
                        </td>
                    </tr>
                    <tr>
                        <td class="field_title"><?php echo get_string('menu2','local_lmsdata'); ?></td>
                        <td class="field_value">
                            <input type="text" title="engname" name="enname" placeholder="<?php echo get_string('placeholder5','local_lmsdata'); ?>" size="60" value="<?php echo $menu->enname; ?>"/>
                        </td>
                    </tr>
                    <tr>
                        <td class="field_title"><?php echo get_string('menu3','local_lmsdata'); ?></td>
                        <td class="field_value">
                            <input type="text" title="url" name="url" placeholder="<?php echo get_string('placeholder6','local_lmsdata'); ?>" size="60" value="<?php echo $menu->url; ?>"/>
                        </td>
                    </tr>
                    <tr>
                        <td class="field_title">Target</td>
                        <td class="field_value">
                            <select title="target" name="target" id="course_search_cata3" class="w_160">
                                <option value="0" <?php echo $menu->target == 0 ? 'selected' : ''; ?>><?php echo get_string('staypage', 'local_lmsdata'); ?></option>
                                <option value="1" <?php echo $menu->target == 1 ? 'selected' : ''; ?>><?php echo get_string('newtab', 'local_lmsdata'); ?></option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td class="field_title"><?php echo get_string('menu4','local_lmsdata'); ?></td>
                        <td class="field_value">
                            <select title="onOff" name="disable" id="course_search_cata3" class="w_160">
                                <option value="1" <?php echo $menu->disable == 1 ? 'selected' : ''; ?>><?php echo get_string('active', 'local_lmsdata'); ?></option>
                                <option value="0" <?php echo $menu->disable == 0 ? 'selected' : ''; ?>><?php echo get_string('nonactive', 'local_lmsdata'); ?></option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td class="field_title"><?php echo get_string('menu5','local_lmsdata'); ?></td>
                        <td class="field_value">
                            <input title="manager" type="checkbox" name="role" value="sa" <?php echo empty($role['sa']) ? '' : 'checked'; ?>/> <?php echo get_string('role1', 'local_lmsdata'); ?>
                            <input title="professor" type="checkbox" name="role" value="pr" <?php echo empty($role['pr']) ? '' : 'checked'; ?>/> <?php echo get_string('teacher', 'local_lmsdata'); ?>
                            <input title="assistant" type="checkbox" name="role" value="ad" <?php echo empty($role['ad']) ? '' : 'checked'; ?>/> <?php echo get_string('role2', 'local_lmsdata'); ?>
                            <input title="student" type="checkbox" name="role" value="rs" <?php echo empty($role['rs']) ? '' : 'checked'; ?>/> <?php echo get_string('role3', 'local_lmsdata'); ?>
                            <input title="gest" type="checkbox" name="role" value="gs" <?php echo empty($role['gs']) ? '' : 'checked'; ?>/> <?php echo get_string('role4', 'local_lmsdata'); ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="field_title"><?php echo get_string('menu6','local_lmsdata'); ?></td>
                        <td class="field_value">
                            <div class="icon_list">
                            <?php 
                                $menu->icon;
                                foreach($tmpfiles as $filename) {
                                   $iconpath = $iconfolder.$filename;
                                   $imgclass = '';
                                   $imgid = $filename;
                                   if($filename == $menu->icon) {
                                       $imgclass = 'img-selected';
                                   }
                                   echo '<span>';
                                   echo $OUTPUT->pix_icon($iconpath, '', 'moodle', array('class'=>$imgclass, 'id'=>$imgid));
                                   echo '</span>';
                                }
                            ?>
                            </div>
                        </td>
                    </tr>
                    </tbody>
                </table>
                <div class="menu-button-area">
                    <input type="hidden" name="icon" value="<?php echo $menu->icon; ?>"/>
                    <input type="hidden" name="default" value="<?php echo $menu->default; ?>"/>
                    <input type="hidden" name="number" value="<?php echo $menu_number; ?>"/>
                    <button id="option_update_button" class="blue_btn"><?php echo get_string('save','local_lmsdata'); ?></button>
                    <button id="menu_delete_button" class="red_btn"><?php echo get_string('delete', 'local_lmsdata'); ?></button> 
                </div> 
            </div>
        </div>
    </div><!--Content End-->
</div> <!--Contents End-->

 <?php include_once  ($CFG->dirroot.'/siteadmin/inc/footer.php');?>

<script type="text/javascript">
    $('.menu-box-area').sortable({
        revert : true,
        //목록 변경이 일어날때
        update : function(event, ui){
            var ordercount = 0;
            var orderArray = [];
            $('.menu-content').each(function(){
                $(this).attr('data-ordering', ordercount);
                orderArray[ordercount] = $(this).attr('id').replace('sortorder-', '');
                ordercount++;
            });
            $.ajax({
                url: '/siteadmin/support/menu_main.ajax.php',
                method: 'POST',
                async: false,
                data: {
                    type : 'change',
                    order : orderArray
                },
                error: function(jqXHR, textStatus, errorThrown ) {
                    alert(jqXHR.responseText);
                }
            });
        }
    });
    $('.menu-content').draggable({
        axis: 'y',
        connectToSortable : '.menu-box-area'
    });
    
    $(document).ready(function(){ 
        // 초기화 버튼 클릭
        $("#menu_reset_button").click(function(){ 
            if(confirm('<?php echo get_string('menureset','local_lmsdata'); ?>')){
                $.ajax({
                    url: '/siteadmin/support/menu_main.ajax.php',
                    method: 'POST',
                    async: false,
                    data: {
                        type : 'reset'
                    },
                    success: function (data) {
                        document.location.href = '<?php echo $CFG->wwwroot.'/siteadmin/support/menu_main.php'; ?>';
                    },
                    error: function(jqXHR, textStatus, errorThrown ) {
                        alert(jqXHR.responseText);
                    }
                });
            }
        });
        // 메뉴추가
        $("#menu_add_button").click(function(){
            var count = $('.menu-content').length;
            $('.menu-content:last').after('<div class="menu-content" id = "sortorder-'+count+'" data-ordering = "'+count+'"><p>추가메뉴</p></div>');
            $.ajax({
                url: '/siteadmin/support/menu_main.ajax.php',
                method: 'POST',
                async: false,
                data: {
                    type : 'add'
                },
                success: function (data) {
                    $('.menu-content').removeClass('menu-selected');
                    $('#sortorder-'+count).addClass('menu-selected');
                    $('#sortorder-'+count).bind('click', function(){
                        var number = $(this).attr('id').replace('sortorder-', '');
                        $('input:hidden[name=number]').attr('value', number);
                        $('.menu-content').removeClass('menu-selected');
                        $(this).addClass('menu-selected');
                        get_menu_option(count);
                    });
                    get_menu_option(count);
                },
                error: function(jqXHR, textStatus, errorThrown ) {
                    alert(jqXHR.responseText);
                }
            });
        }); 
        
        //옵션 저장        
        $("#option_update_button").click(function(){
            var sortorder   = $('.menu-selected').attr('data-ordering');
            var koname   = $("input[name='koname']").val();
            var enname   = $("input[name='enname']").val();
            var url      = $("input[name='url']").val();
            var icon      = $("input[name='icon']").val();
            var target   = $('select[name=target] option:selected').val();
            var disable  = $('select[name=disable] option:selected').val();
            var role     = [];
            var count = 0;
            
            $("input:checkbox[name=role]").each(function(index, element){
              if($(this).is(":checked")){
                  role.push($(this).val()) ;
                  count++;
              }
            });
            
            if( $.trim($("input[name='url']").val()) == '' ) {
                alert("메뉴링크를 입력하세요");
                return false;
            }
            
            $.ajax({
                url: '/siteadmin/support/menu_main.ajax.php',
                method: 'POST',
                async: false,
                data: {
                    type    : 'update',
                    koname  : koname,
                    enname  : enname,
                    url     : url,
                    target  : target,
                    disable : disable,
                    role    : role,
                    sortorder  : sortorder,
                    icon  : icon
                },
                error: function(jqXHR, textStatus, errorThrown ) {
                    alert(jqXHR.responseText);
                }
            });
        }); 
        //메뉴 삭제
        $("#menu_delete_button").click(function(){
            var flag = $('input:hidden[name=default]').val();
            if(flag == 1) {
                alert('<?php echo get_string('alert14','local_lmsdata'); ?>');
            } else {
                if(confirm('<?php echo get_string('alert14','local_lmsdata'); ?>')){
                    var number = $('input:hidden[name=number]').val();
                    $.ajax({
                        url: '/siteadmin/support/menu_main.ajax.php',
                        method: 'POST',
                        async: false,
                        data: {
                            type : 'delete',
                            number : number
                        },
                        success: function (data) {
                            document.location.href = '<?php echo $CFG->wwwroot.'/siteadmin/support/menu_main.php'; ?>';
                        },
                        error: function(jqXHR, textStatus, errorThrown ) {
                            alert(jqXHR.responseText);
                        }
                    });
                }
            }
        });
        // 메뉴 클릭해서 상세
        $(".menu-content").click(function(){ 
            var number = $(this).attr('id').replace('sortorder-', '');
            $('input:hidden[name=number]').attr('value', number);
            $('.menu-content').removeClass('menu-selected');
            $(this).addClass('menu-selected');
            get_menu_option(number);
        });
        // 아이콘 클릭시
        $('.icon_list img').click(function(){
            $('.icon_list img').removeClass('img-selected');
            $(this).addClass('img-selected');
            $('input:hidden[name=icon]').attr('value', $(this).attr('id'));
        });
        
});

function get_menu_option(number) {
   $.ajax({
        url: '/siteadmin/support/menu_main.ajax.php',
        method: 'POST',
        async: false,
        data: {
            type : 'view',
            number : number
        },
        success: function (data) {
            $('input:hidden[name=icon]').attr('value', data.icon);
            $('input:hidden[name=default]').attr('value', data.default);
            $('input:hidden[name=number]').attr('value', number);
            $('input:text[name="koname"]').attr('value', data.koname);
            $('input:text[name="enname"]').attr('value', data.enname);
            $('input:text[name="url"]').attr('value', data.url);
            $('select[name=target] option').removeAttr('selected');
            $('select[name=target] option[value='+data.target+']').prop('selected', true);
            $('select[name=disable] option').removeAttr('selected');
            $('select[name=disable] option[value='+data.disable+']').prop('selected', true);
            $('input:checkbox[name="role"]').removeAttr('checked');
            $('input:checkbox[value="sa"]').prop('checked', data.role.sa);
            $('input:checkbox[value="pr"]').prop('checked', data.role.pr);
            $('input:checkbox[value="ad"]').prop('checked', data.role.ad);
            $('input:checkbox[value="rs"]').prop('checked', data.role.rs);
            $('input:checkbox[value="gs"]').prop('checked', data.role.gs);
            $('.icon_list img').removeClass('img-selected');
            $('#'+data.icon).addClass('img-selected');
        },
        error: function(jqXHR, textStatus, errorThrown ) {
            alert(jqXHR.responseText);
        }
    });
}
</script>
