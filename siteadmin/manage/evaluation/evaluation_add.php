<?php
require_once dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php';
require_once dirname(dirname(dirname(__FILE__))) . '/lib/paging.php';
require_once dirname(dirname(dirname(__FILE__))) . '/lib.php';

// Check for valid admin user - no guest autologin

require_login(0, false);
if (isguestuser()) {
    $SESSION->wantsurl = (string) new moodle_url('/siteadmin/manage/evaluation/evaluation_add.php');
    redirect(get_login_url());
}

$type = required_param('type', PARAM_INT);

$context = context_system::instance();
require_capability('moodle/site:config', $context);

include_once (dirname(dirname(dirname(__FILE__))) . '/inc/header.php'); ?>
<div id="contents">
    <?php include_once (dirname(dirname(dirname(__FILE__))) . '/inc/sidebar_manage.php'); ?>

    <div id="content">
        <h3 class="page_title"><?php echo ($type == 1)?get_string('lectureevaluation', 'local_lmsdata'):get_string('survey', 'local_lmsdata'); ?> <?php echo get_string('add_sample2','local_lmsdata'); ?></h3>
        <div class="page_navbar"><a href="./category_list.php"><?php echo get_string('course_management', 'local_lmsdata'); ?></a> > <a href="evaluation_form.php"><?php echo get_string('evalandsur','local_lmsdata'); ?></a> > <a href="evaluation_form.php"><?php echo get_string('eval_form','local_lmsdata'); ?></a> > <strong><?php echo get_string('add_sample2','local_lmsdata'); ?></strong></div>

        <form id="evaluation_add_form" action="evaluation_submit.php" onsubmit="return validateRequiredFields();" method="post" enctype="multipart/form-data"> 
            <div>
                <table cellpadding="0" cellspacing="0" class="detail">
                    <input type="hidden" name="userid" value="<?php echo $USER->id; ?>" />
                    <input type="hidden" name="mode" value="add" />
                    <tbody>
                        <tr>
                            <td class="field_title"><label for="form_title"><?php echo get_string('title', 'local_lmsdata'); ?></label></td>
                            <td class="field_value">
                                <input type="text" id="form_title" name="title" placeholder="<?php echo get_string('title', 'local_lmsdata'); ?>" maxlength="30" size="30" required />
                            </td>
                        </tr>
                        <input name="type" type="hidden" value="<?php echo $type; ?>">
                        <tr>
                            <td class="field_title"><label for="form_category"><?php echo get_string('category','local_lmsdata'); ?></label></td>
                            <td class="field_value">
                                <?php echo get_string('use','local_lmsdata'); ?> <input name="allow_category" id="form_category" type="radio" value="1" >
                                <?php echo get_string('notuse','local_lmsdata'); ?> <input name="allow_category" type="radio" value="2" checked="">
                            </td>
                        </tr>
                        <tr>
                            <td class="field_title"><label for="form_content"><?php echo get_string('header','local_lmsdata'); ?></label></td>
                            <td class="field_value">
                                <textarea name="contents" id="form_content" style="width:100%; height:auto;" placeholder="<?php echo get_string('header','local_lmsdata'); ?>"></textarea>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div id="btn_area">
                <input type="submit" class="blue_btn" style="float:left; margin-right: 10px;" value="<?php echo get_string('save','local_lmsdata'); ?>" />
                <input type="button" class="normal_btn" style="float:left; margin-right: 10px;" value="<?php echo get_string('list2','local_lmsdata'); ?>" onclick="location.href = '<?php echo ($type == 1)?'evaluation':'survey'; ?>_form.php';"/>
            </div>
        </form><!--Search Area2 End-->
    </div><!--Content End-->

</div> <!--Contents End-->
<script>
    function validateRequiredFields(){
        $('input,textarea,select').attr('required', true).filter(':visible:first').each(function (i, requiredField) {
            if ($(requiredField).val() == ''){
                alert($(requiredField).attr('name'));
                return false;
            }
        });
    }

</script>
<?php
include_once ('../inc/footer.php');
