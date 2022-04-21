<?php
require(dirname(dirname(dirname(__FILE__))) . '/config.php');
?> 
<link rel="stylesheet" type="text/css" href="./styles/common.css" /> 
<?php
// Check for valid admin user - no guest autologin
require_login(0, false);
if (isguestuser()) {
    $SESSION->wantsurl = (string) new moodle_url('/siteadmin/support/notices_write.php');
    redirect(get_login_url());
}
$context = context_system::instance();
require_capability('moodle/site:config', $context);

$context = context_system::instance();

$id = optional_param("id", 0, PARAM_INT);

$board = $DB->get_record('jinoboard', array('id' => $id));

$allow_sa = $DB->get_record('jinoboard_allowd',array('board'=>$board->id,'allowrole'=>'sa'));
$allow_pr = $DB->get_record('jinoboard_allowd',array('board'=>$board->id,'allowrole'=>'pr'));
$allow_ad = $DB->get_record('jinoboard_allowd',array('board'=>$board->id,'allowrole'=>'ad'));
$allow_rs = $DB->get_record('jinoboard_allowd',array('board'=>$board->id,'allowrole'=>'rs'));
$allow_gu = $DB->get_record('jinoboard_allowd',array('board'=>$board->id,'allowrole'=>'gu'));
      
