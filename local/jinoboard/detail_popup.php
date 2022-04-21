<?php

require(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once $CFG->dirroot . '/local/jinoboard/lib.php';

$id       = optional_param('id', 0, PARAM_INT);
$mode     = optional_param('mode', 'write', PARAM_INT);
$page     = optional_param('page', 1, PARAM_INT);
$search   = optional_param('search', '', PARAM_RAW);
$perpage  = optional_param('perpage', 10, PARAM_INT);

$context = context_system::instance();

$content = $DB->get_record('jinoboard_contents', array('id' => $id));
$board   = $DB->get_record('jinoboard', array('id' => $content->board));

$postuser = $DB->get_record('user', array('id' => $content->userid));
$fullname = fullname($postuser);
$userdate = userdate($content->timecreated);
$by = new stdClass();
$by->name = $fullname;
$by->date = $userdate;

// 본인이 글을 읽은 경우 조회수가 증가하지 않고, 재 접속시 조회수가 증가하지 않음
if($SESSION->readcontent != $content->id.'_popup'){
    if($content->userid != $USER->id){
$DB->set_field_select('jinoboard_contents', 'viewcnt', intval($content->viewcnt) + 1, " id='$content->id'");
    }
}
$SESSION->readcontent = $content->id.'_popup';
//require_login();

$boardname = get_string($board->name,'local_jinoboard');



$PAGE->set_context($context);
$PAGE->set_title($boardname);
$PAGE->set_heading($boardname);
$PAGE->set_url('/local/jinoboard/detail_popup.php');
$PAGE->set_pagelayout('popup');

$boardname = get_string($board->name,'local_jinoboard');
$PAGE->navbar->add($boardname);
$PAGE->navbar->add(get_string('viewdetail', 'local_jinoboard'));

echo $OUTPUT->header();



$output .= html_writer::start_tag('div', array('class' => 'board-detail-area'));

//여기가 글내용 디테일하게 들어가는 영역이다.

//제목영역
$output .= html_writer::start_tag('div', array('class' => 'detail-title-area'));
$output .= html_writer::tag('span', $content->title, array('class' => 'detail-title'));
$output .= html_writer::tag('br','');
$output .= html_writer::tag('span', get_string("bynameondate", "local_jinoboard", $by), array('class' => 'detail-date'));
$output .= html_writer::tag('span', $content->viewcnt.'<br/><span>'.get_string("viewcount", "local_jinoboard").'</span>', array('class' => 'detail-viewinfo area-right'));
$output .= html_writer::end_tag('div');

//내용영역
$content->contents = file_rewrite_pluginfile_urls($content->contents, 'pluginfile.php', $context->id, 'local_jinoboard', 'contents', $content->id);
$output .= html_writer::tag('div', $content->contents, array('class' => 'detail-contents'));

//첨부파일영역
$fs = get_file_storage();
$files = $fs->get_area_files($context->id, 'local_jinoboard', 'attachment', $content->id, 'timemodified', false);
$attachments = "";
if (count($files) > 0) {
    $type = '';
    $attfile = '';

    if ($CFG->enableportfolios)
        $canexport = $USER->id == $content->userid;
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

        if (!empty($canexport)) {
            $button = new portfolio_add_button();
            $button->set_callback_options('jinotechboard_portfolio_caller', array('id' => $id, 'attachment' => $file->get_id()), '/local/jinotechboard/locallib.php');

            $button->set_format_by_file($file);
            $attfile .= $button->to_html(PORTFOLIO_ADD_ICON_LINK);
        }
        $attfile .= '</li>';
    }

    $attachments .= $attfile;

    $output .= html_writer::start_tag('div', array('class' => 'detail-attachment-area'));
    $output .= html_writer::tag('span', get_string('attachment', 'jinoforum'), array('class' => "detail-attachment-title"));
    $output .= html_writer::tag('ul', $attachments, array('class' => "detail-attachment"));
    $output .= html_writer::end_tag('div');
}


//댓글영역
if ($board->allowcomment == 1) {
	$output .= html_writer::start_tag('div', array('class' => 'table-reply-area'));
	$output .= html_writer::start_tag('form', array('method' => 'post', 'id' => 'comment_form', 'class' => 'reply', 'action' => 'comment.php?id=' . $id));
	$input = html_writer::tag('textarea', '', array('id' => 'comment_textarea', 'name' => 'comment_value'));
	$cols = html_writer::tag('span', $input, array('class' => "option"));
	$btn = html_writer::tag('input', '', array('id' => 'comment_submit', 'type' => 'submit', 'class' => 'reply-submit', 'value' => get_string('comment', 'jinoforum')));
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
				$linktext = html_writer::link("comment.php?id=" . $id . "&type=delete&commentid=" . $comment->id, get_string('delete', 'jinoforum'), array('class'=>'bluetone'));
			}

			$p = html_writer::tag('p', $comment->comments."&nbsp;".$linktext, array('class' => "value", 'colspan' => '3'));
			$span = html_writer::tag('span', $fullname . " | " . $timecreated, array('class' => "comment_author"));
			$output .= html_writer::tag('li', $p . $span, array('class' => "comment_row"));
		}
		$output .= html_writer::end_tag('ul');
	}
	$output .= html_writer::end_tag('div');
}


echo $output;


echo $OUTPUT->footer();

