<?php
require(dirname(dirname(dirname(__FILE__))) . '/config.php');

?>
<link rel="stylesheet" type="text/css" href="./styles/common.css" />
<?php
// Check for valid admin user - no guest autologin
require_login(0, false);
if (isguestuser()) {
    $SESSION->wantsurl = (string)new moodle_url('/siteadmin/board/list.php');
    redirect(get_login_url());
}
$context = context_system::instance();
require_capability('moodle/site:config', $context);

    require_once dirname(dirname(__FILE__)) . '/lib/paging.php';    
    require_once $CFG->dirroot . '/local/jinoboard/lib.php';
    require_once($CFG->libdir . '/filestorage/file_storage.php');
    require_once($CFG->libdir . '/filestorage/stored_file.php');
    require_once($CFG->libdir . '/filelib.php');

    $context = context_system::instance();        
    
    $id = optional_param("id", 0, PARAM_INT);
    $mod = optional_param("mod", "", PARAM_TEXT);
    

?>
<?php include_once('../inc/header.php');?>
<div id="contents">
    <?php include_once('../inc/sidebar_board.php');?>
    <div id="content">
    <h3 class="page_title"><?php echo get_string('siteadmin_boardmanagement', 'local_lmsdata'); ?></h3>
    <div class="page_navbar"><a href="<?php echo $CFG->wwwroot.'/siteadmin/board/list.php';?>"><?php echo get_string('board_management', 'local_lmsdata'); ?></a> > <a href="#">게시판 등록</a></div>
    <form id="frm_popup_submit" class="popup_submit" enctype="multipart/form-data" action="./add_submit.php" method="POST">
