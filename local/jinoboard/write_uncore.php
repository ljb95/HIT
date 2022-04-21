<?php

require(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once $CFG->dirroot . '/local/jinoboard/lib.php';
require_once $CFG->dirroot . '/lib/form/filemanager.php';
require_once $CFG->dirroot . '/local/jinoboard/write_uncore_form.php';


//파라미터 값을 받는 부분 $_POST, $_GET
$id = optional_param('id', 0, PARAM_INT);
$bid = optional_param('board', 0, PARAM_INT);
$mode = optional_param('mode', 'write', PARAM_RAW);
$page = optional_param('page', 1, PARAM_INT);
$search = optional_param('search', '', PARAM_RAW);
$perpage = optional_param('perpage', 10, PARAM_INT);
$completion = optional_param('completion', 0, PARAM_INT);

//페이지 셋팅
$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_url('/local/jinoboard/write_uncore.php?board='.$bid);
$PAGE->set_pagelayout('standard');
$PAGE->add_body_class('path-local-jinoboard-'.$bid);

//DB에서 역할 체크 
$role = $DB->get_field('lmsdata_user','usergroup', array('userid' => $USER->id));
if (is_siteadmin()) {
    $role = 'ma';
} else if(empty($role)){
    $role = 'gu';
}

//DB에서 게시판 내역 불러옴
$content = $DB->get_record('jinoboard_contents', array('id' => $id));
if (empty($content)) {
    $board = $DB->get_record('jinoboard', array('id' => $bid));
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

//권한 체크
$allows = $DB->get_records('jinoboard_allowd',array('board'=>$board->id));
$access = array();

foreach($allows as $allow){
$access[$allow->allowrole] = $allow; 
}
$myaccess = $access[$role];

//현재 언어체크를 진행하여 board name을 현재 언어로 설정 
$boardname = (current_language() == 'ko') ? $board->name : $board->engname;

$PAGE->navbar->add($boardname);
$PAGE->set_title($boardname);
$PAGE->set_heading($boardname);


if ($mode == "delete") {
    //삭제처리 할때
    if($myaccess->allowdelete != 'true'){
        //권한이 없으므로 리다이렉트 
        redirect($CFG->wwwroot, 'Permission Denied');
    }
    //첨부파일 삭제 2016.6.9 chs
    $fs = get_file_storage();
    $files = $fs->get_area_files($context->id, 'local_jinoboard', 'attachment', $id, 'id');
    if(!empty($files)) {
        foreach($files as $file) {
            $file->delete();
        }
    }
    
    //DB 테이블에서 데이터 삭제 
    $DB->delete_records('jinoboard_contents', array('id' => $id));
    $DB->delete_records('jinoboard_comments', array('contentsid' => $id, 'board' => $board->id));
    redirect("list.php?id=$board->id");
} else if($mode == 'edit'){
    if($myaccess->allowmodify != 'true'){
        redirect($CFG->wwwroot, 'Permission Denied');
    }
}
//file option setting
$options = array('noclean' => true, 'subdirs' => true, 'maxfiles' => -1, 'maxbytes' => 0, 'context' => $context);

//Jinoboard_write_form class 초기화 
$mform = new jinoboard_write_form(null, array('options' => $options,
    'context' => $context,
    'content' => $content
    , 'mode' => $mode)
);

if($mode == 'completion'){
    $board = $DB->get_record('jinoboard_used_board',array('contentid'=>$id));
    $content = new stdClass();
    $content->id = $board->id;
    $content->completion = $completion;
    $newid = $DB->update_record('jinoboard_used_board', $content);
    redirect("detail.php?id=$newid&board=$id");
}
if ($mform->is_cancelled()) {
    //취소 했을때
    redirect("list.php?id=$board->id");
} else if ($fromform = $mform->get_data()) {
    //확인 눌렀을때 
    if ($mode == "edit") {
        edit_uncore_content($fromform, $board->id, $context, $mode, $content);
        redirect("detail_uncore.php?id=$fromform->id&board=$board->id");
    } else {
        add_new_uncorecontent($fromform, $bid, $context, $mode, $content);
    }  
    redirect("list.php?id=$board->id");
}

$draftitemid = file_get_submitted_draft_itemid('attachments');
file_prepare_draft_area($draftitemid, $context->id, 'local_jinoboard', 'attachment', empty($content->id) ? null : $content->id, jinoboard_write_form::attachment_options($board));

$draftid_editor = file_get_submitted_draft_itemid('contents');

if ($mode == "reply") {
    $content->title = 're:' . $content->title;
    $content->contents = '';
    //$content->contents = '<br/><br/><div style="border-top:1px dashed #999;color:#999;"><p style="border-top:1px dashed #999;margin-top:3px;padding-top:3px;">[원문내용]</p>' . $content->contents . "</div>";
}

if ($mode != 'write') {
    $currenttext = file_prepare_draft_area($draftid_editor, $context->id, 'local_jinoboard', 'contents', $id, jinoboard_write_form::editor_options($context, $id), $content->contents);
    $mform->set_data(array('attachments' => $draftitemid,
        'general' => $boardname,
        'name' => html_entity_decode($content->title),
        'timeend' => $content->timeend,
        'contents' => array(
            'text' => $currenttext,
            'format' => 1,
            'itemid' => $draftid_editor,
        ),
        'board' => $board->id));
} else {
    if($myaccess->allowwrite != 'true'){
        redirect($CFG->wwwroot, 'Permission Denied');
    }
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
        $("label[for=fgroup_id_filemanager]").parent().parent().show();
    </script>
    <?php

}
?>
<script>
    $(window).load(function () {
        if ($("input[name=view_filemanager]").is(":checked") == true) {
            $("label[for=fgroup_id_filemanager]").parent().parent().show();
        } else {
            $("label[for=fgroup_id_filemanager]").parent().parent().hide();
        }
    });
    $("input[name=view_filemanager]").click(function () {
        if ($("input[name=view_filemanager]").is(":checked") == true) {
            $("label[for=fgroup_id_filemanager]").parent().parent().show();
        } else {
            $("label[for=fgroup_id_filemanager]").parent().parent().hide();
        }
    });
</script>
<?php

echo $OUTPUT->footer();
?>