?>
<?php include_once('../inc/header.php'); ?>
<div id="contents">
    <?php include_once('../inc/sidebar_board.php'); ?>
    <div id="content">
        <h3 class="page_title"><?php echo get_string('board_management', 'local_lmsdata'); ?></h3>
        <div class="page_navbar"><a href="index.php"><?php echo get_string('board_management', 'local_lmsdata'); ?></a> > <a href="./notices.php"><?php echo get_string('board_boardsettings', 'local_lmsdata'); ?></a></div>
        <div class="siteadmin_tabs">
            <a href="<?php echo $CFG->wwwroot; ?>/siteadmin/board/modify.php?id=<?php echo $id; ?>"><p class="black_btn "><?php echo get_string('board_boardInformation', 'local_lmsdata'); ?></p></a>
            <a href="<?php echo $CFG->wwwroot; ?>/siteadmin/board/category.php?id=<?php echo $id; ?>"><p class="black_btn "><?php echo get_string('board_category', 'local_lmsdata'); ?></p></a>
            <a href="<?php echo $CFG->wwwroot; ?>/siteadmin/board/allow.php?id=<?php echo $id; ?>"><p class="black_btn black_btn_selected"><?php echo get_string('board_setpermissions', 'local_lmsdata'); ?></p></a>
        </div>
        <form id="frm_popup_submit" class="popup_submit" enctype="multipart/form-data" action="./allow_submit.php?id=<?php echo $id; ?>" method="POST">
            <input type="hidden" name="sa[id]" value="<?php echo $allow_sa->id; ?>">
            <input type="hidden" name="pr[id]" value="<?php echo $allow_pr->id; ?>">
            <input type="hidden" name="ad[id]" value="<?php echo $allow_ad->id; ?>">
            <input type="hidden" name="rs[id]" value="<?php echo $allow_rs->id; ?>">
            <input type="hidden" name="gu[id]" value="<?php echo $allow_gu->id; ?>">
            <div id="category_content_body">

            </div>
            <div id="category_form">
                    <div> 
                        <table cellpadding="0" cellspacing="0" class="detail">
                            <thead>
                            <tr>
                                    <th> </th>
                                    <th><input type="checkbox" class="sa_all" /><?php echo get_string('contents_all', 'local_lmsdata'); ?></th>
                                    <th><input type="checkbox" class="pr_all" /><?php echo get_string('contents_all', 'local_lmsdata'); ?></th>
                                    <th><input type="checkbox" class="ad_all" /><?php echo get_string('contents_all', 'local_lmsdata'); ?></th>
                                    <th><input type="checkbox" class="rs_all" /><?php echo get_string('contents_all', 'local_lmsdata'); ?></th>
                                    <th><input type="checkbox" class="gu_all" /><?php echo get_string('contents_all', 'local_lmsdata'); ?></th>
                            </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <th class="field_title"><?php echo get_string('board_list', 'local_lmsdata'); ?></th>
                                    <td class="field_value">
                                        <input type="hidden" value="false" name="sa[allowview]">
                                        <input type="checkbox" value="true" class="sa" name="sa[allowview]" <?php if($allow_pr->allowview == 'true'){ echo 'checked'; } ?>> <?php echo get_string('board_admin', 'local_lmsdata'); ?>
                                    </td>
                                    <td class="field_value">
                                        <input type="hidden" value="false" name="pr[allowview]">
                                        <input type="checkbox" value="true" class="pr" name="pr[allowview]" <?php if($allow_pr->allowview == 'true'){ echo 'checked'; } ?>> <?php echo get_string('teacher', 'local_lmsdata'); ?>
                                    </td>
                                    <td class="field_value">
                                        <input type="hidden" value="false" name="ad[allowview]">
                                        <input type="checkbox" value="true" class="ad" name="ad[allowview]" <?php if($allow_ad->allowview == 'true'){ echo 'checked'; } ?>> <?php echo get_string('stats_assistant', 'local_lmsdata'); ?>
                                    </td>
                                    <td class="field_value">
                                        <input type="hidden" value="false" name="rs[allowview]">
                                        <input type="checkbox" value="true" class="rs" name="rs[allowview]" <?php if($allow_rs->allowview == 'true'){ echo 'checked'; } ?>> <?php echo get_string('student', 'local_lmsdata'); ?>
                                    </td>
                                    <td class="field_value">
                                        <input type="hidden" value="false" name="gu[allowview]">
                                        <input type="checkbox" value="true" class="gu" name="gu[allowview]" <?php if($allow_gu->allowview == 'true'){ echo 'checked'; } ?>> <?php echo get_string('board_guest', 'local_lmsdata'); ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th class="field_title"><?php echo get_string('board_viewdetails', 'local_lmsdata'); ?></th>
                                    <td class="field_value">
                                        <input type="hidden" value="false" name="sa[allowdetail]">
                                        <input type="checkbox" value="true" class="sa" name="sa[allowdetail]" <?php if($allow_pr->allowdetail == 'true'){ echo 'checked'; } ?>> <?php echo get_string('board_admin', 'local_lmsdata'); ?>
                                    </td>
                                    <td class="field_value">
                                        <input type="hidden" value="false" name="pr[allowdetail]">
                                        <input type="checkbox" value="true" class="pr" name="pr[allowdetail]" <?php if($allow_pr->allowdetail == 'true'){ echo 'checked'; } ?>> <?php echo get_string('teacher', 'local_lmsdata'); ?>
                                    </td>
                                    <td class="field_value">
                                        <input type="hidden" value="false" name="ad[allowdetail]">
                                        <input type="checkbox" value="true" class="ad" name="ad[allowdetail]" <?php if($allow_ad->allowdetail == 'true'){ echo 'checked'; } ?>> <?php echo get_string('stats_assistant', 'local_lmsdata'); ?>
                                    </td>
                                    <td class="field_value">
                                        <input type="hidden" value="false" name="rs[allowdetail]">
                                        <input type="checkbox" value="true" class="rs" name="rs[allowdetail]" <?php if($allow_rs->allowdetail == 'true'){ echo 'checked'; } ?>> <?php echo get_string('student', 'local_lmsdata'); ?>
                                    </td>
                                    <td class="field_value">
                                        <input type="hidden" value="false"  name="gu[allowdetail]">
                                        <input type="checkbox" value="true" class="gu" name="gu[allowdetail]" <?php if($allow_gu->allowdetail == 'true'){ echo 'checked'; } ?>> <?php echo get_string('board_guest', 'local_lmsdata'); ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th class="field_title"><?php echo get_string('board_writing', 'local_lmsdata'); ?></th>
                                    <td class="field_value">
                                        <input type="hidden" value="false"  name="sa[allowwrite]">
                                        <input type="checkbox" value="true" class="sa" name="sa[allowwrite]" <?php if($allow_pr->allowwrite == 'true'){ echo 'checked'; } ?>> <?php echo get_string('board_admin', 'local_lmsdata'); ?>
                                    </td>
                                    <td class="field_value">
                                        <input type="hidden" value="false"  name="pr[allowwrite]">
                                        <input type="checkbox" value="true" class="pr" name="pr[allowwrite]" <?php if($allow_pr->allowwrite == 'true'){ echo 'checked'; } ?>> <?php echo get_string('teacher', 'local_lmsdata'); ?>
                                    </td>
                                    <td class="field_value">
                                        <input type="hidden" value="false"  name="ad[allowwrite]">
                                        <input type="checkbox" value="true" class="ad" name="ad[allowwrite]" <?php if($allow_ad->allowwrite == 'true'){ echo 'checked'; } ?>> <?php echo get_string('stats_assistant', 'local_lmsdata'); ?>
                                    </td>
                                    <td class="field_value">
                                        <input type="hidden" value="false"  name="rs[allowwrite]">
                                        <input type="checkbox" value="true" class="rs" name="rs[allowwrite]" <?php if($allow_rs->allowwrite == 'true'){ echo 'checked'; } ?>> <?php echo get_string('student', 'local_lmsdata'); ?>
                                    </td>
                                    <td class="field_value">
                                        <input type="hidden" value="false"  name="gu[allowwrite]">
                                        <input type="checkbox" value="true" class="gu" name="gu[allowwrite]" <?php if($allow_gu->allowwrite == 'true'){ echo 'checked'; } ?>> <?php echo get_string('board_guest', 'local_lmsdata'); ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th class="field_title"><?php echo get_string('board_reply', 'local_lmsdata'); ?></th>
                                    <td class="field_value">
                                        <input type="hidden" value="false"  name="sa[allowreply]">
                                        <input type="checkbox" value="true" class="sa" name="sa[allowreply]" <?php if($allow_pr->allowreply == 'true'){ echo 'checked'; } ?>> <?php echo get_string('board_admin', 'local_lmsdata'); ?>
                                    </td>
                                    <td class="field_value">
                                        <input type="hidden" value="false"  name="pr[allowreply]">
                                        <input type="checkbox" value="true" class="pr" name="pr[allowreply]" <?php if($allow_pr->allowreply == 'true'){ echo 'checked'; } ?>> <?php echo get_string('teacher', 'local_lmsdata'); ?>
                                    </td>
                                    <td class="field_value">
                                        <input type="hidden" value="false"  name="ad[allowreply]">
                                        <input type="checkbox" value="true" class="ad" name="ad[allowreply]" <?php if($allow_ad->allowreply == 'true'){ echo 'checked'; } ?>> <?php echo get_string('stats_assistant', 'local_lmsdata'); ?>
                                    </td>
                                    <td class="field_value">
                                        <input type="hidden" value="false"  name="rs[allowreply]">
                                        <input type="checkbox" value="true" class="rs" name="rs[allowreply]" <?php if($allow_rs->allowreply == 'true'){ echo 'checked'; } ?>> <?php echo get_string('student', 'local_lmsdata'); ?>
                                    </td>
                                    <td class="field_value">
                                        <input type="hidden" value="false"  name="gu[allowreply]">
                                        <input type="checkbox" value="true" class="gu" name="gu[allowreply]" <?php if($allow_gu->allowreply == 'true'){ echo 'checked'; } ?>> <?php echo get_string('board_guest', 'local_lmsdata'); ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th class="field_title"><?php echo get_string('board_writecomment', 'local_lmsdata'); ?></th>
                                    <td class="field_value">
                                        <input type="hidden" value="false"  name="sa[allowcomment]">
                                        <input type="checkbox" value="true" class="sa" name="sa[allowcomment]" <?php if($allow_pr->allowcomment == 'true'){ echo 'checked'; } ?>> <?php echo get_string('board_admin', 'local_lmsdata'); ?>
                                    </td>
                                    <td class="field_value">
                                        <input type="hidden" value="false"  name="pr[allowcomment]">
                                        <input type="checkbox" value="true" class="pr" name="pr[allowcomment]" <?php if($allow_pr->allowcomment == 'true'){ echo 'checked'; } ?>> <?php echo get_string('teacher', 'local_lmsdata'); ?>
                                    </td>
                                    <td class="field_value">
                                        <input type="hidden" value="false"  name="ad[allowcomment]">
                                        <input type="checkbox" value="true" class="ad" name="ad[allowcomment]" <?php if($allow_ad->allowcomment == 'true'){ echo 'checked'; } ?>> <?php echo get_string('stats_assistant', 'local_lmsdata'); ?>
                                    </td>
                                    <td class="field_value">
                                        <input type="hidden" value="false"  name="rs[allowcomment]">
                                        <input type="checkbox" value="true" class="rs" name="rs[allowcomment]" <?php if($allow_rs->allowcomment == 'true'){ echo 'checked'; } ?>> <?php echo get_string('student', 'local_lmsdata'); ?>
                                    </td>
                                    <td class="field_value">
                                        <input type="hidden" value="false"  name="gu[allowcomment]">
                                        <input type="checkbox" value="true" class="gu" name="gu[allowcomment]" <?php if($allow_gu->allowcomment == 'true'){ echo 'checked'; } ?>> <?php echo get_string('board_guest', 'local_lmsdata'); ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th class="field_title"><?php echo get_string('board_deletecomment', 'local_lmsdata'); ?></th>
                                    <td class="field_value">
                                        <input type="hidden" value="false"  name="sa[allowdeletecomment]">
                                        <input type="checkbox" value="true" class="sa" name="sa[allowdeletecomment]" <?php if($allow_pr->allowdeletecomment == 'true'){ echo 'checked'; } ?>> <?php echo get_string('board_admin', 'local_lmsdata'); ?>
                                    </td>
                                    <td class="field_value">
                                        <input type="hidden" value="false"  name="pr[allowdeletecomment]">
                                        <input type="checkbox" value="true" class="pr" name="pr[allowdeletecomment]" <?php if($allow_pr->allowdeletecomment == 'true'){ echo 'checked'; } ?>> <?php echo get_string('teacher', 'local_lmsdata'); ?>
                                    </td>
                                    <td class="field_value">
                                        <input type="hidden" value="false"  name="ad[allowdeletecomment]">
                                        <input type="checkbox" value="true" class="ad" name="ad[allowdeletecomment]" <?php if($allow_ad->allowdeletecomment == 'true'){ echo 'checked'; } ?>> <?php echo get_string('stats_assistant', 'local_lmsdata'); ?>
                                    </td>
                                    <td class="field_value">
                                        <input type="hidden" value="false"  name="rs[allowdeletecomment]">
                                        <input type="checkbox" value="true" class="rs" name="rs[allowdeletecomment]" <?php if($allow_rs->allowdeletecomment == 'true'){ echo 'checked'; } ?>> <?php echo get_string('student', 'local_lmsdata'); ?>
                                    </td>
                                    <td class="field_value">
                                        <input type="hidden" value="false"  name="gu[allowdeletecomment]">
                                        <input type="checkbox" value="true" class="gu" name="gu[allowdeletecomment]" <?php if($allow_gu->allowdeletecomment == 'true'){ echo 'checked'; } ?>> <?php echo get_string('board_guest', 'local_lmsdata'); ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th class="field_title"><?php echo get_string('updates', 'local_lmsdata'); ?></th>
                                    <td class="field_value">
                                        <input type="hidden" value="false"  name="sa[allowmodify]">
                                        <input type="checkbox" value="true" class="sa" name="sa[allowmodify]" <?php if($allow_pr->allowmodify == 'true'){ echo 'checked'; } ?>> <?php echo get_string('board_admin', 'local_lmsdata'); ?>
                                    </td>
                                    <td class="field_value">
                                        <input type="hidden" value="false"  name="pr[allowmodify]">
                                        <input type="checkbox" value="true" class="pr" name="pr[allowmodify]" <?php if($allow_pr->allowmodify == 'true'){ echo 'checked'; } ?>> <?php echo get_string('teacher', 'local_lmsdata'); ?>
                                    </td>
                                    <td class="field_value">
                                        <input type="hidden" value="false"  name="ad[allowmodify]">
                                        <input type="checkbox" value="true" class="ad" name="ad[allowmodify]" <?php if($allow_ad->allowmodify == 'true'){ echo 'checked'; } ?>> <?php echo get_string('stats_assistant', 'local_lmsdata'); ?>
                                    </td>
                                    <td class="field_value">
                                        <input type="hidden" value="false"  name="rs[allowmodify]">
                                        <input type="checkbox" value="true" class="rs" name="rs[allowmodify]" <?php if($allow_rs->allowmodify == 'true'){ echo 'checked'; } ?>> <?php echo get_string('student', 'local_lmsdata'); ?>
                                    </td>
                                    <td class="field_value">
                                        <input type="hidden" value="false"  name="gu[allowmodify]">
                                        <input type="checkbox" value="true" class="gu" name="gu[allowmodify]" <?php if($allow_gu->allowmodify == 'true'){ echo 'checked'; } ?>> <?php echo get_string('board_guest', 'local_lmsdata'); ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th class="field_title"><?php echo get_string('deletes', 'local_lmsdata'); ?></th>
                                    <td class="field_value">
                                        <input type="hidden" value="false"  name="sa[allowdelete]">
                                        <input type="checkbox" value="true" class="sa" name="sa[allowdelete]" <?php if($allow_pr->allowdelete == 'true'){ echo 'checked'; } ?>> <?php echo get_string('board_admin', 'local_lmsdata'); ?>
                                    </td>
                                    <td class="field_value">
                                        <input type="hidden" value="false"  name="pr[allowdelete]">
                                        <input type="checkbox" value="true" class="pr" name="pr[allowdelete]" <?php if($allow_pr->allowdelete == 'true'){ echo 'checked'; } ?>> <?php echo get_string('teacher', 'local_lmsdata'); ?>
                                    </td>
                                    <td class="field_value">
                                        <input type="hidden" value="false"  name="ad[allowdelete]">
                                        <input type="checkbox" value="true" class="ad" name="ad[allowdelete]" <?php if($allow_ad->allowdelete == 'true'){ echo 'checked'; } ?>> <?php echo get_string('stats_assistant', 'local_lmsdata'); ?>
                                    </td>
                                    <td class="field_value">
                                        <input type="hidden" value="false"  name="rs[allowdelete]">
                                        <input type="checkbox" value="true" class="rs" name="rs[allowdelete]" <?php if($allow_rs->allowdelete == 'true'){ echo 'checked'; } ?>> <?php echo get_string('student', 'local_lmsdata'); ?>
                                    </td>
                                    <td class="field_value">
                                        <input type="hidden" value="false"  name="gu[allowdelete]">
                                        <input type="checkbox" value="true" class="gu" name="gu[allowdelete]" <?php if($allow_gu->allowdelete == 'true'){ echo 'checked'; } ?>> <?php echo get_string('board_guest', 'local_lmsdata'); ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th class="field_title"><?php echo get_string('contents_attachments', 'local_lmsdata'); ?></th>
                                    <td class="field_value">
                                        <input type="hidden" value="false"  name="sa[allowupload]">
                                        <input type="checkbox" value="true" class="sa" name="sa[allowupload]" <?php if($allow_pr->allowupload == 'true'){ echo 'checked'; } ?>> <?php echo get_string('board_admin', 'local_lmsdata'); ?>
                                    </td>
                                    <td class="field_value">
                                        <input type="hidden" value="false"  name="pr[allowupload]">
                                        <input type="checkbox" value="true" class="pr" name="pr[allowupload]" <?php if($allow_pr->allowupload == 'true'){ echo 'checked'; } ?>> <?php echo get_string('teacher', 'local_lmsdata'); ?>
                                    </td>
                                    <td class="field_value">
                                        <input type="hidden" value="false"  name="ad[allowupload]">
                                        <input type="checkbox" value="true" class="ad" name="ad[allowupload]" <?php if($allow_ad->allowupload == 'true'){ echo 'checked'; } ?>> <?php echo get_string('stats_assistant', 'local_lmsdata'); ?>
                                    </td>
                                    <td class="field_value">
                                        <input type="hidden" value="false"  name="rs[allowupload]">
                                        <input type="checkbox" value="true" class="rs" name="rs[allowupload]" <?php if($allow_rs->allowupload == 'true'){ echo 'checked'; } ?>> <?php echo get_string('student', 'local_lmsdata'); ?>
                                    </td>
                                    <td class="field_value">
                                        <input type="hidden" value="false"  name="gu[allowupload]">
                                        <input type="checkbox" value="true" class="gu" name="gu[allowupload]" <?php if($allow_gu->allowupload == 'true'){ echo 'checked'; } ?>> <?php echo get_string('board_guest', 'local_lmsdata'); ?>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
            </div>
            <div></div>
            <div class="btn_area">
                 <input type="submit" id="add_user" class="blue_btn" value="<?php echo get_string('updates', 'local_lmsdata'); ?>"  />
                <input type="button" id="notice_list" class="normal_btn" value="<?php echo get_string('board_list', 'local_lmsdata'); ?>"  onclick="location.href = 'list.php'" />
            </div> <!-- Bottom Button Area -->

        </form>
    </div>
