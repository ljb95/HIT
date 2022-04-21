<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<?php
require_once dirname(dirname(dirname (__FILE__))).'/config.php';
    
// Check for valid admin user - no guest autologin
require_login(0, false);
if (isguestuser()) {
    $SESSION->wantsurl = (string)new moodle_url('/siteadmin/support/notices_submit.php');
    redirect(get_login_url());
}
$context = context_system::instance();
require_capability('moodle/site:config', $context);

require_once($CFG->libdir . '/datalib.php');

$id = optional_param('id', date('Y'), PARAM_INT);
$year = optional_param('year', date('Y'), PARAM_INT);
$univ = optional_param('univ', 2, PARAM_TEXT);
$hyear = optional_param('hyear', 1, PARAM_TEXT);

$DB->delete_records('lmsdata_haksa_schedule', array('id' => $id));
?>
<body onload="delete_submit()">
<form id="schedule_delete" name="schedule_delete" action="haksa_schedule.php" method="post">
    <input type="hidden" name="year" value="<?php echo $year;?>">
    <input type="hidden" name="univ" value="<?php echo $univ;?>">
    <input type="hidden" name="hyear" value="<?php echo $hyear;?>">
    <input type="hidden" name="mode" value="1">
</form>
</body>
<script type="text/javascript">
    function delete_submit(){
        sd = document.getElementById('schedule_delete');
        sd.submit();
    };
</script>