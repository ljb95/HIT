<?php
require(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once(dirname(dirname(__FILE__)) . '/lib.php');
require_once (dirname(dirname(dirname(__FILE__))) . '/lib/form/editor.php');

$context = context_system::instance();
$PAGE->set_context($context);

$page = optional_param('page', 0, PARAM_INT);     // which page to show
$searchfield = optional_param('searchfield', '', PARAM_CLEAN); // search string
$searchvalue = optional_param('searchvalue', '', PARAM_CLEAN); // search string
$perpage = optional_param('perpage', 15, PARAM_INT);    //한페이지에 보이는 글의 수
$pagerange = optional_param('pagerange', 10, PARAM_INT);  //하단에 리스트에 보이는 페이지수
$mod = optional_param("mode", "", PARAM_TEXT);
$id = optional_param("id", 0, PARAM_INT);

$temp = new stdclass();

if (!empty($id) && $mod === 'edit') {
    $temp = $DB->get_record('popup', array('id' => $id));
}
require_once dirname(dirname(__FILE__)) . '/lib/paging.php';
require_once $CFG->dirroot . '/local/jinoboard/lib.php';
require_once($CFG->libdir . '/filestorage/file_storage.php');
require_once($CFG->libdir . '/filestorage/stored_file.php');
require_once($CFG->libdir . '/filelib.php');
$js = array(
    '../js/ckeditor-4.3/ckeditor.js',
    '../js/ckfinder-2.4/ckfinder.js'
);
?>
<?php include_once (dirname(dirname(__FILE__)) . '/inc/header.php'); ?>
<div id="contents">
    <?php include_once ('../inc/sidebar_support.php'); ?>

    <div id="content">
        <h3 class="page_title"><?php echo get_string('popup_manage', 'local_lmsdata'); ?></h3>
        <div class="page_navbar"><a href="./notices.php"><?php echo get_string('site_management', 'local_lmsdata'); ?></a> > <a href="./popup.php"><?php echo get_string('popup_manage','local_lmsdata'); ?></a> > <?php echo get_string('list','local_lmsdata'); ?></div>
        <div class="frm_popup">
            <h2 style="float:left;"><?php echo get_string('popup_we', 'local_lmsdata'); ?></h2>
            <p class="popup"><?php echo get_string('reqiured', 'local_lmsdata'); ?></p>
            <?php if (!empty($id) && $mod === 'edit') { ?>
                <form id="frm_popup_submit" class="popup_submit" ENCTYPE='multipart/form-data'  action="<?php echo '../support/popup_submit.php?mod=edit&id=' . $id; ?>" method="POST">
                <?php } else { ?>
                    <form id="frm_popup_submit" class="popup_submit" action="../support/popup_submit.php" method="POST">
                    <?php } ?>
                    <table cellspadding="0" cellspacing="0" class="detail">
                        <tr>
                            <td class="field_title"><font color="#F00A0D"  size="3px;"><strong>*</strong></font><?php echo get_string('popup', 'local_lmsdata'); ?></td>
                            <td class="field_value"><input type="text" title="title" id="title" value="<?php echo (!empty($temp->title)) ? $temp->title : ""; ?>" name="title" class="w_100"/></td>
                        </tr>
                        <tr>
                            <td class="field_title"><font color="#F00A0D"  size="3px;"><strong>*</strong></font><?php echo get_string('post_period', 'local_lmsdata'); ?></td>
                            <td class="field_value"><input type="text" id="timeavailable" title="time" name="timeavailable" style="margin-left: 6px; float:left" value="<?php
                                if (!empty($temp->timeavailable)) {
                                    echo date("Y-m-d", $temp->timeavailable);
                                } else {
                                    echo date("Y-m-d", time());
                                }
                                ?>"/>
                                <span> ~ </span>&nbsp;&nbsp;<input type="text" id="timedue" title="time" name="timedue" value="<?php
                                if (!empty($temp->timedue)) {
                                    echo date("Y-m-d", $temp->timedue);
                                } else {
                                    echo date("Y-m-d", time() + 604800);
                                }
                                ?>"/></td>
                        </tr>
                        <tr>
                            <td class="field_title"><font color="#F00A0D"  size="3px;"><strong>*</strong></font><?php echo get_string('position', 'local_lmsdata'); ?></td>
                            <td class="field_value">
                                <label class="radio"><input type="radio" title="login" name="type" value="1" <?php if (isset($temp->type) && $temp->type == 1) echo "checked" ?>  /><?php echo get_string('support_login', 'local_lmsdata'); ?></label>
                                <label class="radio"><input type="radio" title="my" name="type" value="2" <?php if (isset($temp->type) && $temp->type == 2) echo "checked" ?>/><?php echo get_string('support_mylmsdata', 'local_lmsdata'); ?></label>
                            </td>
                        </tr>
                        <tr>
                            <td class="field_title"><font color="#F00A0D"  size="3px;"><strong>*</strong></font><?php echo get_string('contents', 'local_lmsdata'); ?></td>
                            <td class="field_value">
                                <?php
                                $editor = new MoodleQuickForm_editor('editor', get_string('contents', 'local_lmsdata'), array('id' => 'editor'), array('maxfiles' => EDITOR_UNLIMITED_FILES, 'maxbytes' => 1000000000, 'trusttext' => true, 'return_types' => FILE_INTERNAL | FILE_EXTERNAL));
                                if (isset($temp->description)) {
                                    $editor->setValue(array('text' => file_rewrite_pluginfile_urls($temp->description, 'pluginfile.php', $context->id, 'local_popup', 'popup', $temp->id), 'format' => editors_get_preferred_format(), 'itemid' => file_get_submitted_draft_itemid('temp')));
                                }
                                echo $editor->toHtml();
                                ?>

                            </td>
                        </tr>
                        <tr>
                            <td class="field_title"><?php echo get_string('xsize', 'local_lmsdata'); ?></td>
                            <td class="field_value"><input type="text" title="width" name ="popupwidth" value="<?php
                                if (!empty($temp->popupwidth) || $temp->popupwidth == 0) {
                                    echo $temp->popupwidth;
                                } else {
                                    echo "370";
                                }
                                ?>" class="w_160" /><span class="select">px</span></td>
                        </tr>
                        <tr>
                            <td class="field_title"><?php echo get_string('ysize', 'local_lmsdata'); ?></td>
                            <td class="field_value"><input type="text" title="height" name="popupheight" value="<?php
                                if (!empty($temp->popupheight) || $temp->popupheight==0) {
                                    echo $temp->popupheight;
                                } else {
                                    echo "370";
                                }
                                ?>" class="w_160" /><span class="select">px</span></td>
                        </tr>
                        <tr>
                            <td class="field_title"><?php echo get_string('xposition', 'local_lmsdata'); ?></td>
                            <td class="field_value"><input type="text" title="x" name="popupx" value="<?php
                                if (!empty($temp->popupx) || $temp->popupx==0) {
                                    echo $temp->popupx;
                                } else {
                                    echo "0";
                                }
                                ?>" class="w_160" /><span class="select">px</span></td>
                        </tr>
                        <tr>
                            <td class="field_title"><?php echo get_string('yposition', 'local_lmsdata'); ?></td>
                            <td class="field_value"><input type="text" title="y" name="popupy" value="<?php
                                if (!empty($temp->popupy) || $temp->popupy == 0) {
                                    echo $temp->popupy;
                                } else {
                                    echo "230";
                                }
                                ?>" class="w_160" /><span class="select">px</span></td>
                        </tr>
                        <tr>
                            <td class="field_title"><?php echo get_string('viewscroll', 'local_lmsdata'); ?></td>
                            <td class="field_value" colspan="3">
                                <input type="checkbox" title="availablescroll" name="availablescroll" value="1" <?php if (!empty($temp->availablescroll)) echo "checked"; ?> hclgass="w_160" />
                            </td>
                        </tr>
                    </table>
                    <div id="btn_area">
                        <input type="button" id="popup_submit"  class="blue_btn" value="<?php echo get_string('save', 'local_lmsdata'); ?>" style="float:right" /> 
                        <input type="button" id="popup_list" class="normal_btn" value="<?php echo get_string('list2', 'local_lmsdata'); ?>" style="float:left;" />
                    </div><!--Btn Area End-->
                </form>
        </div><!--Form Popup End-->
    </div><!--Content End-->
</div> <!--Contents End-->

<?php
include_once ('../inc/footer.php');
?>


<script type="text/javascript">

    var editor = CKEDITOR.replace('editor', {
        language : '<?php echo current_language(); ?>',
        filebrowserBrowseUrl: '../js/ckfinder-2.4/ckfinder.html',
        filebrowserImageBrowseUrl: '../js/ckfinder-2.4/ckfinder.html?type=Images',
        filebrowserFlashBrowseUrl: '../js/ckfinder-2.4/ckfinder.html?type=Flash',
        filebrowserUploadUrl: '../js/ckfinder-2.4/core/connector/php/connector.php?command=QuickUpload&type=Files',
        filebrowserImageUploadUrl: '../js/ckfinder-2.4/core/connector/php/connector.php?command=QuickUpload&type=Images',
        filebrowserFlashUploadUrl: '../js/ckfinder-2.4/core/connector/php/connector.php?command=QuickUpload&type=Flash'
    });
    CKFinder.setupCKEditor(editor, '../');
    $("#timeavailable").datepicker({
        dateFormat: "yy-mm-dd",
        onClose: function (selectedDate) {
            $("#timedue").datepicker("option", "minDate", selectedDate);
        }
    });
    $("#timedue").datepicker({
        dateFormat: "yy-mm-dd",
        onClose: function (selectedDate) {
            $("#timeavailable").datepicker("option", "maxDate", selectedDate);
        }
    });
    $(document).ready(function () {
        $('#popup_list').click(function () {
            location.href = "./popup.php";
        });
        $('#popup_submit').click(function () {
            $('#frm_popup_submit').submit();
        });
        $('#frm_popup_submit').submit(function (event) {
            var title = $("#title").val();
            if (title.trim() == '') {
                alert("제목을 입력해 주세요");
                return false;
            };
            if( $("input:radio[name='type']").is(":checked") == false){
                alert("게시위치를 클릭 주세요");
                return false;
            };
            var content = CKEDITOR.instances.editor.getData(); 
            if (content.trim() == '') {
                alert("내용을 입력해 주세요");
                return false;
            };
            
        });
    });
</script>
