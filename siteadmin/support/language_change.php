<?php
require(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once dirname(dirname(__FILE__)) . '/lib.php';

// Check for valid admin user - no guest autologin
require_login(0, false);
if (isguestuser()) {
    $SESSION->wantsurl = (string) new moodle_url('/siteadmin/support/notices.php');
    redirect(get_login_url());
}
$context = context_system::instance();
require_capability('moodle/site:config', $context);

require_once $CFG->dirroot . '/local/jinoboard/lib.php';
require_once dirname(dirname(__FILE__)) . '/lib/paging.php';

$year = optional_param('year', date("Y"), PARAM_INT);
$term = optional_param('term', 0, PARAM_INT);
$mode = optional_param('mode', 'list', PARAM_INT);

// 현재 년도, 학기
if (!$year) {
    $year = get_config('moodle', 'haxa_year');
}
if (empty($year)) {
    $year = date('Y');
}
if (!$term) {
    $term = get_config('moodle', 'haxa_term');
}
$startyear = 2016; //시스템 시작 년도(siteadmin/lib.php에 선언)

$PAGE->set_context($context);
$PAGE->set_url('/local/jinoboard/index.php');

$page_params = array();
$params = array(
    'year' => $year,
    'term' => $term
);

$dates = $DB->get_records('lmsdata_trust', array('year' => $year, 'term' => $term),'id asc');
?>
<?php include_once('../inc/header.php'); ?>
<div id="contents">
    <?php include_once('../inc/sidebar_support.php'); ?>
    
 <div id="content" style=" top:-50px; left:-10px;">
  <iframe src="/admin/tool/customlang/index.php" scrolling="yes"  border="0" frameborder="0" allowTransparency="true" style="min-height: 700px; height:100%; width:100%;"></iframe>
  
 </div>
</div>

<script>

</script>
<?php include_once('../inc/footer.php'); ?>