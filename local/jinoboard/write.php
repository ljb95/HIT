<?php

require(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once $CFG->dirroot . '/local/jinoboard/lib.php';
require_once $CFG->dirroot . '/lib/form/filemanager.php';
require_once $CFG->dirroot . '/local/jinoboard/write_form.php';

$type = optional_param('type', 1, PARAM_INT);
$id = optional_param('id', 0, PARAM_INT);
$mode = optional_param('mode', 'write', PARAM_RAW);
$page = optional_param('page', 1, PARAM_INT);
$search = optional_param('search', '', PARAM_RAW);
$perpage = optional_param('perpage', 10, PARAM_INT);
$completion = optional_param('completion', 0, PARAM_INT);

$context = context_system::instance();

require_login();
if($type!=2 && $type != 7){
	if (!has_capability("local/jinoboard:write", $context)){
		redirect(get_login_url());
	}
}

$PAGE->set_context($context);
$PAGE->set_url('/local/jinoboard/write.php');
$PAGE->set_pagelayout('standard');


$content = $DB->get_record('jinoboard_contents', array('id' => $id));
if (empty($content)) {
    $board = $DB->get_record('jinoboard', array('type' => $type));
    $ten_day = strtotime(date("Y-m-d", strtotime("+10 day")));
    $content = new stdClass();
    $content->contents = '';
    $content->title = '';
    $content->isnotice = isset($content->isnotice) ? $content->isnotice : 0;
    $content->issecret = isset($content->issecret) ? $content->issecret : 0;
    $content->timeend = $ten_day;
} else {
    $board = $DB->get_record('jinoboard', array('id' => $content->board));
}


$boardname = (current_language() == 'ko')?$board->name:$board->engname;

$PAGE->navbar->add($boardname);
$PAGE->set_title($boardname);
$PAGE->set_heading($boardname);


if ($mode == "delete") {
    //첨부파일 삭제 2016.6.9 chs
    $fs = get_file_storage();
    $files = $fs->get_area_files($context->id, 'local_jinoboard', 'attachment', $id, 'id');
    if(!empty($files)) {
        foreach($files as $file) {
            $file->delete();
        }
    }
    $DB->delete_records('jinoboard_contents', array('id' => $id));
    $DB->delete_records('jinoboard_comments', array('contentsid' => $id, 'board' => $board->id));
    redirect("index.php?type=$board->type");
}
$options = array('noclean' => true, 'subdirs' => true, 'maxfiles' => -1, 'maxbytes' => 0, 'context' => $context);
$mform = new jinoboard_write_form(null, array('options' => $options,
    'context' => $context,
    'content' => $content
    , 'mode' => $mode)
);
if($mode == 'completion'){
    global $DB;
    $board = $DB->get_record('jinoboard_used_board',array('contentid'=>$id));
    $content = new stdClass();
    $content->id = $board->id;
    $content->completion = $completion;
    $newid = $DB->update_record('jinoboard_used_board', $content);
    redirect("detail.php?id=$id&type=$type");
}
if ($mform->is_cancelled()) {
    redirect("index.php?type=$board->type");
} else if ($fromform = $mform->get_data()) {
    if ($mode == "edit") {
        edit_content($fromform, $type, $context, $mode, $content);
        redirect("detail.php?id=$fromform->id&type=$type");
    } else {
        add_new_content($fromform, $type, $context, $mode, $content);
    }
    redirect("index.php?type=$board->type");
}

$draftitemid = file_get_submitted_draft_itemid('attachments');
file_prepare_draft_area($draftitemid, $context->id, 'local_jinoboard', 'attachment', empty($content->id) ? null : $content->id, jinoboard_write_form::attachment_options($board));

$draftid_editor = file_get_submitted_draft_itemid('contents');
if ($mode == "reply") {
    $content->title = 're:' . $content->title;
    $content->contents = '';
    //$content->contents = '<br/><br/><div style="border-top:1px dashed #999;color:#999;"><p style="border-top:1px dashed #999;margin-top:3px;padding-top:3px;">[원문내용]</p>' . $content->contents . "</div>";
}

if($mode == 'write' && $type == 7){
    $currenttext = get_string('format','local_jinoboard');
     $mform->set_data(array('attachments' => $draftitemid,
        'contents' => array(
            'text' => $currenttext,
            'format' => 1,
            'itemid' => $draftid_editor,
        )));
}

if ($mode != 'write') {
    $currenttext = file_prepare_draft_area($draftid_editor, $context->id, 'local_jinoboard', 'contents', $id, jinoboard_write_form::editor_options($context, $id), $content->contents);
    $mform->set_data(array('attachments' => $draftitemid,
        'general' => $boardname,
        'name' => $content->title,
        'timeend' => $content->timeend,
        'contents' => array(
            'text' => $currenttext,
            'format' => 1,
            'itemid' => $draftid_editor,
        ),
        'board' => $board->id));
}
echo $OUTPUT->header();

$mform->display();

$fs = get_file_storage();
if(!empty($content->id)){ 
    $files = $fs->get_area_files($context->id, 'local_jinoboard', 'attachment', $content->id, 'id', false);
}
if (!empty($files)) { 
?>
    <script>
        $("input[name=view_filemanager]").attr("checked", true);
        $("#fgroup_id_filemanager").show();
    </script>
<?php
}
?>
<script>
    $(window).load(function () {
        if ($("input[name=view_filemanager]").prop("checked")) {
            $("#fgroup_id_filemanager").show();
        } else {
            $("#fgroup_id_filemanager").hide();
        }
    });
    $("input[name=view_filemanager]").click(function () {
        if ($("input[name=view_filemanager]").prop("checked")) {
            $("#fgroup_id_filemanager").show();
        } else {
            $("#fgroup_id_filemanager").hide();
        }
    });
</script>
<?php
echo $OUTPUT->footer();
?>
