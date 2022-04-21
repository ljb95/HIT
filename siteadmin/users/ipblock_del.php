<?php
require_once dirname(dirname(dirname(__FILE__))) . '/config.php';
require_once dirname(dirname(__FILE__)) . '/lib.php';

// Check for valid admin user - no guest autologin
require_login(0, false);
if (isguestuser()) {
    $SESSION->wantsurl = (string) new moodle_url('/siteadmin/users/info.php');
    redirect(get_login_url());
}
$context = context_system::instance();
require_capability('moodle/site:config', $context);

$id     = required_param('id', PARAM_INT);      // <p>ipblock pk id</p>

$DB->delete_records('ipblock',array('id'=>$id));
?>
<script type="text/javascript">
    location.href='ipblock.php';
</script>