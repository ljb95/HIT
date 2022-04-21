<?php 
require_once dirname(dirname(dirname (__FILE__))).'/config.php';
require_once dirname(dirname (__FILE__)).'/lib/paging.php';
require_once dirname(dirname (__FILE__)).'/lib.php';

// Check for valid admin user - no guest autologin
require_login(0, false);
if (isguestuser()) {
    $SESSION->wantsurl = (string)new moodle_url('/siteadmin/manage/certi.php');
    redirect(get_login_url());
}
$context = context_system::instance();
require_capability('moodle/site:config', $context);


include_once (dirname(dirname(__FILE__)).'/inc/header.php'); ?>
<div id="contents">
    <?php include_once  (dirname(dirname ( __FILE__)).'/inc/sidebar_manage.php');?>
    <div id="content">
        <h3 class="page_title"><?php echo get_string('add_certi','local_lmsdata'); ?></h3>
        <div class="page_navbar"><a href="course_list.php"><?php echo get_string('course_management', 'local_lmsdata'); ?></a> > <a href="certi.php"><?php echo get_string('diplopia_management', 'local_lmsdata'); ?></a> > <strong><?php echo get_string('add_certi','local_lmsdata'); ?></strong></div>

        <form id="certificate_add_form" action="certi_submit.php" method="post" enctype="multipart/form-data" onsubmit="return update_certificate($(this));"> 
            <div>
                <table cellpadding="0" cellspacing="0" class="detail">
                    <input type="hidden" name="userid" value="<?php echo $USER->id; ?>" />
                    <input type="hidden" name="mode" value="add" />
                    <tbody>
                         <tr>
                             <td class="field_title"><label for="form_content"><?php echo get_string('certi_name','local_lmsdata'); ?><span style="color:red;">*</span></label></td>
                            <td class="field_value"> 
                                <input name="name" type="text" value="">
                            </td>
                        </tr>
                        <tr>
                             <td class="field_title"><label for="form_content"><?php echo get_string('lang','local_lmsdata'); ?><span style="color:red;">*</span></label></td>
                            <td class="field_value"> 
                                <select name="lang">
                                    <option value="ko"><?php echo get_string('ko','local_lmsdata'); ?>(ko)</option>
                                    <option value="en"><?php echo get_string('en','local_lmsdata'); ?>(en)</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td class="field_title"><label for="form_content"><?php echo get_string('background_img','local_lmsdata'); ?></label></td>
                            <td class="field_value">
                                <input name="background_img" type="file" value="">
                            </td>
                        </tr>
                        <tr>
                            <td class="field_title"><label for="form_content"><?php echo get_string('dojang','local_lmsdata'); ?></label></td>
                            <td class="field_value">
                                <input  name="dojang" type="file" value="">
                            </td>
                        </tr>
                        <tr>
                            <td class="field_title"><label for="form_content"><?php echo get_string('Issuer','local_lmsdata'); ?><span style="color:red;">*</span></label></label></td>
                            <td class="field_value">
                                <input name="author" type="text" value="">
                            </td>
                        </tr>
                        <tr>
                            <td class="field_title"><label for="form_content"><?php echo get_string('prefix','local_lmsdata'); ?><span style="color:red;">*</span></label></label></td>
                            <td class="field_value">
                                <input name="prefix" type="text" value="">
                            </td>
                        </tr>
                        <tr>
                            <td class="field_title"><label for="form_content"><?php echo get_string('contents','local_lmsdata'); ?><span style="color:red;">*</span></label></td>
                            <td class="field_value">
                                <textarea name="description" style="width: 100%"></textarea>
                            </td>
                        </tr>
                        <tr>
                            <td class="field_title"><label for="form_content"><?php echo get_string('certi_period','local_lmsdata'); ?></label></td>
                            <td class="field_value">
                                <input name="starttime" type="date" value="<?php echo date("Y-m-d", time()); ?>">
                                ~ 
                                <input name="endtime" type="date" value="<?php echo date("Y-m-d", time() + 86400 * 7); ?>">
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div id="btn_area">
                <input type="submit" class="red_btn" style="float:left; margin-right: 10px;" value="<?php echo get_string('save','local_lmsdata'); ?>" />
                <input type="button" class="blue_btn" style="float:left; margin-right: 10px;" value="<?php echo get_string('list2','local_lmsdata'); ?>" onclick="location.href = 'certi.php';"/>
            </div>
        </form><!--Search Area2 End-->
            
    </div><!--Content End-->
    
</div> <!--Contents End-->
<div id="course_search_dialog">
</div>
<script>
    
    function update_certificate(frm){
        if(!frm.find('input[name=name]').val()){
            alert('<?php echo get_string('alert10','local_lmsdata'); ?>');
            frm.find('input[name=name]').focus();
            return false;
        }
        if(!frm.find('input[name=author]').val()){
            alert('<?php echo get_string('alert8','local_lmsdata'); ?>');
            frm.find('input[name=author]').focus();
            return false;
        }
        if(!frm.find('input[name=prefix]').val()){
            alert('<?php echo get_string('alert9','local_lmsdata'); ?>');
            frm.find('input[name=prefix]').focus();
            return false;
        }
        if(!frm.find('textarea[name=description]').val()){
            alert('<?php echo get_string('alert7','local_lmsdata'); ?>');
            frm.find('input[name=ndescription]').focus();
            return false;
        }
    }
    
    function course_selete(id, name) {
        $('#course_search_dialog').dialog('close');
        $('input[name=courseid]').val(id);
        $('input[name=coursename]').val(name);
        $('input[name=course_name]').val(name);
    }

    $(function () {
        $("#course_search_dialog").css('zIndex', 100);
        $("#course_search_dialog").dialog({
            maxWidth: 800,
            maxHeight: 500,
            width: 800,
            height: 500,
            autoOpen: false,
            hide: {
                duration: 1000,
            },
            buttons: {
                "<?php echo get_string('close','local_lmsdata'); ?>": function () {
                    $(this).dialog("close");
                }
            }
        });

        // 다이얼로그 오픈
        $("#opener_course").click(function () {
            $("#course_search_dialog").dialog("open");
            $.ajax({url: "get_course.ajax.php",
                success: function (result) {
                    $("#course_search_dialog").html(result);
                }
            });
        });
    });
    
</script>
 <?php include_once ('../inc/footer.php');?>
