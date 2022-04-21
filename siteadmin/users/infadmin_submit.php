<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<?php
require_once dirname(dirname(dirname(__FILE__))) . '/config.php';
require_once($CFG->libdir . '/gdlib.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->dirroot . '/user/editlib.php');
require_once($CFG->dirroot . '/user/profile/lib.php');
require_once($CFG->dirroot . '/user/lib.php');
require_once($CFG->dirroot . '/lib/gdlib.php');
require_once($CFG->dirroot . '/lib/filelib.php');
require_once($CFG->libdir . '/filestorage/file_storage.php');
require_once($CFG->libdir . '/filestorage/stored_file.php');
// Check for valid admin user - no guest autologin
require_login(0, false);
if (isguestuser()) {
    $SESSION->wantsurl = (string) new moodle_url('/siteadmin/support/infadmin_submit.php');
    redirect(get_login_url());
}
$context = context_system::instance();
require_capability('moodle/site:config', $context);

require_once($CFG->libdir . '/datalib.php');

$mod = optional_param("mod", "write", PARAM_TEXT);
$id = optional_param("id", 0, PARAM_INT);
$file_id = optional_param("file_id", 0, PARAM_INT);
$file_del = optional_param("file_del", 0, PARAM_INT);

$usergroup = optional_param('usergroup', 'de', PARAM_RAW);

$itemid = optional_param("itemid", 0, PARAM_INT);

//$usercontext = context_user::instance($id);
//print_object($usercontext);
//print_object($_FILES);
//die();

if ($mod == 'delete') {
    $admins = array();
    foreach (explode(',', $CFG->siteadmins) as $admin) {
        $admin = (int) $admin;
        if ($admin) {
            $admins[$admin] = $admin;
        }
    }
    unset($admins[$id]);
    set_config('siteadmins', implode(',', $admins));
    
    $lmsdata_user = $DB->get_record('lmsdata_user',array('userid'=>$id));
    if($lmsdata_user->usergroup == 'pr'){
        redirect('infadmin.php');
        exit;
    } else { 
        $user = $DB->get_record('user', array('id' => $id));
        delete_user($user);
        redirect('infadmin.php');
        exit;
    }
}

$usercreated = false;
$usernew = new stdClass();
$usernew->auth = 'manual';
$usernew->timemodified = time();
$usernew->lang = "ko";


$usernew->email = trim(optional_param('email', ' ', PARAM_RAW));
$usernew->firstname = trim(optional_param('username', ' ', PARAM_RAW));
$usernew->phone1 = optional_param('phone', ' ', PARAM_RAW);
$usernew->lastname = ' ';
$usernew->mailedisplay = 0;

$usernew->mnethostid = $CFG->mnet_localhost_id; // Always local user.
$usernew->confirmed = 1;
$usernew->timecreated = time();

$add_type = optional_param('add_type', 0, PARAM_INT);
if ($mod == 'write' && $add_type == 0) {
    $usernamefield = optional_param('userid', false, PARAM_RAW);
    $usernew->username = trim(core_text::strtolower($usernamefield));
    $usernew->password = trim(optional_param('password', ' ', PARAM_RAW));
    $usernew->password = hash_internal_user_password($usernew->password);

    $authplugin = get_auth_plugin($usernew->auth);
    $usernew->id = user_create_user($usernew, false, false);

    $filename = $_FILES['uploadfile']['name'];
    $filepath = $_FILES['uploadfile']['tmp_name'];

    $lmsuser = new stdClass();
    $lmsuser->userid = $usernew->id;
    $lmsuser->eng_name = $usernew->firstname;
    $lmsuser->psosok = optional_param('psosok', ' ', PARAM_RAW);
    $usergroup = optional_param('usergroup', 'sa', PARAM_RAW);
    if ($usergroup == 'sa') {
        $lmsuser->usergroup = $usergroup;
    }

    $lmsuser->b_temp = 0;
    $lmsuser->b_mobile = 0;
    $lmsuser->b_email = 0;
    $lmsuser->b_tel = 0;
    $lmsuser->b_univ = 0;
    $lmsuser->b_major = 0;
    $lmsuser->domain = optional_param('dept_name', null, PARAM_RAW);

    $DB->insert_record('lmsdata_user', $lmsuser);

    if (!$authplugin->is_internal() and $authplugin->can_change_password() and ! empty($usernew->password)) {
        if (!$authplugin->user_update_password($usernew, $usernew->password)) {
            // Do not stop here, we need to finish user creation.
            debugging(get_string('cannotupdatepasswordonextauth', '', '', $usernew->auth), DEBUG_NONE);
        }
    }

    $admins = array();
    foreach (explode(',', $CFG->siteadmins) as $admin) {
        $admin = (int) $admin;
        if ($admin) {
            $admins[$admin] = $admin;
        }
    }
    $admins[$usernew->id] = $usernew->id;
    set_config('siteadmins', implode(',', $admins));

    $usercreated = true;

    $usercontext = context_user::instance($usernew->id);

    // Update preferences.
    useredit_update_user_preference($usernew);

    // Save custom profile fields data.
    profile_save_data($usernew);

    //save user picture
    if (!empty($_FILES['uploadfile']['tmp_name'])) {
        $draftitemid = file_get_unused_draft_itemid();
        $filerecord = array(
            'contextid' => $usercontext->id,
            'component' => 'user',
            'filearea' => 'draft',
            'itemid' => $draftitemid,
            'filepath' => '/',
            'filename' => $filename,
            'userid' => $usernew->id,
            'license' => 'allrightsreserved'
        );

        $fs = get_file_storage();
        $file = $fs->create_file_from_pathname($filerecord, $filepath);

        $newrev = process_new_icon($usercontext, 'user', 'icon', 0, $filepath);

        $DB->set_field('user', 'picture', $newrev, array('id' => $usernew->id));
    }
    // Trigger update/create event, after all fields are stored.
    \core\event\user_created::create_from_userid($usernew->id)->trigger();
} else if($mod == 'write'){
    $admins = array();
    $userid = required_param('prof_userid', PARAM_INT);
    $lmsdata_user = $DB->get_record('lmsdata_user',array('userid'=>$userid));
    $lmsdata_user->domain = optional_param('dept_name', null, PARAM_RAW);
    
    if($usergroup == 'ma'){
        $lmsdata_user->menu_auth = 9;
    } else {
        $lmsdata_user->menu_auth = 8;
    }
    
    $DB->update_record('lmsdata_user',$lmsdata_user);
    
    foreach (explode(',', $CFG->siteadmins) as $admin) {
        $admin = (int) $admin;
        if ($admin) {
            $admins[$admin] = $admin;
        }
    }
    $admins[$userid] = $userid;
    set_config('siteadmins', implode(',', $admins));
}
if ($mod == 'edit') {

    $password_change = optional_param('pw_edit', false, PARAM_BOOL);
    $user = $DB->get_record('user', array('id' => $id), '*', MUST_EXIST);
    $PAGE->set_context(context_user::instance($user->id));

    $usercontext = context_user::instance($user->id);

    if ($password_change) {
        $usernew->password = trim(optional_param('password', ' ', PARAM_RAW));
        $usernew->password = hash_internal_user_password($usernew->password);

        $authplugin = get_auth_plugin($usernew->auth);
        if (!$authplugin->is_internal() and $authplugin->can_change_password() and ! empty($usernew->password)) {
            if (!$authplugin->user_update_password($usernew, $usernew->password)) {
                // Do not stop here, we need to finish user creation.
                debugging(get_string('cannotupdatepasswordonextauth', '', '', $usernew->auth), DEBUG_NONE);
            }
        }
    }

    $usernew->id = $id;

    user_update_user($usernew, false, false);


    $lmsuser = $DB->get_record('lmsdata_user', array('userid' => $id));

    $lmsuser->eng_name = $usernew->firstname;
    if($usergroup == 'ma'){
        $lmsuser->menu_auth = 9;
    } else { 
        $lmsuser->menu_auth = 8;
    }
    
    $lmsuser->psosok = optional_param('psosok', ' ', PARAM_RAW);
    $lmsuser->domain = optional_param('dept_name', $lmsuser->domain, PARAM_RAW);
    if ($lmsuser->usergroup != 'pr' && ($usergroup == 'sa' || $usergroup == 'de')) {
        $lmsuser->usergroup = $usergroup;
    }
    $DB->update_record('lmsdata_user', $lmsuser);

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
        foreach ((array) $usernew as $variable => $value) {
            if ($variable === 'description' or $variable === 'password') {
                // These are not set for security nad perf reasons.
                continue;
            }
            $USER->$variable = $value;
        }
    }

    if ($file_id != 0) {
        $context = context_system::instance();

        $fs = get_file_storage();

        if (!empty($_FILES['uploadfile']['tmp_name']))
            $file_del = 1;

        if ($mod == 'edit' && !empty($id) && $file_del == 1) {
            $overlap_files = $DB->get_records('files', array('itemid' => $file_id));
            foreach ($overlap_files as $file) {
                $fs->get_file_instance($file)->delete();
                $icon_files = $DB->get_records('files', array('contextid' => $file->contextid, 'component' => 'user', 'filearea' => 'icon'));
                foreach ($icon_files as $ifile) {
                    $fs->get_file_instance($ifile)->delete();
                }
            }
        }

        if (!empty($_FILES['uploadfile']['tmp_name'])) {

            //save user picture
            $filename = $_FILES['uploadfile']['name'];
            $filepath = $_FILES['uploadfile']['tmp_name'];

            $draftitemid = file_get_unused_draft_itemid();
            $filerecord = array(
                'contextid' => $usercontext->id,
                'component' => 'user',
                'filearea' => 'draft',
                'itemid' => $draftitemid,
                'filepath' => '/',
                'filename' => $filename,
                'userid' => $usernew->id,
                'license' => 'allrightsreserved'
            );

            $fs = get_file_storage();
            $file = $fs->create_file_from_pathname($filerecord, $filepath);

            $newrev = process_new_icon($usercontext, 'user', 'icon', 0, $filepath);

            $DB->set_field('user', 'picture', $newrev, array('id' => $usernew->id));
        }
    }
    // Preload custom fields.
    profile_load_custom_fields($USER);
}
echo '<script type="text/javascript">document.location.href="./infadmin.php"</script>';
