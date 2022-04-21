<?php
require(dirname(dirname(dirname(__FILE__))) . '/config.php');

?>
<link rel="stylesheet" type="text/css" href="./styles/common.css" />
<?php
// Check for valid admin user - no guest autologin
require_login(0, false);
if (isguestuser()) {
    $SESSION->wantsurl = (string)new moodle_url('/siteadmin/support/notices_write.php');
    redirect(get_login_url());
}
$context = context_system::instance();
require_capability('moodle/site:config', $context);

    $context = context_system::instance();        
    
    $id = optional_param("id", 0, PARAM_INT);
    
    $board = $DB->get_record('jinoboard',array('id'=>$id));

?>
<?php include_once('../inc/header.php');?>
<div id="contents">
    <?php include_once('../inc/sidebar_board.php');?>
    <div id="content">
    <h3 class="page_title"><?php echo get_string('board_management', 'local_lmsdata'); ?></h3>
    <div class="page_navbar"><a href="<?php echo $CFG->wwwroot.'/siteadmin/board/list.php';?>"><?php echo get_string('board_management', 'local_lmsdata'); ?></a> > <a href="#"><?php echo get_string('board_boardsettings', 'local_lmsdata'); ?></a></div>
    <div class="siteadmin_tabs">
            <a href="<?php echo $CFG->wwwroot; ?>/siteadmin/board/modify.php?id=<?php echo $id; ?>"><p class="black_btn black_btn_selected"><?php echo get_string('board_boardInformation', 'local_lmsdata'); ?></p></a>
            <a href="<?php echo $CFG->wwwroot; ?>/siteadmin/board/category.php?id=<?php echo $id; ?>"><p class="black_btn "><?php echo get_string('board_category', 'local_lmsdata'); ?></p></a>
            <a href="<?php echo $CFG->wwwroot; ?>/siteadmin/board/allow.php?id=<?php echo $id; ?>"><p class="black_btn "><?php echo get_string('board_setpermissions', 'local_lmsdata'); ?></p></a>
        </div>
    <form id="frm_popup_submit" class="popup_submit" enctype="multipart/form-data" action="./modify_submit.php" method="POST">
        <input type="hidden" name="id" value="<?php echo $board->id; ?>">
