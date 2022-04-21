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
?>
<?php include_once('../inc/header.php'); ?>
<div id="contents">
    <?php include_once('../inc/sidebar_board.php'); ?>
    <div id="content">
        <h3 class="page_title"><?php echo get_string('board_management', 'local_lmsdata'); ?></h3>
        <div class="page_navbar"><a href="<?php echo $CFG->wwwroot.'/siteadmin/board/list.php';?>"><?php echo get_string('board_management', 'local_lmsdata'); ?></a> > <a href="#"><?php echo get_string('board_boardsettings', 'local_lmsdata'); ?></a></div>
        <div class="siteadmin_tabs">
            <a href="<?php echo $CFG->wwwroot; ?>/siteadmin/board/modify.php?id=<?php echo $id; ?>"><p class="black_btn "><?php echo get_string('board_boardInformation', 'local_lmsdata'); ?></p></a>
            <a href="<?php echo $CFG->wwwroot; ?>/siteadmin/board/category.php?id=<?php echo $id; ?>"><p class="black_btn black_btn_selected "><?php echo get_string('board_category', 'local_lmsdata'); ?></p></a>
            <a href="<?php echo $CFG->wwwroot; ?>/siteadmin/board/allow.php?id=<?php echo $id; ?>"><p class="black_btn "><?php echo get_string('board_setpermissions', 'local_lmsdata'); ?></p></a>
        </div>
        <div>
        </div>
        <div id="category_content_body">
            <?php 
                $categories = $DB->get_records('jinoboard_category',array('board'=>$id),'sortorder asc');
                $cnt = 1;
                foreach($categories as $category){
            ?>
            <form id="frm_popup_submit" class="popup_submit" enctype="multipart/form-data" action="./category_modify.php?id=<?php echo $id; ?>" method="POST">
                <input type="hidden" value="<?php echo $category->id; ?>" name="cid">
            <div id="category_form">
                <fieldset style="clear: both;">
                    <legend><h2><?php echo get_string('board_category', 'local_lmsdata'); ?> <?php echo $cnt++; ?></h2></legend>
                    <div>
                        <table cellpadding="0" cellspacing="0" class="detail">
                            <tbody>
                                <tr>
                                    <td class="field_title"><?php echo get_string('board_categorynameko', 'local_lmsdata'); ?></td>
                                    <td class="field_value">
                                        <input type="text" class="w_300" name ="name" value="<?php echo $category->name; ?>"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_title"><?php echo get_string('board_categorynameen', 'local_lmsdata'); ?></td>
                                    <td class="field_value">
                                        <input type="text" class="w_300" name ="engname" value="<?php echo $category->engname; ?>" />
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_title"><?php echo get_string('siteadmin_act', 'local_lmsdata'); ?></td>
                                    <td class="field_value">
                                        <select name="isused" class="w_70">
                                            <option value="1"><?php echo get_string('siteadmin_act', 'local_lmsdata'); ?></option>
                                            <option value="2"><?php echo get_string('siteadmin_noact', 'local_lmsdata'); ?></option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_title"><?php echo get_string('board_categoryorder', 'local_lmsdata'); ?></td>
                                    <td class="field_value">
                                        <input type="text" class="w_150" name ="sortorder" value="<?php echo $category->sortorder; ?>" />
                                    </td>
                                </tr>
                        </table>
                    </div>
                <input type="submit" id="add_user" class="blue_btn" value="<?php echo get_string('updates', 'local_lmsdata'); ?>" style="float: right;" />

                </fieldset>
            </div>
        </form>
            <?php } ?>
        </div>
        <form id="frm_popup_submit" class="popup_submit" enctype="multipart/form-data" action="./category_submit.php?id=<?php echo $id; ?>" method="POST">
            <div id="category_form">
                <fieldset style="clear: both;">
                    <legend><h2><?php echo get_string('board_categoryregist', 'local_lmsdata'); ?></h2></legend>
                    <div>
                        <table cellpadding="0" cellspacing="0" class="detail">
                            <tbody>
                                <tr>
                                    <td class="field_title"><?php echo get_string('board_categorynameko', 'local_lmsdata'); ?></td>
                                    <td class="field_value">
                                        <input type="text" class="w_300" name ="name" value=""/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_title"><?php echo get_string('board_categorynameen', 'local_lmsdata'); ?></td>
                                    <td class="field_value">
                                        <input type="text" class="w_300" name ="engname" value="" />
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_title"><?php echo get_string('siteadmin_act', 'local_lmsdata'); ?></td>
                                    <td class="field_value">
                                        <select name="isused" class="w_70">
                                            <option value="1"><?php echo get_string('siteadmin_act', 'local_lmsdata'); ?></option>
                                            <option value="2"><?php echo get_string('siteadmin_noact', 'local_lmsdata'); ?></option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_title"><?php echo get_string('board_categoryorder', 'local_lmsdata'); ?></td>
                                    <td class="field_value">
                                        <input type="text" class="w_150" name ="sortorder" value="" />
                                        <span style="color:#666;">ex ) 1</span>
                                    </td>
                                </tr>
                        </table>
                    </div>
                </fieldset>
            </div>
            <div></div>
            <div class="btn_area">
                <input type="submit" id="add_user" class="blue_btn" value="<?php echo get_string('board_categoryregist', 'local_lmsdata'); ?>"  />
                <input type="button" id="notice_list" class="normal_btn" value="<?php echo get_string('board_list', 'local_lmsdata'); ?>"  onclick="location.href = 'list.php'" />
            </div> <!-- Bottom Button Area -->

        </form>
    </div>
</div>
<?php include_once('../inc/footer.php'); ?>
<script type="text/javascript">
    $(function () {
        $("input:radio[name=noticescore]").each(function () {
            $(this).click(function () {
                noticescore_changed($(this).val());
                objection_changed($("input:radio[name=objection]:checked").val());
            });
        });
        $("input:radio[name=objection]").each(function () {
            $(this).click(function () {
                objection_changed($(this).val());
            });
        });
    });
</script>
