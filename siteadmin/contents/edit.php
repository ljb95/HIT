<?php
require(dirname(dirname(dirname(__FILE__))) . '/config.php');

?>
<link rel="stylesheet" type="text/css" href="./styles/common.css" />
<script>
          function cc_mark_change(val){
    switch(val){
        case 1: 
            $('#cc_type1').show();
            $('#cc_type2').hide();
            $('#cc_type3').hide();
            break;
        case 2: 
            $('#cc_type1').hide();
            $('#cc_type2').show();
            $('#cc_type3').hide();
            break;
        case 3: 
            $('#cc_type1').hide();
            $('#cc_type2').hide();
            $('#cc_type3').show();
            break;
    }
}
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

    $context = context_system::instance();        
    $nav = array('top'=>'site', 'left'=>'board', 'sub'=>'notice');;
    
    $id = optional_param("id", 0, PARAM_INT);
    $ref = optional_param("ref", 0, PARAM_INT);
    $mod = optional_param("mode", "edit", PARAM_TEXT);
    
    $temp = new stdclass();
    
 if (!$ref) {
    $sql = "select "
            . "rep.id , rep.referencecnt , "
            . "con.id as con_id,con.cc_type,con.cc_mark,con.author,con.teacher,con.con_name,con.con_type,con.con_des,con.update_dt,con.share_yn,con.data_dir,con.embed_type,con.embed_code, con.user_no, "
            . "rep_group.name as gname "
            . "from {lcms_repository} rep "
            . "join {lcms_contents} con on con.id= rep.lcmsid "
            . "left join {lcms_repository_groups} rep_group on rep_group.id = rep.groupid "
            . "where rep.id= :id";
} else {
    $sql = "select "
            . "rep.id , "
            . "con.id as con_id,con.con_name,con.cc_type,con.cc_mark,con.author,con.teacher,con.con_type,con.con_des,con.update_dt,con.share_yn,con.data_dir,con.embed_type,con.embed_code, con.user_no,"
            . "rep_group.name as gname "
            . "from {lcms_repository_reference} rep "
            . "join {lcms_contents} con on con.id= rep.lcmsid "
            . "left join {lcms_repository_groups} rep_group on rep_group.id = rep.groupid "
            . "where rep.id= :id";
}

$data = $DB->get_record_sql($sql, array('id' => $id)); 
$type = $data->con_type;
    $js = array(
    '../js/ckeditor-4.3/ckeditor.js',
    '../js/ckfinder-2.4/ckfinder.js'
);
?>
<?php include_once('../inc/header.php');?>
<div id="contents">
    <?php include_once('../inc/sidebar_contents.php');?>
    <div id="content">
    <h3 class="page_title">파일수정</h3>
    <div class="page_navbar"><a href="index.php"><?php echo get_string('lcms_management', 'local_lmsdata'); ?></a> > <a href="./notices.php"><?php echo get_string('contents_registration', 'local_lmsdata'); ?></a></div>
    <form id="frm_popup_submit" class="popup_submit" enctype="multipart/form-data" action="<?php echo './add_submit.php?mode=edit&id='.$id.'&type='.$type; ?>" method="POST">
