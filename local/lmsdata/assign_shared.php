<?php
require(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once($CFG->dirroot . '/calendar/lib.php');
$id = optional_param('aid', 0, PARAM_INT); //개인이 제출한 과제의 아이디
$cmid = optional_param('cmid', 0, PARAM_INT); //코스모듈 아이디
$userid = optional_param('userid', 0, PARAM_INT); //사용자 고유 아이디
$context = get_context_instance(CONTEXT_COURSE, $id);

require_login();

$PAGE->set_context($context);
$PAGE->set_pagelayout('incourse');
$PAGE->set_title('공개된 과제');
$PAGE->navbar->add("공개된 과제");
$PAGE->set_url(new moodle_url('/local/lmsdata/assign_shared.php'));


$sql = 'select * from {assign_submission} asub '
        . 'join {assignsubmission_onlinetext} ao on asub.id = ao.submission '
        . 'where asub.id = :submission';
$assignsub = $DB->get_record_sql($sql, array('submission' => $id));

$user = $DB->get_field('lmsdata_user', 'eng_name', array('userid' => $userid));//사용자 이름

echo $OUTPUT->header();
?>
<h2><?php echo $user . ' 과제' ?></h2>

<?php
if ($assignsub->onlinetext) {//내용이 있을 때
    $content = $assignsub->onlinetext;
    $content = preg_replace("(\<(/?[^\>]+)\>)", "", $content);//태그 제거
    echo '<div><textarea id="editor" readonly="readonly" rows="20" cols="100">' . $content . '</textarea></div>';
}else{
    echo '<div></div>';
}
//파일 가져옴
$fs = get_file_storage();
$context = context_module::instance($cmid);//과제 모듈 context 가져옴
$files = $fs->get_area_files($context->id, 'assignsubmission_file', 'submission_files', $id, 'timemodified', false);
?>       
<?php
$output = '';
foreach ($files as $file) {
    $filename = $file->get_filename();
    $mimetype = $file->get_mimetype();
    $iconimage = '<img src="' . $OUTPUT->pix_url(file_mimetype_icon($mimetype)) . '" class="icon" alt="' . $mimetype . '" />';
    $icon = "<a href=\"$path\">$iconimage</a> ";
    $path = file_encode_url($CFG->wwwroot . '/pluginfile.php', '/' . $context->id . '/assignsubmission_file/submission_files/' . $id . '/' . $filename);
    if ($file->get_filesize() > 0) {
        $attfile .= '<li>';
        $attfile .= "<a href=\"$path\">$iconimage</a> ";
        $attfile .= format_text("<a href=\"$path\">" . s($filename) . "</a>", FORMAT_HTML, array('context' => $context));
        $attfile .= '</li>';
        $fileobj = '<img class=\'small\' src="' . $path . '">';
    }
}
echo '<div>' . $attfile . '</div>';



echo $OUTPUT->footer();