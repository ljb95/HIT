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

$id = optional_param('id', 0, PARAM_INT);
$type = optional_param('type', 5, PARAM_INT);
$title = optional_param('title', 0, PARAM_TEXT);
$email = optional_param('email', 0, PARAM_TEXT);
$contents = optional_param('editor', 0, PARAM_TEXT);
$mod = optional_param('mod', "", PARAM_TEXT);
$file_id = optional_param('file_id', 0, PARAM_INT);
$file_del = optional_param('file_del', 0, PARAM_INT);

global $DB, $USER;
$board = $DB->get_record('jinoboard',array('type'=>$type));

        
    $newdata = new stdClass();

    $newdata->board = $board->id;
    $newdata->userid = $USER->id;
    $newdata->title = $title;
    $newdata->category = 0;
    $newdata->contents = $contents;
    $newdata->email = $email;
    $newdata->isnotice = 0;
    $newdata->ispush = 0;
    $newdata->issecret = 0;
    $newdata->lev = 0;
    $newdata->viewcnt = 0;

if($mod == "reply"){
    $ref = $DB->get_record("jinoboard_contents", array("id"=>$id));
    $newdata->ref = $ref->ref;
    $newdata->step = $ref->step+1;
    $newdata->lev = $ref->lev+1;
    $newdata->timecreated = time();
    $newdata->timemodified = time();
    
    $adminuser = $DB->get_record('user',array('id'=>2));
    email_to_user($adminuser,$USER,$newdata->title,$newdata->contents,$newdata->contents);
    
    $DB->execute("update {jinoboard_contents} set step = step + 1 where ref = ? and step > ?", array($ref->ref, $ref->step));
    
    $itemid = $DB->insert_record('jinoboard_contents', $newdata);
} else if($mod == 'edit' && !empty($id)){
    $newdata->id = $id;
    $newdata->timemodified = time();
    $newdata->ref = $id;
    $DB->update_record('jinoboard_contents', $newdata);
    $itemid = $id;
} else {
    $newdata->ref = 0;
    $newdata->step = 0;
    $newdata->timecreated = time();
    $newdata->timemodified = time();
    $itemid = $DB->insert_record('jinoboard_contents', $newdata);
    $newdata->id = $itemid;
    $newdata->ref = $itemid;
    $DB->update_record('jinoboard_contents', $newdata);
}

    if($file_id != 0){
        $context = context_system::instance();

        $fs = get_file_storage();
        
        if(!empty($_FILES['uploadfile']['tmp_name'])) $file_del = 1;

        if($mod == 'edit' && !empty($id) && $file_del == 1){
            $overlap_files = $DB->get_records('files', array('itemid'=> $itemid));
            foreach($overlap_files as $file){
                $fs->get_file_instance($file)->delete();
            }
        }
        
        if(!empty($_FILES['uploadfile']['tmp_name'])){

            $file_record = array(
                                'contextid'   => $context->id,
                                'component'   => 'local_jinoboard',
                                'filearea'    => 'attachment',
                                'itemid'      => $itemid,
                                'filepath'    => '/',
                                'filename'    => $_FILES['uploadfile']['name'],
                                'timecreated' => time(),
                                'timemodified'=> time(),
                                'userid'      => $USER->id,
                                'author'      => fullname($USER),
                                'license'     => 'allrightsreserved',
                                'sortorder'   => 0
                            );

            $storage_id = $fs->create_file_from_pathname($file_record, $_FILES['uploadfile']['tmp_name']);
        }
    }
?>
<script>
    window.onload = function(){
        location.href = '<?php echo $CFG->wwwroot;?>/siteadmin/support/qna.php'
    }
</script>