<table cellpadding="0" cellspacing="0" class="detail">

    <tbody>
        <tr>
            <td class="field_title"><?php echo get_string('title', 'local_lmsdata'); ?></td>
            <td class="field_value">
                <input type="text" class="w_300" name ="con_name" value="<?php echo (!empty($data->con_name))?$data->con_name:""; ?>"/>
            </td>
        </tr>
        <?php if(!$ref){ 
            $teacher = $DB->get_record('lmsdata_user',array('userid'=>$data->user_no));
            ?>
                <tr>
            <td class="field_title"><?php echo get_string('contents_lecturer', 'local_lmsdata'); ?></td>
            <td class="field_value">
                <input title="담당교수" type="text" name="prof_name" placeholder="<?php echo get_string('prof_search', 'local_lmsdata'); ?>" size="30" disabled  value="<?php echo!empty($teacher->eng_name) ? $teacher->eng_name : ''; ?>"/>
                <input type="hidden" name="prof_userid" value="<?php echo!empty($teacher->userid) ? $teacher->userid : $USER->id; ?>"/>
                <input type="button" value="<?php echo get_string('search', 'local_lmsdata'); ?>" class="gray_btn" onclick="search_prof_popup()"/>
            </td>
        </tr>
        <tr>
            <td class="field_title"><?php echo get_string('contents_showauthor', 'local_lmsdata'); ?></td>
            <td class="field_value">
                <input type="radio" <?php if($data->cc_type == 1){ echo 'checked'; } ?> onclick="cc_mark_change(1);" name="cc_type" value="1"> <?php echo get_string('contents_nocopyright', 'local_lmsdata'); ?>
                <input type="radio" <?php if($data->cc_type == 2){ echo 'checked'; } ?> name="cc_type" onclick="cc_mark_change(2);" value="2"> <?php echo get_string('contents_creativecommons', 'local_lmsdata'); ?>
                <input type="radio" <?php if($data->cc_type == 3){ echo 'checked'; } ?> name="cc_type" onclick="cc_mark_change(3);" value="3"> <?php echo get_string('contents_directinput', 'local_lmsdata'); ?>
                <div id="cc_type1" class="box_gray <?php if($data->cc_type == 2 || $data->cc_type == 3){ echo 'display_none'; } ?>">
                - <?php echo get_string('contents_nocopyright', 'local_lmsdata'); ?>
                </div>
                <div id="cc_type2" class="box_gray <?php if($data->cc_type == 1 || $data->cc_type == 3){ echo 'display_none'; } ?>">
                <?php echo get_string('contents_writer', 'local_lmsdata'); ?> : 
                <input type="text" name="author"  size="30" maxlength="30" value="<?php echo $data->author?>"/> <span class="gray_text"> * <?php echo get_string('contents_30characters', 'local_lmsdata'); ?></span><br/>
                <select name="cc_mark">
                    <option <?php if($data->cc_mark == 'CC BY'){ echo 'selected'; } ?> value="CC BY"><?php echo get_string('contents_copyright1', 'local_lmsdata'); ?> </option>
                    <option <?php if($data->cc_mark == 'CC BY-NC'){ echo 'selected'; } ?> value="CC BY-NC"><?php echo get_string('contents_copyright2', 'local_lmsdata'); ?> </option>
                    <option <?php if($data->cc_mark == 'CC BY-ND'){ echo 'selected'; } ?> value="CC BY-ND"><?php echo get_string('contents_copyright3', 'local_lmsdata'); ?> </option>
                    <option <?php if($data->cc_mark == 'CC BY-SA'){ echo 'selected'; } ?> value="CC BY-SA"><?php echo get_string('contents_copyright4', 'local_lmsdata'); ?> </option>
                    <option <?php if($data->cc_mark == 'CC BY-NC-SA'){ echo 'selected'; } ?> value="CC BY-NC-SA"><?php echo get_string('contents_copyright5', 'local_lmsdata'); ?> </option>
                    <option <?php if($data->cc_mark == 'CC BY-NC-ND'){ echo 'selected'; } ?> value="CC BY-NC-ND"><?php echo get_string('contents_copyright6', 'local_lmsdata'); ?> </option>
                </select>
                </div>
                <?php
                    if($data->cc_type == 3){
                        $ccmark = $data->cc_mark;
                    } else {
                        $ccmark = '';
                    }
                ?>
                <div id="cc_type3" class="box_gray <?php if($data->cc_type == 1 || $data->cc_type == 2){ echo 'display_none'; } ?>">
                    <input type="text" name="cc_text" size="70" maxlength="70" placeholder="카피라이트를 입력하세요." value="<?php echo $ccmark?>"/>
                </div>
            </td>
        </tr>
        <tr>
            <td class="field_title"><?php echo get_string('contents_visibility', 'local_lmsdata'); ?></td>
            <td class="field_value">
                <input type="radio" name="share_yn" <?php if($data->share_yn == 'Y'){ echo 'checked'; } ?> value="Y"> <?php echo get_string('contents_open', 'local_lmsdata'); ?>
                <input type="radio" name="share_yn" <?php if($data->share_yn == 'N'){ echo 'checked'; } ?> value="N"> <?php echo get_string('contents_private', 'local_lmsdata'); ?>
            </td>
        </tr>
        <?php } else { ?>
    <input type="hidden" name="ref" value="<?php echo $ref; ?>" />
        <?php } ?>
            <input type="hidden" name="con_type" value="<?php echo $type; ?>">
        <?php
        switch ($type) {
                    case "word" : $type_txt = get_string('document', 'local_repository');
                        break;
                    case "html" :
                    case "html2" : $type_txt = get_string('html', 'local_repository');
                        break;
                    case "video" : $type_txt = get_string('video', 'local_repository');
                        break;
                    case "embed" : $type_txt = get_string('embed', 'local_repository');
                        break;
                    default : $type_txt = "<?php echo get_string('contents_attachments', 'local_lmsdata'); ?>"; break;
                }
        ?>
        <!--tr>
            <td class="field_title">대상</td>
            <td class="field_value">
                <input type="button" class="red_btn" value="<?php echo get_string('stats_board8', 'local_lmsdata'); ?>">
                <input type="hidden" name="userid">
                <span>선택되지 않음.</span>
            </td>
        </tr-->
        <tr>
            <td class="field_title"><?php echo get_string('contents_fileformat', 'local_lmsdata'); ?></td>
            <td class="field_value">
                <?php echo $type_txt; ?>
            </td>
        </tr>
        <tr>
            <td class="field_title"><?php echo get_string('contents_explanation', 'local_lmsdata'); ?></td>
            <td class="field_value">
                <textarea style="width: 98%" id="editor" name="con_des" ><?php echo (!empty($data->con_des))?$data->con_des:"" ?></textarea>			  	
            </td>
        </tr>
        <tr>
            <td class="field_title" id="rowspan_td" rowspan="1"><?php echo get_string('contents_attachments', 'local_lmsdata'); ?></td>
            <td class="field_value number">
                <input type="hidden" name="stay_file" value="0">
                <input type="checkbox" name="stay_file" onclick="file_change()" value="1">
                <?php echo get_string('filechange', 'local_repository'); ?>
            </td>
        </tr>
        <tr>
            <td class="field_value number" id="con_form" style="display: none;" ></td>
                <script type="text/javascript">
                              <?php if($type == 'ref')$type = 'word'; ?>
        load_con_form($('#con_form'), './html/write_<?php echo $type; ?>.php?con_id=<?php echo $data->con_id?>&id=<?php echo $id; ?>&userid=<?php echo $USER->id; ?>&wwwroot=<?php echo $CFG->wwwroot; ?>');
        function file_change(){
            if($('input[name=stay_file]').is(':checked')){
                $('#rowspan_td').attr('rowspan',2);
                $('#con_form').prop('style','');
            } else {
                $('#rowspan_td').attr('rowspan',1);
                $('#con_form').prop('style','display:none;');
            }
        }
    </script>
        </tr>


    </tbody>

</table>

<div class="btn_area">
     <input type="submit" id="add_user" class="blue_btn" value="<?php echo get_string('board_save', 'local_lmsdata'); ?>"  />
    <input type="button" onclick="location.href='index.php'" class="normal_btn" value="<?php echo get_string('board_list', 'local_lmsdata'); ?>"  />
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
  function search_prof_popup() {
        var tag = $("<div id='course_prof_popup'></div>");
        $.ajax({
            url: '<?php echo $CFG->wwwroot . '/siteadmin/contents/add_teacher.php'; ?>',
            method: 'POST',
            success: function (data) {
                tag.html(data).dialog({
                    title: '<?php echo get_string('prof_search', 'local_lmsdata'); ?>',
                    modal: true,
                    width: 800,
                    resizable: false,
                    height: 400,
                    buttons: [{id: 'close',
                            text: '<?php echo get_string('cancle', 'local_lmsdata'); ?>',
                            disable: true,
                            click: function () {
                                $(this).dialog("close");
                            }}],
                    close: function () {
                        $('#frm_course_prof').remove();
                        $(this).dialog('destroy').remove()
                    }
                }).dialog('open');
            }
        });
    }
</script>
