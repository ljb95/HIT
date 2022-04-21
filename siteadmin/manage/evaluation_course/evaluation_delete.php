<?php

require_once dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php';
require_once dirname(dirname(dirname(__FILE__))) . '/lib/paging.php';
require_once dirname(dirname(dirname(__FILE__))) . '/lib.php';

// Check for valid admin user - no guest autologin

require_login(0, false);
if (isguestuser()) {
    $SESSION->wantsurl = (string) new moodle_url('/siteadmin/manage/evaluation/evaluation_add.php');
    redirect(get_login_url());
}

$id = required_param('id', PARAM_INT);

$DB->delete_records('lmsdata_evaluation',array('id'=>$id));
?>
<script>
    location.href='evaluation_list.php';
</script>