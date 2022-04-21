<?php

require(dirname(dirname(dirname(__FILE__))) . '/config.php');
$id = optional_param('id', 0, PARAM_INT);
$context = get_context_instance(CONTEXT_COURSE, $id);

require_login();

$PAGE->set_context($context);
$PAGE->set_pagelayout('incourse');
$PAGE->set_title('공개된 과제');
$PAGE->navbar->add("과제 공개 목록");
$PAGE->set_url(new moodle_url('/local/lmsdata/assign_share_list.php'));

$sql = 'select cm.id as cmid, ag.id as aid, a.name, lu.userid, lu.eng_name from {assign} a 
join {assign_submission} ag on a.id = ag.assignment 
join {course_modules} cm on cm.instance = a.id and cm.course = a.course 
join {lmsdata_user} lu on lu.userid = ag.userid 
join {assign_share} asss on asss.assignid = cm.id and asss.userid = ag.userid 
where cm.module = 1 and a.course = :course and asss.shared = 1';
$assigns = $DB->get_records_sql($sql, array('course' => $id));

echo $OUTPUT->header();

if($assigns){
    foreach ($assigns as $assign) {
    echo '<table class="generaltable">'
    . '<tr><th>과제명</th><th style="width:20%">제출자</th><th style="width:5%">과제 보기</th></tr><tr><td>' . $assign->name . '</td><td>' . $assign->eng_name . '</td><td><a href="/local/lmsdata/assign_shared.php?cmid=' . $assign->cmid . '&aid=' . $assign->aid . '&userid=' . $assign->userid . '" target="_blank"><input type="button" value="보기"></a></td></tr></table>';
}
}else{
    echo "공개된 과제가 없습니다.";
}


echo $OUTPUT->footer();
