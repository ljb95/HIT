<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<?php
require_once dirname(dirname(dirname (__FILE__))).'/config.php';
require_once($CFG->libdir.'/gdlib.php');
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->dirroot.'/user/editlib.php');
require_once($CFG->dirroot.'/user/profile/lib.php');
require_once($CFG->dirroot.'/user/lib.php');
require_once($CFG->dirroot.'/lib/gdlib.php');
require_once($CFG->dirroot.'/lib/filelib.php');
require_once($CFG->libdir . '/filestorage/file_storage.php');
require_once($CFG->libdir . '/filestorage/stored_file.php');
    
// Check for valid admin user - no guest autologin
require_login(0, false);
if (isguestuser()) {
    $SESSION->wantsurl = (string)new moodle_url('/siteadmin/support/inftemp_submit.php');
    redirect(get_login_url());
}
$context = context_system::instance();
require_capability('moodle/site:config', $context);

require_once($CFG->libdir . '/datalib.php');

$mod = optional_param("mod", "write", PARAM_TEXT);
$id = optional_param("id", 0, PARAM_INT);
$file_id = optional_param("file_id", 0, PARAM_INT);
$file_del = optional_param("file_del", 0, PARAM_INT);
$itemid = optional_param("itemid", 0, PARAM_INT);
$password_change = optional_param('pw_edit',false, PARAM_BOOL);

if($mod == 'suspended'){
    $user_chk = optional_param_array('user_chk', 0, PARAM_INT);
    $suspended = optional_param("suspended", 0, PARAM_INT);
    foreach($user_chk as $user){
        $suser = new stdClass();
        $suser->id = $user;
        $suser->suspended = $suspended;
        $DB->update_record('user', $suser);
    }
    echo '<script type="text/javascript">document.location.href="./inftemp.php"</script>';
}

if($mod == 'delete'){
    $user = $DB->get_record('user',array('id'=>$id));
    delete_user($user);
    echo '<script type="text/javascript">document.location.href="./inftemp.php"</script>';
}

$usercreated = false;
$usernew = new stdClass();
$usernew->auth = 'manual';
$usernew->timemodified = time();
$usernew->lang = "ko";

if($password_change == 1){
    $usernew->password = trim(optional_param('password', ' ', PARAM_RAW));
    $usernew->password = hash_internal_user_password($usernew->password);
}
$usernew->email = trim(optional_param('email', ' ', PARAM_RAW));
$usernew->firstname = trim(optional_param('firstname', ' ', PARAM_RAW));
$usernew->phone2 = optional_param('phone', ' ', PARAM_RAW);
$usernew->lastname = trim(optional_param('lastname', ' ', PARAM_RAW));
$usernew->mailedisplay = 0;
$usernew->suspended = optional_param('suspended', ' ', PARAM_INT);
$usergroup = optional_param('usergroup', 'rs', PARAM_RAW);

$usernew->mnethostid = $CFG->mnet_localhost_id; // Always local user.
$usernew->confirmed  = 1;
$usernew->timecreated = time();

