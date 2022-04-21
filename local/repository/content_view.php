<?php
require(dirname(dirname(dirname(__FILE__))) . '/config.php');

$type = optional_param('type', 1, PARAM_INT); // 1 = 나의 콘텐츠 2 공유받은 콘텐츠 3 나의 콘텐츠 파일 4 공유받은 콘텐츠 파일;

$context = context_system::instance();

require_login();

$PAGE->set_context($context);
$PAGE->set_url('/local/repository/content_view.php', array('type' => $type));
$PAGE->set_pagelayout('standard');

$strplural = get_string("cdms", "local_repository");
$PAGE->navbar->add($strplural);
$PAGE->set_title($strplural);
$PAGE->set_heading($strplural);


echo $OUTPUT->header();
?>
