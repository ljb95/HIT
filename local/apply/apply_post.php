<?php

require_once(dirname(__FILE__) . '/../../config.php');

$id = required_param('courseid', PARAM_INT);
$phone = required_param('phone', PARAM_RAW);
$role = required_param('role', PARAM_RAW);
$apply_reason = required_param('apply_reason', PARAM_TEXT);
require_login();


$app = new stdClass();
$app->apply_date = time();
$app->phone = $phone;
$app->userid = $USER->id;
$app->courseid = $id;
$app->application_type = $role;
$app->approval_status = 0;
$app->approver = 0;
$app->unapprove_reason = '';
$app->apply_reason = $apply_reason;
$app->processing_date = 0;


$DB->insert_record('approval_reason', $app);

?>
<script type="text/javascript">
    alert('신청되었습니다.');
    location.href='apply.php';
</script>