if ($mod == 'write') {
    
    $usernamefield = optional_param('userid', false, PARAM_RAW);
    $usernew->username = trim(core_text::strtolower($usernamefield));   
    
    $authplugin = get_auth_plugin($usernew->auth);
    $usernew->id = user_create_user($usernew, false, false);
    
    $filename = $_FILES['uploadfile']['name'];
    $filepath = $_FILES['uploadfile']['tmp_name'];

    $ysuser = new stdClass();
    $ysuser->userid = $usernew->id;
    $ysuser->eng_name = $usernew->firstname.$usernew->lastname;
    $ysuser->psosok = optional_param('psosok', ' ', PARAM_RAW);
    $ysuser->usergroup = $usergroup;
    $ysuser->b_temp = 1;
    $ysuser->b_mobile = 0;
    $ysuser->b_email = 0;
    $ysuser->b_tel = 0;
    $ysuser->b_univ = 0;
    $ysuser->b_major = 0;

    $DB->insert_record('lmsdata_user', $ysuser);

    if (!$authplugin->is_internal() and $authplugin->can_change_password() and !empty($usernew->password)) {
        if (!$authplugin->user_update_password($usernew, $usernew->password)) {
            // Do not stop here, we need to finish user creation.
            debugging(get_string('cannotupdatepasswordonextauth', '', '', $usernew->auth), DEBUG_NONE);
        }
    }
    
    $usercreated = true;

    $usercontext = context_user::instance($usernew->id);

    // Update preferences.
    useredit_update_user_preference($usernew);

    // Save custom profile fields data.
    profile_save_data($usernew);
    
    //save user picture
    if(!empty($_FILES['uploadfile']['tmp_name'])){
    $draftitemid = file_get_unused_draft_itemid();
    $filerecord = array(
        'contextid' => $usercontext->id,
        'component' => 'user',
        'filearea'  => 'draft',
        'itemid'    => $draftitemid,
        'filepath'  => '/',
        'filename'  => $filename,
        'userid' => $usernew->id,
        'license' => 'allrightsreserved'
    );

    $fs = get_file_storage();
    $file = $fs->create_file_from_pathname($filerecord, $filepath);

    $newrev = process_new_icon($usercontext, 'user', 'icon', 0, $filepath);
    
    $DB->set_field('user', 'picture', $newrev, array('id'=>$usernew->id));
    }
    // Reload from db.
    $usernew = $DB->get_record('user', array('id' => $usernew->id));

    // Trigger update/create event, after all fields are stored.
    \core\event\user_created::create_from_userid($usernew->id)->trigger();
    
}
if($mod == 'edit'){
    $user = $DB->get_record('user', array('id' => $id), '*', MUST_EXIST);
    $PAGE->set_context(context_user::instance($user->id));
    
    $usercontext = context_user::instance($user->id);
    
    $authplugin = get_auth_plugin($usernew->auth);
    $usernew->id = $id; 
    
    user_update_user($usernew, false, false);
    
    $ysuser = new stdClass();
    $ysuser = $DB->get_record_sql('select * from {lmsdata_user} where userid = :userid ', array('userid'=>$id));    
        
    
    $ysuser->userid = $usernew->id;
    $ysuser->eng_name = $usernew->firstname;
    $ysuser->psosok = optional_param('psosok', ' ', PARAM_RAW);
    $ysuser->usergroup = optional_param('usergroup', 'rs', PARAM_RAW);
    
    $DB->update_record('lmsdata_user', $ysuser);
    
    if (!$authplugin->is_internal() and $authplugin->can_change_password() and !empty($usernew->password)) {
        if (!$authplugin->user_update_password($usernew, $usernew->password)) {
            // Do not stop here, we need to finish user creation.
            debugging(get_string('cannotupdatepasswordonextauth', '', '', $usernew->auth), DEBUG_NONE);
        }
    }
    $usercreated = true;
    
    // Load user preferences.
    useredit_load_preferences($user);

    // Load custom profile fields data.
    profile_load_data($user);

    // Update mail bounces.
    useredit_update_bounces($user, $usernew);

    // Update forum track preference.
    useredit_update_trackforums($user, $usernew);

    if ($user->id == $USER->id) {
        // Override old $USER session variable.
        foreach ((array)$usernew as $variable => $value) {
            if ($variable === 'description' or $variable === 'password') {
                // These are not set for security nad perf reasons.
                continue;
            }
            $USER->$variable = $value;
        }
    }   
    
    if($file_id != 0){
            $context = context_system::instance();

            $fs = get_file_storage();

            if(!empty($_FILES['uploadfile']['tmp_name'])) $file_del = 1;

            if($mod == 'edit' && !empty($id) && $file_del == 1){
                $overlap_files = $DB->get_records('files', array('itemid'=> $file_id));
                foreach($overlap_files as $file){
                    $fs->get_file_instance($file)->delete();
                    $icon_files = $DB->get_records('files', array('contextid'=> $file->contextid, 'component'=>'user', 'filearea'=>'icon'));
                    foreach($icon_files as $ifile){
                        $fs->get_file_instance($ifile)->delete();
                    }
                }
            }

        if(!empty($_FILES['uploadfile']['tmp_name'])){

            //save user picture
            $filename = $_FILES['uploadfile']['name'];
            $filepath = $_FILES['uploadfile']['tmp_name'];

            $draftitemid = file_get_unused_draft_itemid();
            $filerecord = array(
                'contextid' => $usercontext->id,
                'component' => 'user',
                'filearea'  => 'draft',
                'itemid'    => $draftitemid,
                'filepath'  => '/',
                'filename'  => $filename,
                'userid' => $usernew->id,
                'license' => 'allrightsreserved'
            );

            $fs = get_file_storage();
            $file = $fs->create_file_from_pathname($filerecord, $filepath);

            $newrev = process_new_icon($usercontext, 'user', 'icon', 0, $filepath);

            $DB->set_field('user', 'picture', $newrev, array('id'=>$usernew->id));
        }
    }
}

echo '<script type="text/javascript">document.location.href="./inftemp_add.php?id='.$usernew->id.'&mod=edit"</script>';