<table cellpadding="0" cellspacing="0" class="detail">

    <tbody>
        <tr>
            <td class="field_title"><?php echo get_string('siteadmin_boardnameko', 'local_lmsdata'); ?></td>
            <td class="field_value">
                <input type="text" title="name" class="w_300" name ="name" value="<?php echo (!empty($temp->title))?$temp->title:""; ?>"/>
            </td>
        </tr>
        <tr>
            <td class="field_title"><?php echo get_string('siteadmin_boardnameen', 'local_lmsdata'); ?></td>
            <td class="field_value">
                <input type="text"  title="engname" class="w_300" name ="engname" value="<?php echo (!empty($temp->title))?$temp->title:""; ?>"/>
            </td>
        </tr>
        <tr>
            <td class="field_title"><?php echo get_string('siteadmin_location', 'local_lmsdata'); ?></td>
            <td class="field_value">
                <select title="type" name="type" class="w_120">
                    <option value="5"><?php echo get_string('siteadmin_mainmenu', 'local_lmsdata'); ?></option>
                    <option value="6"><?php echo get_string('siteadmin_mypage', 'local_lmsdata'); ?></option>
                </select>
            </td>
        </tr>
        <tr>
            <td class="field_title"><?php echo get_string('siteadmin_actyn', 'local_lmsdata'); ?></td>
            <td class="field_value">
                <select name="status" title="status" class="w_70">
                    <option value="1"><?php echo get_string('siteadmin_act', 'local_lmsdata'); ?></option>
                    <option value="2"><?php echo get_string('siteadmin_noact', 'local_lmsdata'); ?></option>
                </select>
            </td>
        </tr>
        <tr>
            <td class="field_title"><?php echo get_string('siteadmin_filemax', 'local_lmsdata'); ?></td>
            <td class="field_value">
                <select name="maxbytes" title="maxbyte" class="w_90">
                    <option value="0"><?php echo get_string('siteadmin_nolimit', 'local_lmsdata'); ?></option>
                    <option value="1024">1M</option>
                    <option value="10240">10M</option>
                    <option value="102400">100M</option>
                    <option value="512000">500M</option>
                    <option value="1048576">1G</option>
                </select>
            </td>
        </tr>
        <tr>
            <td class="field_title"><?php echo get_string('siteadmin_filemaxcount', 'local_lmsdata'); ?></td>
            <td class="field_value">
                <select name="maxattachments"  title="maxattachments"class="w_90">
                    <option value="0"><?php echo get_string('siteadmin_nolimit', 'local_lmsdata'); ?></option>
                    <option value="1">1</option>
                    <option value="3">3</option>
                    <option value="5">5</option>
                    <option value="10">10</option>
                </select>
            </td>
        </tr>
        <tr>
            <td class="field_title"><?php echo get_string('siteadmin_newcontent', 'local_lmsdata'); ?></td>
            <td class="field_value">
                <select name="allownew" title="new" class="w_70">
                    <option value="1"><?php echo get_string('siteadmin_use', 'local_lmsdata'); ?></option>
                    <option value="0"><?php echo get_string('siteadmin_nouse', 'local_lmsdata'); ?></option>
                </select>
                <input type="number" name="newday" title="newday" class="w_50" size="3" value="3" maxlength="3"> <?php echo get_string('contents_day', 'local_lmsdata'); ?>
            </td>
        </tr>
        <tr>
            <td class="field_title"><?php echo get_string('siteadmin_notice', 'local_lmsdata'); ?></td>
            <td class="field_value">
                <select name="allownotice" title="notice" class="w_70">
                    <option value="1"><?php echo get_string('siteadmin_use', 'local_lmsdata'); ?></option>
                    <option value="0"><?php echo get_string('siteadmin_nouse', 'local_lmsdata'); ?></option>
                </select>
            </td>
        </tr>
        <tr>
            <td class="field_title"><?php echo get_string('siteadmin_reply', 'local_lmsdata'); ?></td>
            <td class="field_value">
                <select name="allowreply" title="reply" class="w_70">
                    <option value="1"><?php echo get_string('siteadmin_use', 'local_lmsdata'); ?></option>
                    <option value="0"><?php echo get_string('siteadmin_nouse', 'local_lmsdata'); ?></option>
                </select>
            </td>
        </tr>
        <tr>
            <td class="field_title"><?php echo get_string('siteadmin_coment', 'local_lmsdata'); ?></td>
            <td class="field_value">
                <select name="allowcomment" title="comment" class="w_70">
                    <option value="1"><?php echo get_string('siteadmin_use', 'local_lmsdata'); ?></option>
                    <option value="0"><?php echo get_string('siteadmin_nouse', 'local_lmsdata'); ?></option>
                </select>
            </td>
        </tr>
        <tr>
            <td class="field_title"><?php echo get_string('siteadmin_mediarental', 'local_lmsdata'); ?></td>
            <td class="field_value">
                <select name="allowrental" title="rental" class="w_70">
                    <option value="1"><?php echo get_string('siteadmin_use', 'local_lmsdata'); ?></option>
                    <option value="0" selected><?php echo get_string('siteadmin_nouse', 'local_lmsdata'); ?></option>
                </select>
            </td>
        </tr>
        <tr>
            <td class="field_title"><?php echo get_string('siteadmin_fileyn', 'local_lmsdata'); ?></td>
            <td class="field_value">
                <select name="allowupload" title="upload" class="w_70">
                    <option value="1"><?php echo get_string('siteadmin_use', 'local_lmsdata'); ?></option>
                    <option value="0"><?php echo get_string('siteadmin_nouse', 'local_lmsdata'); ?></option>
                </select>
            </td>
        </tr>
        <tr>
            <td class="field_title"><?php echo get_string('siteadmin_secret', 'local_lmsdata'); ?></td>
            <td class="field_value">
                <select name="allowsecret" title="secret" class="w_70">
                    <option value="1"><?php echo get_string('siteadmin_use', 'local_lmsdata'); ?></option>
                    <option value="0"><?php echo get_string('siteadmin_nouse', 'local_lmsdata'); ?></option>
                </select>
            </td>
        </tr>
        <tr>
            <td class="field_title"><?php echo get_string('siteadmin_category', 'local_lmsdata'); ?></td>
            <td class="field_value">
                <select name="allowcategory" title="category" class="w_70">
                    <option value="1"><?php echo get_string('siteadmin_use', 'local_lmsdata'); ?></option>
                    <option value="0"><?php echo get_string('siteadmin_nouse', 'local_lmsdata'); ?></option>
                </select>
            </td>
        </tr>
        <tr>
            <td class="field_title"><?php echo get_string('siteadmin_publicationperiod', 'local_lmsdata'); ?></td>
            <td class="field_value">
                <select name="allowperiod" title="period" class="w_70">
                    <option value="1"><?php echo get_string('siteadmin_use', 'local_lmsdata'); ?></option>
                    <option value="0"><?php echo get_string('siteadmin_nouse', 'local_lmsdata'); ?></option>
                </select>
            </td>
        </tr>
    </tbody>

</table>

<div class="btn_area">
    <input type="submit" id="add_user" class="blue_btn" value="<?php echo get_string('board_save', 'local_lmsdata'); ?>" />
    <input type="button" id="notice_list" class="normal_btn" value="<?php echo get_string('board_list', 'local_lmsdata'); ?>"/>
</div> <!-- Bottom Button Area -->

 </form>
    </div>
</div>
<?php include_once('../inc/footer.php');?>


<script type="text/javascript">
   var editor = CKEDITOR.replace( 'editor', {
        language : '<?php echo current_language(); ?>',
	filebrowserBrowseUrl : '../js/ckfinder-2.4/ckfinder.html',
	filebrowserImageBrowseUrl : '../js/ckfinder-2.4/ckfinder.html?type=Images',
	filebrowserFlashBrowseUrl : '../js/ckfinder-2.4/ckfinder.html?type=Flash',
	filebrowserUploadUrl : '../js/ckfinder-2.4/core/connector/php/connector.php?command=QuickUpload&type=Files',
	filebrowserImageUploadUrl : '../js/ckfinder-2.4/core/connector/php/connector.php?command=QuickUpload&type=Images',
	filebrowserFlashUploadUrl : '../js/ckfinder-2.4/core/connector/php/connector.php?command=QuickUpload&type=Flash'
        });
        CKFinder.setupCKEditor( editor, '../' );
        
        
$(document).ready(function () { 
    
 });
   function remove_file(){

        $("a[name='file_link']").remove();
        $("input[name='remove_button']").remove();
        $("input[name='file_del']").val(1);
        
    }
    
</script>
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