</div>
<?php include_once('../inc/footer.php'); ?>
<script type="text/javascript">
              $("input:checkbox.sa_all").click(function () {
                if($("input:checkbox.sa_all").prop("checked")) {
                    $("input:checkbox.sa").prop("checked",true);
                } else {
                    $("input:checkbox.sa").prop("checked",false);
                } 
              });
              $("input:checkbox.pr_all").click(function () {
                if($("input:checkbox.pr_all").prop("checked")) {
                    $("input:checkbox.pr").prop("checked",true);
                } else {
                    $("input:checkbox.pr").prop("checked",false);
                } 
              });
              $("input:checkbox.ad_all").click(function () {
                if($("input:checkbox.ad_all").prop("checked")) {
                    $("input:checkbox.ad").prop("checked",true);
                } else {
                    $("input:checkbox.ad").prop("checked",false);
                } 
              });
              $("input:checkbox.rs_all").click(function () {
                if($("input:checkbox.rs_all").prop("checked")) {
                    $("input:checkbox.rs").prop("checked",true);
                } else {
                    $("input:checkbox.rs").prop("checked",false);
                } 
              });
              $("input:checkbox.gu_all").click(function () {
                if($("input:checkbox.gu_all").prop("checked")) {
                    $("input:checkbox.gu").prop("checked",true);
                } else {
                    $("input:checkbox.gu").prop("checked",false);
                } 
              });
</script>
