<?php
require(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once $CFG->dirroot . '/local/board/lib.php';
require_once $CFG->libdir . '/formslib.php';
require_once $CFG->dirroot . '/lib/form/filemanager.php';

$contentId = required_param('contentId', PARAM_INT);				// Discussion ID

$boardContent = $DB->get_record_sql('select c.*, jb.type, u.lastname, u.firstname, u.username, c.userid userid 
                    from {jinotechboard_contents} c
                    join {jinotechboard} jb on jb.id = c.board
                    left join {user} u on c.userid = u.id
                    where c.id = ?', array('id' => $contentId));

$boardContents = array($boardContent->id => $boardContent);

$course = $DB->get_record('course', array('id' => $boardContent->course), '*', MUST_EXIST);
$board = $DB->get_record('jinotechboard', array('id' => $boardContent->board), '*', MUST_EXIST);
$cm = get_coursemodule_from_instance('jinotechboard', $board->id, $course->id, false, MUST_EXIST);

$classname = context_helper::get_class_for_level(CONTEXT_MODULE);

$contexts[$cm->id] = $classname::instance($cm->id);

$context = $contexts[$cm->id];

require_course_login($course, true, $cm);

$PAGE->set_url(new moodle_url('/mod/jinotechboard/content.php', array('contentId' => $contentId)));
$PAGE->set_title("$course->shortname: " . format_string($boardContent->title));
$PAGE->set_context($context);
$PAGE->set_heading($course->fullname);
$PAGE->set_pagetype("local-board-content");
$PAGE->set_pagelayout('standard');
if ($board->course == SITEID) {
	$PAGE->navbar->ignore_active();
	$PAGE->navbar->add(get_string('study_reference_room', 'theme_dgist'), new moodle_url('mod/jinotechboard/view.php', array('b' => $CFG->NOTICEID)));
	$PAGE->navbar->add(get_string($board->name, "theme_dgist"));
	$PAGE->set_title(format_string(get_string($board->name, "theme_dgist")));
	$PAGE->set_heading(format_string(get_string($board->name, "theme_dgist")));

	$CFG->page_icon = '/theme/dgist/pix/images/support_icon.png';
	$CFG->page_description = get_string('support_' . $board->name . '_content', 'theme_dgist');
}
//$PAGE->requires->js('/mod/jinotechboard/dummyapi.js', true);

add_to_log($course->id, 'jinotechboard', 'view board content', $PAGE->url->out(false), $boardContent->id, $cm->id);
unset($SESSION->fromdiscussion);



echo $OUTPUT->header();

if (isset($boardContent->id)) {

	$DB->set_field_select('jinotechboard_contents', 'viewcnt', intval($boardContent->viewcnt) + 1, " id='$contentId'");

	$comments = $DB->get_records_select("jinotechboard_comments", "board=? and jinotechboard_contents=?", array($board->id, $contentId), "id DESC", "*");
}

require_once($CFG->dirroot . '/rating/lib.php');
$ratingoptions = new stdClass;
$ratingoptions->context = $context;
$ratingoptions->component = 'mod_jinotechboard';
$ratingoptions->ratingarea = 'post';
$ratingoptions->items = $boardContents;
$ratingoptions->aggregate = 1;
$ratingoptions->scaleid = 5;
$ratingoptions->userid = $USER->id;
if ($board->type == 'single' or ! $boardContent->id) {
	$ratingoptions->returnurl = "$CFG->wwwroot/mod/jinotechboard/view.php?id=$cm->id";
} else {
	$ratingoptions->returnurl = "$CFG->wwwroot/mod/jinotechboard/content.php?d=$boardContent->id";
}
$ratingoptions->assesstimestart = 0;
$ratingoptions->assesstimefinish = 0;

$rm = new rating_manager();
$boardContents = $rm->get_ratings($ratingoptions);


$datetimeformat = "%Y.%m.%d %H:%M";

$postuser = $DB->get_record('user',array('id'=>$boardContent->userid));
$userdate = userdate($boardContent->timecreated);

$output = html_writer::start_tag('div', array('class' => 'username'));
$output .= html_writer::end_tag('div');

$output .= html_writer::start_tag('div', array('class' => 'board-detail-area'));
//제목영역
$output .= html_writer::start_tag('div', array('class' => 'detail-title-area'));
$output .= html_writer::tag('span', $boardContent->title, array('class' => 'detail-title'));
$by = new stdClass();
$by->date = $userdate;
$by->name = fullname($postuser);
$output .= html_writer::tag('span', get_string('bynameondate','local_board',$by), array('class' => 'detail-date'));

$span = html_writer::tag('span', '<span>'.get_string('view:cnt','local_board').' </span>'. $boardContent->viewcnt);
$output .= html_writer::tag('span',$span, array('class' => 'detail-viewinfo area-right'));

$output .= html_writer::end_tag('div');

$boardContent->contents = file_rewrite_pluginfile_urls($boardContent->contents, 'pluginfile.php', $context->id, 'mod_jinotechboard', 'contents', $boardContent->id);
$output .= html_writer::tag('div', $boardContent->contents , array('class' => 'detail-contents'));

$attfile = '';
$fs = get_file_storage();
$files = $fs->get_area_files($context->id, 'mod_jinotechboard', 'attachment', $boardContent->id, 'id', false);
if (count($files) > 0) {
    if ($CFG->enableportfolios)
        $canexport = $USER->id == $boardContent->userid;
    if ($canexport) {
        require_once($CFG->libdir . '/portfoliolib.php');
    }
    
	foreach ($files as $file) {

		$filename = $file->get_filename();
		$mimetype = $file->get_mimetype();
		$iconimage = '<img src="' . $OUTPUT->pix_url(file_mimetype_icon($mimetype)) . '" class="icon" alt="' . $mimetype . '" />';
		$path = file_encode_url($CFG->wwwroot . '/pluginfile.php', '/' . $context->id . '/mod_jinotechboard/attachment/' . $boardContent->id . '/' . $filename);

		$attfile .= "<li><a href=\"$path\">$iconimage</a> ";
		$attfile .= format_text("<a href=\"$path\">" . s($filename) . "</a>", FORMAT_HTML, array('context' => $context));
		
		$attfile .= '</li>';
	}
	$attachments .= $attfile;
	
	$output .= html_writer::start_tag('div', array('class' => 'detail-attachment-area'));
    $output .= html_writer::tag('span', get_string('attachment', 'jinoforum'), array('class' => "detail-attachment-title"));
    $output .= html_writer::tag('ul', $attachments, array('class' => "detail-attachment"));
	$output .= html_writer::end_tag('div');

}
	echo $output;
?>
    <div class="table-footer-area">
        <div class="btn-area btn-area-left">
            <input type="button" class="btn_list blue-form"  onClick="javascript:location.href = '<?php echo $CFG->wwwroot . "/local/board/index.php?type=".$boardContent->type; ?>'" value="<?php echo get_string('content:list', 'local_board'); ?>" />
            <?php if (has_capability('mod/jinotechboard:reply', $context) && $board->allowreply == 1) { ?>
	        <input type="button"  class="btn_reply gray-form" value="<?php echo get_string('content:reply', 'local_board'); ?>" onclick="document.location.href = '<?php echo($CFG->wwwroot); ?>/local/board/write.php?mode=reply&type=<?php echo $boardContent->type; ?>&b=<?php echo($board->id); ?>&contentId=<?php echo($boardContent->id); ?>';"/>
	    <?php }?>
        </div>
        <div class="btn-area btn-area-right">
			
			<?php if (has_capability('mod/jinotechboard:edit', $context) || (($boardContent->userid == $USER->id))) { ?>
			<input type="button" class="btn_edit red-form" value="<?php echo get_string('content:edit', 'local_board'); ?>"  onclick="document.location.href = '<?php echo($CFG->wwwroot); ?>/local/board/write.php?mode=edit&type=<?php echo $boardContent->type; ?>&b=<?php echo($board->id); ?>&contentId=<?php echo($boardContent->id); ?>';"/></a>
			<?php
			}
                        
                        if (has_capability('mod/jinotechboard:delete', $context) || ($boardContent->userid == $USER->id)) {
			?>
			<input type="button" class="btn_delete blue-form" value="<?php echo get_string('content:delete', 'local_board'); ?>"  onclick="deleteBoard('<?php echo($boardContent->board); ?>', '<?php echo($contentId); ?>');"/>
			<?php
			}

			$recommend_where = ' jinotechboardid = ? and contentsid = ? and userid = ? ';
			$recommend_params = array($boardContent->board, $boardContent->id, $USER->id);
			$recommend_count = $DB->count_records_select("jinotechboard_recommend", $recommend_where, $recommend_params);

			if (empty($recommend_count) and $board->allowrecommend) {
				?>
				<input type="button"  class="btn_recommend" value="<?php echo get_string('reCommondCnt', 'jinotechboard'); ?>" onclick="reCommendBoard('<?php echo($board->id); ?>', '<?php echo($contentId); ?>');" />
				<?php
			}
			?>

        </div>
    </div>

    <form name="delForm" method="post">
        <input type="hidden" name="b" value="<?php echo($board->id); ?>">
        <input type="hidden" name="contentId" value="<?php echo($contentId); ?>">
    </form>
<?php
echo $OUTPUT->footer();
?>   
	<SCRIPT LANGUAGE="JavaScript">
		function deleteBoard(b, jinotechboard_contents) {
			if (confirm("<?php echo get_string('suredeleteselectedcontents', 'jinotechboard'); ?>")) {

				document.delForm.b.value = b;
				document.delForm.contentId.value = jinotechboard_contents;
				document.delForm.action = '<?php echo($CFG->wwwroot); ?>/local/board/delete.php';
				document.delForm.submit();

			}
		}

		function reCommendBoard(b, jinotechboard_contents) {
			if (confirm("<?php echo get_string('surerecommendcontents', 'jinotechboard'); ?>")) {

				document.delForm.b.value = b;
				document.delForm.contentId.value = jinotechboard_contents;
				document.delForm.action = '<?php echo($CFG->wwwroot); ?>/local/board/recommend.php';
				document.delForm.submit();
			}
		}
	</SCRIPT>    