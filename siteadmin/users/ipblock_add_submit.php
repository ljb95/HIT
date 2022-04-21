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
$ip1 = required_param('ip1', PARAM_INT);
$ip2 = optional_param('ip2','*', PARAM_RAW);
$ip3 = optional_param('ip3','*', PARAM_RAW);
$ip4 = optional_param('ip4','*', PARAM_RAW);

$ip2 = ($ip2)?$ip2:'*';
$ip3 = ($ip3)?$ip3:'*';
$ip4 = ($ip4)?$ip4:'*';


$ip = $ip1.'.'.$ip2.'.'.$ip3.'.'.$ip4;
echo $ip.'<br>';

if((is_number($ip3) || is_number($ip4)) && $ip2 == '*'){
?>
<script type="text/javascript">
    alert('0.*.0.* 처럼 사용 할 수 없습니다. 0.0.*.* 로 입력해주세요.');
    location.href='ipblock.php';
</script>
<?php
} else if(is_number($ip4) && $ip3 == '*'){
?>
<script type="text/javascript">
    alert('0.0.*.0 처럼 사용 할 수 없습니다. 0.0.0.* 로 입력해주세요.');
    location.href='ipblock.php';
</script>
<?php 
}
die();
$description = trim(optional_param('description','', PARAM_TEXT));
$isused = required_param('isused', PARAM_INT);

$ip = $ip1.'.'.$ip2.'.'.$ip3.'.'.$ip4;

if(!$id && !$ipblock = $DB->get_record('ipblock',array('ip'=>$ip))){
    $data = new stdClass();
    $data->ip = $ip;
    $data->description = $description;
    $data->userid = $USER->id;
    $data->isused = $isused;
    $data->timecreated = time();
    $newid = $DB->insert_record('ipblock',$data);
} else if($id && $ipblock = $DB->get_record('ipblock',array('id'=>$id))){
    $data = new stdClass();
    $data->id = $id;
    $data->description = $description;
    $data->userid = $USER->id;
    $data->isused = $isused;
    $data->timemodified = time();
    $newid = $DB->update_record('ipblock',$data);
} else {
    ?>
<script type="text/javascript">
    alert('이미 등록된 IP입니다.');
    location.href='ipblock.php';
</script>
<?php
}
?>
<script type="text/javascript">
    location.href='ipblock.php';
</script>