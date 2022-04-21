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

    require_once $CFG->dirroot . '/local/jinoboard/lib.php';
    require_once($CFG->libdir . '/filestorage/file_storage.php');
    require_once($CFG->libdir . '/filestorage/stored_file.php');
    require_once($CFG->libdir . '/filelib.php');
    require_once($CFG->libdir . '/datalib.php');
    
    $id = optional_param('id', 0, PARAM_INT);
    $mod = optional_param('mod', "", PARAM_TEXT);
    $isnotice = optional_param('isnotice', false, PARAM_BOOL);
    $ispush = optional_param('ispush', false, PARAM_BOOL);
    $title = required_param('title', PARAM_TEXT);
    $contents = optional_param('editor', "", PARAM_RAW);
    $targets = optional_param_array('targets', "", PARAM_RAW);
    $timestart = optional_param('timestart', null, PARAM_ALPHANUMEXT);
    $timeend = optional_param('timeend', null, PARAM_ALPHANUMEXT);
    $board = optional_param('type', 1 , PARAM_INT);
    $file_id = optional_param('file_id', 0, PARAM_INT);
    $file_del = optional_param('file_del', 0, PARAM_INT);
    $alltargets = optional_param('alltargets', 0 , PARAM_INT);
    
    if($targets){
        $targets = implode(',',$targets);
    }
    
    $newdata = new object();
    
    $newdata->board = $board;
    $newdata->userid = $USER->id;
    $newdata->title = $title;
    $newdata->category = 0;
    $newdata->contents = $contents;
    $newdata->targets = $targets;
    $newdata->isnotice = 0;
    $newdata->ispush = 0;
    $newdata->issecret = 0;
    $newdata->step = 0;
    $newdata->lev = 0;
   
    if($isnotice){
        $newdata->isnotice = 1;
    }
    
    if($ispush){
        $newdata->ispush = 1;
    }
    
    if(!empty($timestart)) {
        $newdata->timestart = strtotime($timestart);
    }
    
    if(!empty($timeend)) {
        $newdata->timeend = strtotime($timeend);
    }

    
    $itemid;
    
    if($mod == "reply"){
        $ref = $DB->get_record("jinoboard_contents", array("id"=>$id));
        $newdata->ref = $ref->ref;
        $newdata->step = $ref->step+1;
        $newdata->lev = $ref->lev+1;
        $newdata->timecreated = time();
        $newdata->timemodified = time();
        $newdata->viewcnt = 0;
        $newdata->contents = $contents;
        
        $itemid = $DB->insert_record('jinoboard_contents', $newdata);
    }else if($mod == 'edit' && !empty($id)){
        $newdata->id = $id;
        $newdata->timemodified = time();
        $newdata->ref = $id;
        $DB->update_record('jinoboard_contents', $newdata);
        $itemid = $id;
    }else{
        $newdata->viewcnt = 0;
        $newdata->timecreated = time();
        $newdata->timemodified = time();
        $newdata->ref = 0;
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
     
     if($ispush == 1){
        if($targets){
            $targets = explode(',',$targets);
        }
        $userfrom = $DB->get_record('user',array('id' => $USER->id));
        $where = '';
        foreach ($targets as $target){
            if($alltargets == 1){
                $where = '';
            } else if($target == get_string('teacher', 'local_lmsdata')){
                $where = " WHERE lu.usergroup = 'pr'";
            } else if(strpos($target,'p') !== false){
                $univ = 1;
                $hyear = substr($target,1,1);
                $where = " WHERE lu.usergroup = 'rs' and lu.univ = ".$univ." and lu.hyear = ".$hyear;
            } else if(strpos($target,'m') !== false){
                $univ = 2;
                $hyear = substr($target,1,1);
                $where = " WHERE lu.usergroup = 'rs' and lu.univ = ".$univ." and lu.hyear = ".$hyear;
            }
            $sql = "SELECT u.*, lu.hyear, lu.usergroup
                    FROM {lmsdata_user} lu
                    JOIN {user} u ON lu.userid = u.id
                    ".$where;
            $usertolist = $DB->get_records_sql($sql);
            foreach($usertolist as $userto){
                $postsubject = '[YES2.0] <?php echo get_string('notice','local_lmsdata'); ?> 알림';
                $posttext = $title.'이(가) 업데이트 되었습니다.
                    
링크 : '.$CFG->wwwroot.'/local/jinoboard/detail.php?id='.$itemid.'&type=1';
                $posthtml = '';
                $eventdata = new object();
                $eventdata->component         = 'moodle';    // the component sending the message. Along with name this must exist in the table message_providers
                $eventdata->name              = 'instantmessage';        // type of message from that module (as module defines it). Along with component this must exist in the table message_providers
                $eventdata->userfrom          = $userfrom;      // user object
                $eventdata->userto            = $userto;        // user object
                $eventdata->subject           = $postsubject;   // very short one-line subject
                $eventdata->fullmessage       = $posttext;      // raw text
                $eventdata->fullmessageformat = FORMAT_PLAIN;   // text format
                $eventdata->fullmessagehtml   = $posthtml;      // html rendered version
                $eventdata->smallmessage      = $postsubject;             // useful for plugins like sms or twitter
                $eventdata->notification      = 1;
                
                message_send($eventdata);
            }
            if($alltargets == 1){
                break;
            }
        }
    }
    
    
//    switch($board){
//        case 1 : redirect($SITECFG->wwwroot.'./notices.php'); break;
//        case 2 : redirect($SITECFG->wwwroot.'./qna.php'); break;
//        case 3 : redirect($SITECFG->wwwroot.'./guide_faq.php'); break;
//        case 4 : redirect($SITECFG->wwwroot.'./guide_manual.php'); break;
//        
//    }
    
?>
<script>
    window.onload = function(){
        location.href = '<?php echo $CFG->wwwroot;?>/siteadmin/support/notices.php'
    }
</script>