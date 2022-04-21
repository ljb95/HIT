<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<?php
require_once dirname(dirname(dirname (__FILE__))).'/config.php';
    
// Check for valid admin user - no guest autologin
require_login(0, false);
if (isguestuser()) {
    $SESSION->wantsurl = (string)new moodle_url('/siteadmin/support/qna_submit.php');
    redirect(get_login_url());
}
$context = context_system::instance();
require_capability('moodle/site:config', $context);

require_once($CFG->libdir . '/datalib.php');

$check_list = optional_param_array('check_list', 0, PARAM_INT);

foreach ($check_list as $val){
    $DB->delete_records('jinoboard_contents', array('id'=>$val));

    $fs = get_file_storage();
    $overlap_files = $DB->get_records('files', array('itemid'=> $val));
    foreach($overlap_files as $file){
        $fs->get_file_instance($file)->delete();
    }
}

//    $r    = optional_param_array('data',array(), PARAM_INT);    
//    foreach($r as  $val){    
//       $DB->delete_records('jinoboard_contents', array('ref'=>$val));
//       
//       $fs = get_file_storage();
//       $overlap_files = $DB->get_records('files', array('itemid'=> $val));
//          foreach($overlap_files as $file){
//              $fs->get_file_instance($file)->delete();
//          }
//    }
?>
<script>
    window.onload = function(){
        location.href = '<?php echo $CFG->wwwroot;?>/siteadmin/support/qna.php'
    }
</script>