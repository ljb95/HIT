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
    $cap = $DB->get_record('menu_auth', array('id' => $id));
}
?>

<?php include_once (dirname(dirname(__FILE__)) . '/inc/header.php'); ?>
<div id="contents">
    <?php include_once (dirname(dirname(__FILE__)) . '/inc/sidebar_support.php'); ?>

    <div id="content">
        <h3 class="page_title"><?php echo empty($id) ? '메뉴 권한 등록' : '메뉴 권한 수정'; ?></h3>
        <div class="page_navbar"><a href="./category_list.php"><?php echo get_string('course_management', 'local_lmsdata'); ?></a> > <a href="./course_list.php"><?php echo get_string('opencourse', 'local_lmsdata'); ?></a> > <?php echo empty($courseid) ? get_string('create_course', 'local_lmsdata') : get_string('edit_course', 'local_lmsdata'); ?></div>
        <form name="" action="cap_submit.php" method="post" enctype="multipart/form-data">
            <input type="hidden" name="current_step" value="<?php if(isset($menu)){ echo $menu->step; }?>" >
            <input type="hidden" name="id" value="<?php echo $id?>" >
            <?php
            if (isset($menu)) {
            ?>
            <input type="hidden" name="edit" value="<?php echo $menu->id; ?>">
            <?php if($menu->type == 1 ||  $menu->type == 2){ ?>
            <input type="hidden" name="type" value="<?php echo $menu->type; ?>">
            <?php } } ?>
            <table cellpadding="0" cellspacing="0" class="detail">
                <tbody>
                    <tr>
                        <td class="field_title">권한명</td>
                        <td class="field_value">
                            <?php
                            $languages = get_string_manager()->get_list_of_translations(); // Get languages for quick search later 
                            foreach ($languages as $k => $v) {
                                $menu_auth_name = $DB->get_record('menu_auth_name', array('authid' => $id,'lang'=>$k));
                                echo '<p>' . '<input type="text" value="' . $menu_auth_name->name . '" name="name[' . $k . ']">' . $v;
                                if($k == 'ko' || $k == 'en'){
                                    echo '<span class="required red">*</span>';
                                }
                                echo '</p>';
                            }
                            ?>
                        </td>
                    </tr>
                </tbody>
            </table>

            <div id="btn_area">
                <input type="submit" class="blue_btn" style="float:left; margin-right: 10px;" value="<?php echo get_string('save', 'local_lmsdata'); ?>" />
                <?php if($id){ ?>
                <input type="button" class="normal_btn" style="float:left; margin-right: 10px;" value="<?php echo get_string('delete', 'local_lmsdata'); ?>" onclick="delete_popup(<?php echo $id; ?>);"/>
                <?php } ?>
                <input type="button" class="normal_btn" style="float:left; margin-right: 10px;" value="<?php echo get_string('list2', 'local_lmsdata'); ?>" onclick="location.href = 'menu_cap.php';"/>
            </div>
        </form><!--Search Area2 End-->
    </div><!--Content End-->

</div> <!--Contents End-->

<script type="text/javascript">
    function delete_popup(id) {
        var tag = $("<div id='delete_popup'></div>");
        $.ajax({
          url: '<?php echo $CFG->wwwroot.'/siteadmin/support/menu_cap_list.ajax.php'; ?>',
          method: 'POST',
          data: {id:id},
          success: function(data) {
            tag.html(data).dialog({
                title: '권한삭제',
                modal: true,
                width: 800,
                resizable: false,
                height: 200,
                buttons: [ {id:'delete',
                            text:'<?php echo get_string('delete','local_lmsdata'); ?>',
                            disable: true,
                            click: function() {
                                var change_auth = $('select[name=auth]').val();
                                if(change_auth == '-1'){
                                    alert('대체 가능한 권한이 없어서 삭제할 수 없습니다.');
                                } else {
                                       $.ajax({
                                            url:'menu_cap_del.php',
                                            type:'POST',
                                            data: {id:<?php echo $id; ?>,changeid:change_auth},
                                            success:function(data){
                                                alert('삭제되었습니다.');
                                                location.href='menu_cap.php';
                                            }
                                        });
                                }
                                // $( this ).dialog( "close" );
                            }
                        },{id:'close',
                            text:'<?php echo get_string('cancle','local_lmsdata'); ?>',
                            disable: true,
                            click: function() {
                                $( this ).dialog( "close" );
                            }
                        }],
                close: function () {
                    $('#frm_course_prof').remove();
                    $( this ).dialog('destroy').remove()
                }
            }).dialog('open');
          }
        });
    }
</script>
<?php
include_once ('../inc/footer.php');
