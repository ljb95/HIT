<?php
require(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once $CFG->dirroot . '/local/repository/config.php';
require_once $CFG->dirroot . '/local/repository/lib.php';
require_once $CFG->dirroot . '/lib/form/filemanager.php';
require_once $CFG->dirroot . '/local/repository/write_form.php';
require_once $CFG->dirroot . '/local/repository/pclzip.lib.php';


ini_set('memory_limit', '4096M'); 
ini_set('max_execution_time', 0);

$id = optional_param('id', 0, PARAM_INT); // Moodle Repository DB id
$mode = optional_param('mode', 'write', PARAM_RAW);
$ref = optional_param('ref', 0, PARAM_INT);

$PAGE->requires->jquery_plugin('ui-css');

$context = context_system::instance();

require_login();

$PAGE->set_context($context);
$PAGE->set_url('/local/repository/write.php', array('mode' => $mode, 'id' => $id));
$PAGE->set_pagelayout('standard');

$strplural = get_string("pluginnameplural", "local_repository");
$PAGE->navbar->add($strplural);
$PAGE->set_title($strplural);
$PAGE->set_heading($strplural);

if (!$ref) {
    $sql = "select "
            . "rep.id , rep.referencecnt , "
            . "con.id as con_id , con.share_yn , con.con_name ,con.con_type,con.data_dir,con.con_des,con.author,con.con_tag ,con.teacher,con.cc_type,con.cc_mark ,con.update_dt, con.embed_type, con.embed_code, "
            . "rep_group.id as gid, rep_group.name as gname "
            . "from {lcms_repository} rep "
            . "join {lcms_contents} con on con.id= rep.lcmsid "
            . "left join {lcms_repository_groups} rep_group on rep_group.id = rep.groupid "
            . "where rep.id= :id";
} else {
    $sql = "select "
            . "rep.id , "
            . "con.id as con_id , con.share_yn , con.con_name ,con.con_type,con.data_dir,con.con_des,con.author,con.con_tag ,con.teacher,con.cc_type,con.cc_mark ,con.update_dt, con.embed_type, con.embed_code, "
            . "rep_group.id as gid, rep_group.name as gname "
            . "from {lcms_repository_reference} rep "
            . "join {lcms_contents} con on con.id= rep.lcmsid "
            . "left join {lcms_repository_groups} rep_group on rep_group.id = rep.groupid "
            . "where rep.id= :id";
}
$file = $DB->get_record_sql($sql, array('id' => $id));

$type_txt = "";
if(isset($file->con_type)){
//?????? ????????? ?????? ??? ????????????
if ($file->con_type == 'video'){
    $type_txt = get_string($file->con_type, 'local_repository');
} else if ($file->con_type == 'embed'){
    $type_txt = get_string('embed', 'local_repository');
} else if ($file->con_type == 'word'){
    $type_txt = get_string('document', 'local_repository');
} else if ($file->con_type == 'html'){
    $type_txt = get_string('html', 'local_repository');
} else if ($file->con_type == 'ref'){
    $type_txt = get_string('reference', 'local_repository');
} else if ($file->con_type == 'media'){
    $type_txt = get_string('mid', 'local_repository');
} else {
    $type_txt = '????????????';
}
$fileinfo = '<span>' . $type_txt . '</span> -> ';
$fileinfo .= ($file->con_type == 'html' || $file->con_type == 'video') ? get_string('fileallchange', 'local_repository') : get_string('filechangeadd', 'local_repository');
$cfiles = $DB->get_records('lcms_contents_file', array('con_seq' => $file->con_id));

$cfilelist = '<ul class="cfiles">';
foreach ($cfiles as $cf) {
    $filename = $cf->fileoname;
    $filesize = filesize($filepath);
    $cfilelist .= '<li>';
    if ($file->con_type == 'word' || $file->con_type == 'ref')
        $cfilelist .= '<button type="button" class="btn btn-delete delete" data-type="DELETE" data-url="' . $cf->id . '">del</button>';
    $cfilelist .= $filename . '</li>';
}

$cfilelist .= '</ul>';
}

/* Form ?????? */

$options = array('noclean' => true, 'subdirs' => true, 'maxfiles' => -1, 'maxbytes' => 0, 'context' => $context);
$mform = new lcms_repository_write_form(null, array('options' => $options,
    'context' => $context
    , 'mode' => $mode)
);

if ($mode == "delete") {
    redirect("index.php");
}
// ?????? ?????? ?????????
if ($mform->is_cancelled()) {
    lcms_temp_dir_filemode('del');
    redirect("index.php");

// ????????? ?????? ?????? ????????? ???????????? = Submit ????????? ???
} else if ($fromform = $mform->get_data()) {
    // ?????? ?????? ??????
    $draftitemid = file_get_submitted_draft_itemid('script');
    if ($mode == "edit") {
        if (!$fromform->stay_file) {
            $fromform->data_dir = $file->data_dir;
            $fromform->embed_type = $file->embed_type;
            $fromform->embed_code = $file->embed_code;
        } else {
            $fromform->data_dir = $file->data_dir;
        }
        $newconid = update_lcms_contents($fromform);
        file_save_draft_area_files($draftitemid, $context->id, 'local_repository', 'subtitle',
                      $newconid, lcms_repository_write_form::attachment_options());
        
        insert_lcms_history($newconid,'Update lcms content in multiple',3);
        
        redirect("detail.php?id=$fromform->id&ref=$fromform->ref");
    } else {     
        $newconid = upload_lcms_contents($fromform, $id);
        insert_lcms_history($newconid,'Insert lcms content in local repository',1);
        file_save_draft_area_files($draftitemid, $context->id, 'local_repository', 'subtitle',
                      $newconid, lcms_repository_write_form::attachment_options());
        if ($mode == "write") {
        $repoid = $DB->get_field('lcms_repository','id',array('lcmsid'=>$newconid));
        redirect("detail.php?id=$repoid");
        }
    }
    echo "<script>location.href='index.php'</script>";
    die();
}
$draftid_editor = file_get_submitted_draft_itemid('con_des');
if(!empty($file)){
$currenttext = file_prepare_draft_area($draftid_editor, $context->id, 'local_repository', 'con_des', $id, lcms_repository_write_form::editor_options($context, $id), $file->con_des);
} else {
    $currenttext = "";
}
if (!empty($file) && $mode == "edit") {
    $attach_editor = file_get_submitted_draft_itemid('script');
    file_prepare_draft_area($attach_editor, $context->id, 'local_repository', 'subtitle', $file->con_id, lcms_repository_write_form::editor_options($context, $id));
    $mform->set_data(array(
        'con_name' => $file->con_name,
        'teacher' => $file->teacher,
        'teacher' => $file->teacher,
        'con_id' => $file->con_id,
        'con_type' => $file->con_type,
        'con_des' => array(
            'text' => $currenttext,
            'format' => 1,
            'itemid' => $draftid_editor,
        ),
        'teacher' => $file->teacher,
        'teacher' => $file->teacher,
        'con_tag' => $file->con_tag,
        'share_yn' => $file->share_yn,
        'cc_type' => $file->cc_type,
        'author' => $file->author,
        'cc_mark' => $file->cc_mark,
        'groupid' => $file->gid,
        'mediaid' => $file->embed_code,
        'fileinfo' => $fileinfo . $cfilelist,
        'ref' => $ref,
        'script' => $attach_editor
    ));
} else if ($mode == "reply") {
    $parent = $DB->get_record('lcms_contents', array('id' => $id));
    $groupid = $DB->get_record('lcms_repository', array('lcmsid' => $parent->id), 'groupid');
    $mform->set_data(array(
        'con_tag' => $parent->con_tag,
        'share_yn' => $parent->share_yn,
        'groupid' => $groupid->groupid,
        'con_type' => 'ref'
    ));
}

?>

<!-- Generic page styles -->
<link rel="stylesheet" href="contents_upload/css/style.css">
<!-- CSS to style the file input field as button and adjust the Bootstrap progress bars -->
<link rel="stylesheet" href="contents_upload/css/jquery.fileupload.css">

<?php

echo $OUTPUT->header();
/* form ?????? */

$mform->display();

/* * ****************  ????????? ????????? ???????????? ???????????? ?????? ?????? ?????????  **************** */
?>
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
<!-- The jQuery UI widget factory, can be omitted if jQuery UI is already included -->
<script src="contents_upload/js/vendor/jquery.ui.widget.js"></script>
<!-- The basic File Upload plugin -->
<script src="contents_upload/js/jquery.fileupload.js"></script>
<script src="jquery.sessionTimeout.js"></script>
<!-- Bootstrap JS is not required, but included for the responsive demo navigation -->
<script src="contents_upload/js/bootstrap.min.js"></script>

<script>
    /*jslint unparam: true */
    /*global window, $ */
    
     var atgroup =  $('#lcms_upload_component').parent().parent();
     var embedgroup = $('#id_emb_type').parent().parent().parent().parent();
     var videoidgroup = $('#video_frame').parent().parent();
     var caption = $('#id_script').parent().parent();
    
    
    $(function () {
        'use strict';
        // Change this to the location of your server-side upload handler:
        var url = window.location.hostname === 'blueimp.github.io' ?
                '//jquery-file-upload.appspot.com/' : 'contents_upload/server/php/';

        $('#fileupload').fileupload({
            wwwroot:'<?php echo $CFG->wwwroot; ?>',
            dirroot:'<?php echo $CFG->dirroot; ?>',
            url: url,
            dataType: 'json',
            done: function (e, data) {
                $.each(data.result.files, function (index, file) {
                    if (file.error) {
                        $('<li/>').html(file.name + '[Error: ' + file.error + ']').appendTo('#files');
                    } else {
                        $('<li/>').html('<button type="button" class="btn btn-delete delete" onclick="delete_file(this)" data-type="DELETE" data-url="' + file.dirroot + '">del</button>' + file.name).appendTo('#files');
                    }
                });
            },
            progress: function (e, data) {

            },
            progressall: function (e, data) {
                var progress = parseInt(data.loaded / data.total * 100, 10);
                $('#progress .progress-bar').css(
                        'width',
                        progress + '%'
                        );
            }
        }).prop('disabled', !$.support.fileInput)
                .parent().addClass($.support.fileInput ? undefined : 'disabled');
        
        <?php if ($mode == "edit") {?>
        var edit_contype = '<?php echo $file->con_type;?>';
        if (edit_contype == 'word' || edit_contype == 'html') {
            videoidgroup.hide();
            embedgroup.hide();
            atgroup.show();
            $('#fitem_id_script').hide();
        } else if (edit_contype == 'video') {
            videoidgroup.show();
            atgroup.hide();
            embedgroup.hide();
            $('#fitem_id_script').show();
        } else if (edit_contype == 'embed') {
            videoidgroup.hide();
            atgroup.hide();
            embedgroup.show();
            $('#fitem_id_script').show();
        }
        <?php }?>
    });

    $("button.delete").click(function () {

        var filedir = $(this).attr('data-url');
        var papa = $(this).parent();

        $.ajax({
            url: '<?php echo $CFG->wwwroot; ?>/local/repository/delete_contents.php',
            type: 'POST',
            async: true,
            data: {
                mode: 'filedel',
                filedir: filedir
            },
            success: function (data, textStatus, jqXHR) {
                papa.hide();
            },
            error: function (jqXHR, textStatus, errorThrown) {

            }
        });

    });
    
    function delete_file(obj){
    
        var filedir = $(obj).attr('data-url');
        var papa = $(obj).parent();
        
        $.ajax({
            url: '<?php echo $CFG->wwwroot; ?>/local/repository/delete_contents.php',
            type: 'POST',
            async: true,
            data: {
                mode: 'filedel',
                filedir: filedir
            },
            success: function (data, textStatus, jqXHR) {
                papa.hide();
            },
            error: function (jqXHR, textStatus, errorThrown) {

            }
        });
     }
    

    $(".cfiles button.delete").click(function () {

        var fileid = $(this).attr('data-url');
        var papa = $(this).parent();

        $.ajax({
            url: '<?php echo $CFG->wwwroot; ?>/local/repository/delete_contents.php',
            type: 'POST',
            async: true,
            data: {
                mode: 'cfiledel',
                fileid: fileid
            },
            success: function (data, textStatus, jqXHR) {
                papa.hide();
            },
            error: function (jqXHR, textStatus, errorThrown) {

            }
        });

    });

    $("input[name=stay_file]").click(function () {
        var type = $("input[name=con_type]").val();
        if ($("input[name=stay_file]").prop("checked")) {
            if (type == 'video')
               videoidgroup.show();
            else if (type == 'embed')
                embedgroup.show();
            else if (type == 'media')
                $('#fgroup_id_mediaidgroup').show();
            else
                atgroup.show();
        } else {
            atgroup.hide();
            videoidgroup.hide();
            embedgroup.hide();
            $('#fgroup_id_mediaidgroup').hide();
        }
    });
    $(window).load(function () {
        $('input[name=tfile]').attr("id", 'video_file_name');
        $('input[name=file_dir]').attr("id", 'form_f_path');
        $('input[name=userid]').attr("id", 'form_userid');
        caption.hide();
        
        <?php 
            if(isset($file->con_type) && ($file->con_type == 'video' || $file->con_type == 'embed')) {
        ?>    
        caption.show();
        <?php
            }
        ?>
        var radio_val = $("input[name=cc_type]:checked").val();
        var radio_val2 = $("input[name=con_type]:checked").val();
        if (!radio_val2)
            radio_val2 = $("input[name=con_type_ref]").val();
        if (!radio_val2)
            radio_val2 = $("input[name=con_type]").val();


        switch (radio_val) {
            case 1 :
                $('.mform_author').hide();
                $('.mform_cc_mark').hide();
                break;
            case 2 :
                $('.mform_author').show();
                $('.mform_cc_mark').show();
                break;
            case 3 :
                $('.mform_cc_mark').hide();
                $('.mform_author').show();
                break;
        }
        if ($("input[name=stay_file]").prop("checked")) {
            if (radio_val2 == 'video'){
                videoidgroup.show();
                caption.show();
            } else if (radio_val2 == 'embed'){
                embedgroup.show();
                caption.show();
            } else if (radio_val2 == 'media'){
                $('#fgroup_id_mediaidgroup').show();
            } else {
                atgroup.show();
            }
        } else if ($("input[name=stay_file]").val() != 1) {
            
            switch (radio_val2) {
                case 'word' :
                    atgroup.show();
                    embedgroup.hide();
                    videoidgroup.hide();
                    caption.hide();
                    break;
                case 'html' :
                    embedgroup.hide();
                    videoidgroup.hide();
                    atgroup.show();
                    caption.hide();
                    break;
                case 'video' :
                   videoidgroup.show();
                    atgroup.hide();
                    embedgroup.hide();
                    caption.show();
                    break;
                    caption.show();
                case 'embed' :
                    atgroup.hide();
                    embedgroup.show();
                    videoidgroup.hide();
                    caption.show();
                    break;
                case 'ref' :
                    atgroup.show();
                    embedgroup.hide();
                    videoidgroup.hide();
                    caption.hide();
                    break;
            }
        }

    });
    $("input[name=con_type]").click(function () {
        var radio_val2 = $("input[name=con_type]:checked").val();
        if (radio_val2 == 'word' || radio_val2 == 'html') {
            videoidgroup.hide();
            embedgroup.hide();
            atgroup.show();
            caption.hide();
        } else if (radio_val2 == 'video') {
            videoidgroup.show();
            atgroup.hide();
            embedgroup.hide();
            caption.show();
        } else if (radio_val2 == 'embed') {
            videoidgroup.hide();
            atgroup.hide();
            embedgroup.show();
            caption.show();
        }
    });
</script>
<script src="//code.jquery.com/ui/1.11.4/jquery-ui.js"></script>
<script>
    function enter_key(){
        if (window.event.keyCode == 13) { event.preventDefault(); }
    }
    function enter_search(){
        if (window.event.keyCode == 13) {
             event.preventDefault();
             search_embed_contents();
        }
    }
    
    function search_embed_contents() {
        var type = $('select[name=emb_type] option:selected').val(), type_txt = '';
        
        if(type == 'youtube'){
            type_txt = 'Youtube';
            width = 670;
        } else if(type == 'vimeo'){
            type_txt = 'Vimeo';
            width = 670;
        } else {
            type_txt = 'RISS';
            width = 1024;
        }
        var dir = './ajax/search_embed_' + type + '.php';
        var tag = $("<div id='load_form'></div>");
        
        if(!$('input[name=emb_code]').val()){
            alert('???????????? ??????????????????.');
            $('input[name=emb_code]').focus();
            return false;
        }
        $.ajax({
            url: dir,
            data: { 
                search : $('input[name=emb_code]').val()
            },
            success: function (data) {

                $('body').css({'overflow': 'hidden'});
                tag.html(data).dialog({
                    title: type_txt,
                    modal: true,
                    width: width,
                    height: 650,
                    position: { my: "center", at: "center" , of:$(document) },
                    buttons: [{
                            id: 'close',
                            text: '<?php echo get_string('close', 'local_repository'); ?>',
                            disable: true,
                            click: function () {
                                $('body').css({'overflow': 'auto'});
                                $('#load_form').remove();
                                $(this).dialog("close");
                            }
                        }]
                }).dialog('open');
                $(".ui-dialog-titlebar-close").click(function () {
                    $('body').css({'overflow': 'auto'});
                    $('#load_form').remove();
                });
            }

        });
    }
    
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
<?php
echo $OUTPUT->footer();
?>
