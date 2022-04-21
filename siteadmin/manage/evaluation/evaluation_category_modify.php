<?php
require_once dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php';
require_once dirname(dirname(dirname(__FILE__))) . '/lib/paging.php';
require_once dirname(dirname(dirname(__FILE__))) . '/lib.php';

// Check for valid admin user - no guest autologin

require_login(0, false);
if (isguestuser()) {
    $SESSION->wantsurl = (string) new moodle_url('/siteadmin/manage/course_list_add.php');
    redirect(get_login_url());
}
$context = context_system::instance();
require_capability('moodle/site:config', $context);

$formid = optional_param('formid', 0, PARAM_INT);
$categoryid = optional_param('categoryid', 0, PARAM_INT);

$category = $DB->get_record('lmsdata_evaluation_category',array('id'=>$categoryid));
$orders = $DB->get_records('lmsdata_evaluation_category', array('formid' => $formid), 'sortorder asc', 'sortorder');

$used = "&nbsp;".get_string('used_order','local_lmsdata')." : ";
foreach ($orders as $order) {
    $used .= $order->sortorder . ",";
}
?>
<?php include_once (dirname(dirname(dirname(__FILE__))) . '/inc/header.php'); ?>
<div id="contents">
    <?php include_once (dirname(dirname(dirname(__FILE__))) . '/inc/sidebar_manage.php'); ?>

    <div id="content">
        <h3 class="page_title"><?php echo get_string('edit_category','local_lmsdata'); ?></h3>
        <div class="page_navbar"><a href="./category_list.php"><?php echo get_string('course_management', 'local_lmsdata'); ?></a> > <a href="evaluation_form.php"><?php echo get_string('evalandsur','local_lmsdata'); ?></a> > <a href="evaluation_form.php"><?php echo get_string('eval_form','local_lmsdata'); ?></a> > <a href="evaluation_categories.php?formid=<?php echo $formid; ?>"><?php echo get_string('prev_sample','local_lmsdata'); ?></a> > <strong><?php echo get_string('edit_category','local_lmsdata'); ?></strong></div>

        <form id="category_add_form" action="evaluation_category_submit.php" onsubmit="return validateRequiredFields();" method="post" enctype="multipart/form-data"> 
            <input type="hidden" name="formid" value="<?php echo $formid; ?>">
            <input type="hidden" name="categoryid" value="<?php echo $category->id; ?>">
            <input type="hidden" name="mode" value="modify">
            <div>
                <table cellpadding="0" cellspacing="0" class="detail">
                    <tbody>
                        <tr>
                            <td class="field_title"><label for="form_name"><?php echo get_string('name','local_lmsdata'); ?></label></td>
                            <td class="field_value">
                                <input type="text" id="form_name" name="name" value="<?php echo $category->name; ?>" placeholder="<?php echo get_string('category_name','local_lmsdata'); ?>" size="80" maxlength="30" required />
                            </td>
                        </tr>
                        <tr>
                            <td class="field_title"><label for="form_sortorder"><?php echo get_string('order','local_lmsdata'); ?></label></td>
                            <td class="field_value">
                                <input type="text" name="sortorder" id="form_sortorder" value="<?php echo $category->sortorder; ?>" placeholder="ex) 1" size="2" />
                                <?php
                                echo rtrim($used, ',');
                                ?>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div id="btn_area">
                <input type="submit" class="red_btn" style="float:left; margin-right: 10px;" value="<?php echo get_string('save','local_lmsdata'); ?>" />
                <input type="button" class="blue_btn" style="float:left; margin-right: 10px;" value="<?php echo get_string('list2','local_lmsdata'); ?>" onclick="location.href = 'evaluation_categories.php?formid=<?php echo $formid; ?>';"/>
            </div>
        </form><!--Search Area2 End-->
    </div><!--Content End-->
    <script>
        function validateRequiredFields() {
            $('input,textarea,select').attr('required', true).filter(':visible:first').each(function (i, requiredField) {
                if ($(requiredField).val() == '') {
                    alert($(requiredField).attr('name'));
                    return false;
                }
            });
        }

    </script>
    <?php include_once ('../../inc/footer.php'); ?>
