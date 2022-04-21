<?php

require(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once $CFG->dirroot . '/local/jinoboard/lib.php';


$id = optional_param('id', 0, PARAM_INT);				// content ID
$comment_value = optional_param('comment_value', "", PARAM_TEXT);
$type = optional_param('type', 'write', PARAM_TEXT);
$commentid = optional_param('commentid', 0, PARAM_INT);
$board_type = optional_param('boardtype', 1, PARAM_INT);
$list_num = optional_param('list_num', 1, PARAM_INT);
$search = optional_param('search', '', PARAM_RAW);
$market = optional_param('market', '', PARAM_INT);
$perpage = optional_param('perpage', 10, PARAM_INT);
$content = $DB->get_record('jinoboard_contents',array('id'=>$id));
$board = $DB->get_record('jinoboard',array('id'=>$content->board));


if ($type == "write") {

	$comment = new stdClass();
	$comment->board = $board->id;
	$comment->contentsid = $content->id;
	$comment->userid = $USER->id;
	$comment->comments = $comment_value;
	$user = $DB->get_record('user',array('id'=>$USER->id));
	$comment->username = fullname($user);
	$comment->timecreated = time();
} else if ($type == 'modify') {
	$comment->id = $commentid;
	$comment->comments = $comment_value;
} else {
	$DB->delete_records("jinoboard_comments", array('id' => $commentid));
	redirect(new moodle_url('/local/jinoboard/detail.php', array('id' => $id,'type'=>$board_type,'search'=>$search,'perpage'=>$perpage,'list_num'=>$list_num,'market'=>$market)));
}
$comment->timemodified = time();
if (!$DB->get_records("jinoboard_comments", array('id' => $commentid))) {
        $DB->insert_record('jinoboard_comments', $comment);
	$DB->set_field_select('jinoboard_contents', 'commentscount', intval($post->recommendcnt) + 1, " id='$id'");
} else {
	$DB->update_record('jinoboard_comments', $comment);
}

redirect(new moodle_url('/local/jinoboard/detail.php', array('id' => $id,'type'=>$board_type,'search'=>$search,'perpage'=>$perpage,'list_num'=>$list_num,'market'=>$market)));
