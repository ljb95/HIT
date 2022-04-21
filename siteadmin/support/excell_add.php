<?php
require(dirname(dirname(dirname(__FILE__))) . '/config.php');

?>
<link rel="stylesheet" type="text/css" href="./styles/common.css" />
<script>
   function load_con_form(frm, url) {

        frm.load(url, function () {

            var $load = $(this);

            //파일선택시 선택한 폼 로드
            $(this).find('input:radio[name=file_type]').click(function () {
                var fl_num = $(this).val();
                for (var i = 1; i <= 2; i++) {
                    $load.find('#file_type' + i).addClass('display_none');
                }
                $load.find('#file_type' + fl_num).removeClass('display_none');
            });

        });
    }

</script>
<?php
// Check for valid admin user - no guest autologin
require_login(0, false);
if (isguestuser()) {
    $SESSION->wantsurl = (string)new moodle_url('/siteadmin/support/notices_write.php');
    redirect(get_login_url());
}
$context = context_system::instance();
require_capability('moodle/site:config', $context);

    require_once($CFG->libdir . '/filestorage/file_storage.php');
    require_once($CFG->libdir . '/filestorage/stored_file.php');
    require_once($CFG->libdir . '/filelib.php');

    $type = optional_param('type', 'excell', PARAM_RAW);
    $page = optional_param('page', 1, PARAM_INT);
    $search = optional_param('search', '', PARAM_RAW);
    $perpage = optional_param('perpage', 10, PARAM_INT);

    $board = $DB->get_record('jinoboard', array('type' => $type));
    $context = context_system::instance();        
    $nav = array('top'=>'site', 'left'=>'board', 'sub'=>'notice');;
    
    $id = optional_param("id", 0, PARAM_INT);
    $mod = optional_param("mod", "", PARAM_TEXT);
    
    
    $js = array(
    '../js/ckeditor-4.3/ckeditor.js',
    '../js/ckfinder-2.4/ckfinder.js'
);
?>
<?php include_once('../inc/header.php');?>
<div id="contents">
    <?php include_once('../inc/sidebar_support.php');?> 
    <div id="content">
    <h3 class="page_title"><?php echo get_string('contents_packageregistration', 'local_lmsdata'); ?></h3>
    <div class="page_navbar"><a href="<?php echo $CFG->wwwroot . '/siteadmin/support/popup.php'; ?>"><?php echo get_string('site_management', 'local_lmsdata'); ?></a> > <a href="#"><?php echo get_string('contents_packageregistration', 'local_lmsdata'); ?></a></div>
    <form id="frm_popup_submit" class="popup_submit" enctype="multipart/form-data" action="<?php echo './add_excell_submit.php?mod=edit&id='.$id.'&type='.$type; ?>" method="POST">
<table cellpadding="0" cellspacing="0" class="detail">
    <tbody>
        <tr>
            <td class="field_title"><?php echo get_string('contents_attachments', 'local_lmsdata'); ?></td>
            <td class="field_value number" id="con_form">
                <script type="text/javascript">
                    <?php if(empty($type)){ $type = "excell"; } ?>
                    load_con_form($('#con_form'), './html/write_<?php echo $type; ?>.php?<?php if($id){ echo 'seq='.$id; ?>&data_dir=<?php echo (isset($data))?$data['DATA_DIR']:""; } ?>');
                </script>
            </td>
        </tr>
    </tbody>

</table>

<div class="btn_area">
     <input type="submit" id="add_user" class="blue_btn" value="<?php echo get_string('board_save', 'local_lmsdata'); ?>"  />
    <input type="button" id="notice_list" class="normal_btn" value="<?php echo get_string('board_list', 'local_lmsdata'); ?>"  />
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
