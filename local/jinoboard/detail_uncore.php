<?php

require(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once $CFG->dirroot . '/local/jinoboard/lib.php';
$bid = optional_param('board', 0, PARAM_INT);
$id = optional_param('id', 0, PARAM_INT);
$mode = optional_param('mode', 'write', PARAM_INT);
$page = optional_param('page', 1, PARAM_INT);
$search = optional_param('search', '', PARAM_RAW);
$perpage = optional_param('perpage', 10, PARAM_INT);
$list_num = optional_param('list_num', 10, PARAM_INT);
$market = optional_param('market', 3, PARAM_INT);
$searchfield = optional_param('searchfield', 'title', PARAM_RAW);

$context = context_system::instance();

$content = $DB->get_record('jinoboard_contents', array('id' => $id));
$board = $DB->get_record('jinoboard', array('id' => $content->board));

$role = $DB->get_field('lmsdata_user','usergroup', array('userid' => $USER->id));
if (is_siteadmin()) {
    $role = 'ma';
} else if(empty($role)){
    $role = 'gu';
}

$allows = $DB->get_records('jinoboard_allowd',array('board'=>$board->id));
$access = array();

foreach($allows as $allow){
$access[$allow->allowrole] = $allow; 
}
$myaccess = $access[$role];

if($myaccess->allowdetail != 'true'){
    redirect($CFG->wwwroot, 'Permission Denied');
}

$postuser = $DB->get_record('user', array('id' => $content->userid));
$fullname = fullname($postuser);
$userdate = userdate($content->timecreated);
$by = new stdClass();
$by->name = $fullname;
$by->date = $userdate;

// 본인이 글을 읽은 경우 조회수가 증가하지 않고, 재 접속시 조회수가 증가하지 않음
if($SESSION->readcontent != $content->id.'_uncore'){
    if($content->userid != $USER->id){
$DB->set_field_select('jinoboard_contents', 'viewcnt', intval($content->viewcnt) + 1, " id='$content->id'");
    }
}
$SESSION->readcontent = $content->id.'_uncore';

$boardname = (current_language() == 'ko') ? $board->name : $board->engname;

$PAGE->set_context($context);
$PAGE->set_title($boardname);
$PAGE->set_heading($boardname);
$PAGE->set_url('/local/jinoboard/detail_uncore.php?id='.$id.'&page='.$page.'&perpage='.$perpage."&list_num=".$list_num."&search".$search."&board=".$bid);
$PAGE->set_pagelayout('standard');
$PAGE->add_body_class('path-local-jinoboard-'.$bid);

$PAGE->navbar->add(get_string('guide','local_jinoboard'));
$PAGE->navbar->add($boardname);
$PAGE->navbar->add(get_string('viewdetail', 'local_jinoboard'));

echo $OUTPUT->header();

$output = html_writer::start_tag('div', array('class' => 'board-detail-area'));
$sql = "select count(*) from {jinoboard_comments} jc "
        . " where jc.board = ".$board->id." and jc.contentsid =".$content->id;
$comments_count = $DB->count_records_sql($sql);
//여기가 글내용 디테일하게 들어가는 영역이다.
//
//제목영역
if ($board->allowrental == '1'){
    switch($content->status){
     case 0:  $content->title .= '&nbsp;[신청]'; break;
     case 1:  $content->title .= '&nbsp;[승인]'; break;
     case 2:  $content->title .= '&nbsp;[거절]'; break;
    }
}
        
$output .= html_writer::start_tag('div', array('class' => 'detail-title-area'));
$output .= html_writer::tag('span', strip_tags($content->title), array('class' => 'detail-title'));
$output .= html_writer::tag('br', '');
$output .= html_writer::tag('span', get_string("bynameondate", "local_jinoboard", $by), array('class' => 'detail-date'));
$output .= html_writer::tag('span','<span>' . get_string('reply:cnt', 'mod_jinotechboard') . ' </span>'. $comments_count , array('class' => 'detail-viewinfo area-right'));
$output .= html_writer::tag('span','<span>' . get_string("viewcount", "local_jinoboard") . ' </span>'. $content->viewcnt, array('class' => 'detail-viewinfo area-right'));
$output .= html_writer::end_tag('div');

//내용영역
$content->contents = file_rewrite_pluginfile_urls($content->contents, 'pluginfile.php', $context->id, 'local_jinoboard', 'contents', $content->id);
$output .= html_writer::tag('div', $content->contents, array('class' => 'detail-contents'));

//첨부파일영역
$fs = get_file_storage();
$files = $fs->get_area_files($context->id, 'local_jinoboard', 'attachment', $content->id, 'timemodified', false);
$attachments = "";
if (count($files) > 0) {
    $attfile = '';
    if ($CFG->enableportfolios){
        $canexport = $USER->id == $content->userid;
    }
    if (!empty($canexport)) {
        require_once($CFG->libdir . '/portfoliolib.php');
    }
    foreach ($files as $file) {
        $filename = $file->get_filename();
        $mimetype = $file->get_mimetype();
        $iconimage = '<img src="' . $OUTPUT->pix_url(file_mimetype_icon($mimetype)) . '" class="icon" alt="' . $mimetype . '" />';
        $path = file_encode_url($CFG->wwwroot . '/pluginfile.php', '/' . $context->id . '/local_jinoboard/attachment/' . $content->id . '/' . $filename);
        $attfile .= "<li>";
        if ($board->id == $CFG->DATAID) {
            $attfile .= "<a href=\"javascript:alertDistribution('$path');\">$iconimage</a> ";
            $attfile .= "<a href=\"javascript:alertDistribution('$path');\">" . s($filename) . "</a>";
        } else {
            $attfile .= "<a href=\"$path\">$iconimage</a> ";
            $attfile .= format_text("<a href=\"$path\">" . s($filename) . "</a>", FORMAT_HTML, array('context' => $context));
        }

        //if (!empty($canexport)) {
        //  $button = new portfolio_add_button();
        // $button->set_callback_options('jinotechboard_portfolio_caller', array('id' => $id, 'attachment' => $file->get_id()), '/local/jinotechboard/locallib.php');
        //  $button->set_format_by_file($file);
        //  $attfile .= $button->to_html(PORTFOLIO_ADD_ICON_LINK);
        //}
        $attfile .= '</li>';
    }

    $attachments .= $attfile;

    $output .= html_writer::start_tag('div', array('class' => 'detail-attachment-area'));
    $output .= html_writer::tag('span', get_string('attachment', 'local_jinoboard'), array('class' => "detail-attachment-title"));
    $output .= html_writer::tag('ul', $attachments, array('class' => "detail-attachment"));
    $output .= html_writer::end_tag('div');
}

//버튼영역
$list_page = ceil($list_num/$perpage);
if($list_page == 0){ 
    $list_page = 1; 
}
$left_btns = html_writer::tag('button', get_string('list'), array('onclick' => 'location.href="' . $CFG->wwwroot . '/local/jinoboard/list.php?id=' . $bid .'&search='.$search.'&page='.$list_page.'&perpage='.$perpage."&searchfield=".$searchfield.'"'));
if ($board->allowreply == 1 && $myaccess->allowreply == 'true') {
    $left_btns .= html_writer::tag('button', get_string('replies', 'local_jinoboard'), array('onclick' => 'location.href="' . $CFG->wwwroot . '/local/jinoboard/write_uncore.php?id=' . $content->id . '&mode=reply&board=' . $board->id.'"'));
}
$right_btns = "";
if ($board->allowrental == '1' && is_siteadmin()) {
    $right_btns .= html_writer::tag('button', get_string('yes', 'local_jinoboard'), array('onclick' => 'location.href="' . $CFG->wwwroot . '/local/jinoboard/rental.php?id=' . $content->id . '&status=1&board=' . $board->id . '"'));
    $right_btns .= html_writer::tag('button', get_string('no', 'local_jinoboard'), array('onclick' => 'location.href="' . $CFG->wwwroot . '/local/jinoboard/rental.php?id=' . $content->id . '&status=2&board=' . $board->id . '"'));
}
if ($myaccess->allowmodify == 'true' && $USER->id == $content->userid) {
    $right_btns .= html_writer::tag('button', get_string('edit', 'local_jinoboard'), array('onclick' => 'location.href="' . $CFG->wwwroot . '/local/jinoboard/write_uncore.php?id=' . $content->id . '&mode=edit&board=' . $board->id . '"'));
}
if (has_capability('local/jinoboard:delete', $context) || ($USER->id == $content->userid)) {
//    $right_btns .= html_writer::tag('button', get_string('delete', 'local_jinoboard'), array('onclick' => 'location.href="' . $CFG->wwwroot . '/local/jinoboard/write_uncore.php?id=' . $content->id . "&mode=delete&type=" . $board->type . '"'));
    $right_btns .= html_writer::tag('button', get_string('delete', 'local_jinoboard'), array('id'=>'del-button', 'data-cid'=>$content->id, 'data-mode'=>'delete', 'data-type'=>$board->type));
}
$cols = html_writer::tag('div', $left_btns, array('class' => "btn-area btn-area-left"));
$cols .= html_writer::tag('div', $right_btns, array('class' => "btn-area btn-area-right"));
$output .= html_writer::tag('div', $cols, array('class' => "table-footer-area"));


//댓글영역
if ($board->allowcomment == 1 && $myaccess->allowcomment == 'true') {
    $output .= html_writer::start_tag('div', array('class' => 'table-reply-area'));
    $output .= html_writer::start_tag('form', array('method' => 'post', 'id' => 'comment_form', 'class' => 'reply', 'action' => 'comment_uncore.php?search='.$search.'&list_num='.$list_num.'&perpage='.$perpage.'&id='.$id));
    $input = html_writer::tag('textarea', '', array('id' => 'comment_textarea','maxlength'=>'300', 'name' => 'comment_value', "title" => "댓글"));
    $cols = html_writer::tag('span', $input, array('class' => "option"));
    $btn = html_writer::tag('input', '', array('id' => 'comment_submit', 'type' => 'submit', 'class' => 'reply-submit', 'value' => get_string('comment', 'local_jinoboard')));
    $cols .= html_writer::tag('span', $btn, array('class' => "option"));
    $output .= html_writer::tag('tr', $cols, array('class' => "view_row"));
    $output .= html_writer::end_tag('form');

    //댓글 목록 영역
    if ($comments = $DB->get_records('jinoboard_comments', array('contentsid' => $id), ' id desc')) {
        $output .= html_writer::start_tag('ul', array('class' => 'reply-list'));
        foreach ($comments as $comment) {

            $commentuser = $DB->get_record('user', array('id' => $comment->userid));
            $fullname = fullname($commentuser);
            $timecreated = date("Y-m-d", $comment->timecreated);
            $linktext = "";
            if (has_capability('local/jinoboard:commentdelete', $context) || $USER->id == $comment->userid) {
                $linktext = html_writer::link("comment_uncore.php?search=".$search."&list_num=".$list_num."&perpage=".$perpage.'&id='.$id."&type=delete&commentid=" . $comment->id, get_string('delete', 'local_jinoboard'), array('class' => 'blue_delete_button'));
            }

            $p = html_writer::tag('p', $comment->comments, array('class' => "value", 'colspan' => '3'));
            $span = html_writer::tag('span', $fullname . " | " . $timecreated . "&nbsp;" . $linktext, array('class' => "comment_author"));
            $output .= html_writer::tag('li', $p . $span, array('class' => "comment_row"));
        }
        $output .= html_writer::end_tag('ul');
    }
    $output .= html_writer::end_tag('div');
}

echo $output;

echo $OUTPUT->footer();
?>
<script>
    $("document").ready(function() {  
        var currentstr = 0;
        var mx = $("#comment_textarea").attr('maxlength');
        $('#comment_textarea').keyup(function(){
                var va = $(this).val().length;
                if(currentstr != va){
                    if(mx <= va){
                        alert('<?php echo get_string('maxlength','local_jinoboard');?>');
                    }
                }
                    currentstr = va;    
        });
        
        $('#del-button').click(function(){
            if(confirm('삭제된 데이터는 복구되지 않습니다. 정말 삭제하시겠습니까?')) {
                var id = $(this).data('cid');
                var mode = $(this).data('mode');
                var type = $(this).data('type');
                document.location.href = './write_uncore.php?id='+id+'&mode='+mode+'&type='+type;
            }
        });
    }) 
</script>