<table cellpadding="0" cellspacing="0" class="detail">

    <tbody>
        <tr>
            <td class="field_title"><?php echo get_string('siteadmin_boardnameko', 'local_lmsdata'); ?></td>
            <td class="field_value">
                <input type="text" class="w_300" name ="name" value="<?php echo $board->name; ?>"/>
            </td>
        </tr>
        <tr>
            <td class="field_title"><?php echo get_string('siteadmin_boardnameen', 'local_lmsdata'); ?></td>
            <td class="field_value">
                <input type="text" class="w_300" name ="engname" value="<?php echo $board->engname; ?>" />
            </td>
        </tr>
        <tr>
            <td class="field_title"><?php echo get_string('siteadmin_location', 'local_lmsdata'); ?></td>
            <td class="field_value">
                <select name="type" class="w_120">
                    <option value="5"><?php echo get_string('siteadmin_mainmenu', 'local_lmsdata'); ?></option>
                    <option value="6" <?php if($board->type == 6){ echo 'selected'; } ?>><?php echo get_string('siteadmin_mypage', 'local_lmsdata'); ?></option>
                </select>
            </td>
        </tr>
        <tr>
            <td class="field_title"><?php echo get_string('siteadmin_actyn', 'local_lmsdata'); ?></td>
            <td class="field_value">
                <select name="status" class="w_70">
                    <option value="1"><?php echo get_string('siteadmin_act', 'local_lmsdata'); ?></option>
                    <option value="2" <?php if($board->status == 2){ echo 'selected'; } ?>><?php echo get_string('siteadmin_noact', 'local_lmsdata'); ?></option>
                </select>
            </td>
        </tr>
        <tr>
            <td class="field_title"><?php echo get_string('siteadmin_filemax', 'local_lmsdata'); ?></td>
            <td class="field_value">
                <select name="maxbytes" class="w_90">
                    <option value="0"><?php echo get_string('siteadmin_nolimit', 'local_lmsdata'); ?></option>
                    <option <?php if($board->maxbytes == 1024){ echo 'selected'; } ?> value="1024">1M</option>
                    <option <?php if($board->maxbytes == 10240){ echo 'selected'; } ?> value="10240">10M</option>
                    <option <?php if($board->maxbytes == 102400){ echo 'selected'; } ?> value="102400">100M</option>
                    <option <?php if($board->maxbytes == 512000){ echo 'selected'; } ?> value="512000">500M</option>
                    <option <?php if($board->maxbytes == 1048576){ echo 'selected'; } ?> value="1048576">1G</option>
                </select>
            </td>
        </tr>
        <tr>
            <td class="field_title"><?php echo get_string('siteadmin_filemaxcount', 'local_lmsdata'); ?></td>
            <td class="field_value">
                <select name="maxattachments" class="w_90">
                    <option value="0"><?php echo get_string('siteadmin_nolimit', 'local_lmsdata'); ?></option>
                    <option <?php if($board->maxattachments == 1){ echo 'selected'; } ?> value="1">1</option>
                    <option <?php if($board->maxattachments == 3){ echo 'selected'; } ?> value="3">3</option>
                    <option <?php if($board->maxattachments == 5){ echo 'selected'; } ?> value="5">5</option>
                    <option <?php if($board->maxattachments == 10){ echo 'selected'; } ?> value="10">10</option>
                </select>
            </td>
        </tr>
        <tr>
            <td class="field_title"><?php echo get_string('siteadmin_newcontent', 'local_lmsdata'); ?></td>
            <td class="field_value">
                <select name="allownew" class="w_70">
                    <option value="1"><?php echo get_string('siteadmin_use', 'local_lmsdata'); ?></option>
                    <option <?php if($board->allownew == 0){ echo 'selected'; } ?> value="0"><?php echo get_string('siteadmin_nouse', 'local_lmsdata'); ?></option>
                </select>
                <input type="number" name="newday" class="w_50" size="3" value="<?php echo $board->newday; ?>" maxlength="3"> <?php echo get_string('contents_day', 'local_lmsdata'); ?>
            </td>
        </tr>
        <tr>
            <td class="field_title"><?php echo get_string('siteadmin_notice', 'local_lmsdata'); ?></td>
            <td class="field_value">
                <select name="allownotice" class="w_70">
                    <option value="1"><?php echo get_string('siteadmin_use', 'local_lmsdata'); ?></option>
                    <option <?php if($board->allownotice == 0){ echo 'selected'; } ?> value="0"><?php echo get_string('siteadmin_nouse', 'local_lmsdata'); ?></option>
                </select>
            </td>
        </tr>
        <tr>
            <td class="field_title"><?php echo get_string('siteadmin_reply', 'local_lmsdata'); ?></td>
            <td class="field_value">
                <select name="allowreply" class="w_70">
                    <option value="1"><?php echo get_string('siteadmin_use', 'local_lmsdata'); ?></option>
                    <option value="0" <?php if($board->allowreply == 0){ echo 'selected'; } ?>><?php echo get_string('siteadmin_nouse', 'local_lmsdata'); ?></option>
                </select>
            </td>
        </tr>
        <tr>
            <td class="field_title"><?php echo get_string('siteadmin_coment', 'local_lmsdata'); ?></td>
            <td class="field_value">
                <select name="allowcomment" class="w_70">
                    <option value="1"><?php echo get_string('siteadmin_use', 'local_lmsdata'); ?></option>
                    <option value="0" <?php if($board->allowcomment == 0){ echo 'selected'; } ?>><?php echo get_string('siteadmin_nouse', 'local_lmsdata'); ?></option>
                </select>
            </td>
        </tr>
                <tr>
            <td class="field_title"><?php echo get_string('contents_day', 'local_lmsdata'); ?></td>
            <td class="field_value">
                <select name="allowrental" class="w_70">
                    <option value="1"><?php echo get_string('siteadmin_use', 'local_lmsdata'); ?></option>
                    <option value="0" <?php if($board->allowrental == 0){ echo 'selected'; } ?>><?php echo get_string('siteadmin_nouse', 'local_lmsdata'); ?></option>
                </select>
            </td>
        </tr>
        <tr>
            <td class="field_title"><?php echo get_string('siteadmin_fileyn', 'local_lmsdata'); ?></td>
            <td class="field_value">
                <select name="allowupload" class="w_70">
                    <option value="1"><?php echo get_string('siteadmin_use', 'local_lmsdata'); ?></option>
                    <option value="0" <?php if($board->allowupload == 0){ echo 'selected'; } ?>><?php echo get_string('siteadmin_nouse', 'local_lmsdata'); ?></option>
                </select>
            </td>
        </tr>
        <tr>
            <td class="field_title"><?php echo get_string('siteadmin_secret', 'local_lmsdata'); ?></td>
            <td class="field_value">
                <select name="allowsecret" class="w_70">
                    <option value="1"><?php echo get_string('siteadmin_use', 'local_lmsdata'); ?></option>
                    <option value="0" <?php if($board->allowsecret == 0){ echo 'selected'; } ?>><?php echo get_string('siteadmin_nouse', 'local_lmsdata'); ?></option>
                </select>
            </td>
        </tr>
        <tr>
            <td class="field_title"><?php echo get_string('siteadmin_category', 'local_lmsdata'); ?></td>
            <td class="field_value">
                <select name="allowcategory" class="w_70">
                    <option value="1"><?php echo get_string('siteadmin_use', 'local_lmsdata'); ?></option>
                    <option value="0" <?php if($board->allowcategory == 0){ echo 'selected'; } ?>><?php echo get_string('siteadmin_nouse', 'local_lmsdata'); ?></option>
                </select>
            </td>
        </tr>
        <tr>
            <td class="field_title"><?php echo get_string('siteadmin_publicationperiod', 'local_lmsdata'); ?></td>
            <td class="field_value">
                <select name="allowperiod" class="w_70">
                    <option value="1"><?php echo get_string('siteadmin_use', 'local_lmsdata'); ?></option>
                    <option value="0" <?php if($board->allowperiod == 0){ echo 'selected'; } ?>><?php echo get_string('siteadmin_nouse', 'local_lmsdata'); ?></option>
                </select>
            </td>
        </tr>
    </tbody>

</table>

<div class="btn_area">
    <input type="submit" id="add_user" class="blue_btn" value="<?php echo get_string('board_save', 'local_lmsdata'); ?>" />
    <input type="button" id="notice_list" class="normal_btn" value="<?php echo get_string('board_list', 'local_lmsdata'); ?>" onclick="location.href='list.php'" />
</div> <!-- Bottom Button Area -->

 </form>
    </div>
</div>
<?php include_once('../inc/footer.php');?>
<script type="text/javascript">
  $(function() {
    $("input:radio[name=noticescore]").each(function() {
        $(this).click(function(){
            noticescore_changed($(this).val());
            objection_changed($("input:radio[name=objection]:checked").val());
        });
    });
    $("input:radio[name=objection]").each(function() {
        $(this).click(function(){
            objection_changed($(this).val());
        });
    });
  });
</script>
