<?php
require('../../config.php');
require_once($CFG->dirroot . "/local/lmsdata/lib.php");
require_once($CFG->dirroot . '/local/lmsdata/edit_form.php');
require_once($CFG->dirroot . '/user/profile/lib.php');
require($CFG->dirroot . '/user/editlib.php');
require($CFG->dirroot . '/user/lib.php');

$type = optional_param('type', "change", PARAM_TEXT);
$edit = optional_param('edit', false, PARAM_BOOL);
$id = optional_param('id', 0, PARAM_INT);

if (!is_siteadmin() && $USER->id != $id) {
    print_error('cannotedityourprofile');
}
if (!empty($id)) {
    $userid = $id;
} else {
    $userid = $USER->id;
}
$sql = ' SELECT u.* '
        . ', yu.userid , yu.eng_name , yu.usergroup , yu.b_temp, yu.b_mobile , yu.b_email, yu.univ,yu.major,yu.b_tel,yu.b_univ,yu.b_major,yu.ehks,yu.edhs,yu.domain,yu.hyhg,yu.persg,yu.psosok,yu.sex '
        . ' FROM {user} u '
        . ' LEFT JOIN {lmsdata_user} yu on u.id = yu.userid '
        . ' WHERE u.id = :id';
$user = $DB->get_record_sql($sql, array('id' => $userid));

$personalcontext = context_user::instance($user->id);

$PAGE->set_url('/local/lmsdata/user_info_edit.php', array(
    'id' => $userid));
$PAGE->set_pagelayout('standard');
$PAGE->set_context($personalcontext);
// Print the header
$strplural = get_string('personal_information_change', 'local_lmsdata');

$PAGE->navbar->add(get_string('title:mypage', 'local_lmsdata'));
$PAGE->navbar->add($strplural, new moodle_url('/local/lmsdata/user_info.php'));

$PAGE->set_title(get_string('personal_information_manage', 'local_lmsdata'));
$PAGE->set_heading($strplural);

echo $OUTPUT->header();

$usercase = get_usercase($userid);

//tab
$row[] = new tabobject('info', "$CFG->wwwroot/local/lmsdata/user_info.php?id=" . $userid, get_string('personal_information', 'local_lmsdata'));
if ($USER->id == $userid || is_siteadmin($USER->id)) {
    $row[] = new tabobject('change', "$CFG->wwwroot/local/lmsdata/user_info_edit.php?id=" . $userid, get_string('personal_information_change', 'local_lmsdata'));
    if ($usercase == 'temp') {
        $row[] = new tabobject('pass', "$CFG->wwwroot/local/lmsdata/user_password.php?id=" . $userid, get_string('password_change', 'local_lmsdata'));
    }
    $row[] = new tabobject('message', "$CFG->wwwroot/local/lmsdata/user_message_edit.php?id=" . $userid, get_string('personal_message_change', 'local_lmsdata'));
}
$rows[] = $row;
// Prepare the editor and create form.
$editoroptions = array(
    'maxfiles' => EDITOR_UNLIMITED_FILES,
    'maxbytes' => $CFG->maxbytes,
    'trusttext' => false,
    'forcehttps' => false,
    'context' => $personalcontext
);
$user = file_prepare_standard_editor($user, 'picture', $editoroptions, $personalcontext, 'user', 'draft');
// Prepare filemanager draft area.
$draftitemid = 0;
$filemanagercontext = $editoroptions['context'];
$filemanageroptions = array('maxbytes' => $CFG->maxbytes,
    'subdirs' => 0,
    'maxfiles' => 1,
    'accepted_types' => 'web_image');
file_prepare_draft_area($draftitemid, $filemanagercontext->id, 'user', 'newicon', 0, $filemanageroptions);
$user->imagefile = $draftitemid;
// Create form.
$userform = new user_info_edit_form(null, array(
    'editoroptions' => $editoroptions,
    'filemanageroptions' => $filemanageroptions,
    'userid' => $userid,
    'description' => $user->description
        ));
$user->id = $userid;
$userform->set_data($user);

if ($edit) {
    $usernew = $userform->get_data();
    $usernew->id = $usernew->id;
    $usernew->timemodified = time();
    $usernew->description = $usernew->description['text'];
    $usernew->interests = Array();

    $sso_user = new stdClass();
    $sso_user = $DB->get_record('lmsdata_user', array('userid' => $usernew->id));

    $sso_user->email = $usernew->email;
    $sso_user->b_email = $usernew->maildisplay;
    $sso_user->b_tel = $usernew->b_tel;
    $sso_user->b_mobile = $usernew->b_mobile;
    $sso_user->b_univ = $usernew->b_univ;
    $sso_user->b_major = $usernew->b_major;
    $DB->update_record("lmsdata_user", $sso_user);

    $authplugin = get_auth_plugin($user->auth);

    // Description editor element may not exist!
    if (isset($usernew->description_editor)) {
        $usernew = file_postupdate_standard_editor($usernew, 'description', $editoroptions, $personalcontext, 'user', 'profile', 0);
    }

    // Pass a true old $user here.
    if (!$authplugin->user_update($user, $usernew)) {
        // Auth update failed.
        print_error('cannotupdateprofile');
    }

    // Update user with new profile data.
    user_update_user($usernew, false, false);

    // Update preferences.
    useredit_update_user_preference($usernew);

    useredit_update_picture($usernew, $userform, $filemanageroptions);

    // Update mail bounces.
    useredit_update_bounces($user, $usernew);

    // Update forum track preference.
    useredit_update_trackforums($user, $usernew);

    // Save custom profile fields data.
    profile_save_data($usernew);

    // Trigger event.
    \core\event\user_updated::create_from_userid($user->id)->trigger();
    $user = $DB->get_record('user', array('id' => $user->id), '*', MUST_EXIST);
    if ($USER->id == $user->id) {
        // Override old $USER session variable if needed.
        foreach ((array) $user as $variable => $value) {
            if ($variable === 'description' or $variable === 'password') {
                // These are not set for security nad perf reasons.
                continue;
            }
            $USER->$variable = $value;
        }
        // Preload custom fields.
        profile_load_custom_fields($USER);
    }
    redirect('user_info.php');
}
?>

<div id="tab_area">
    <?php
    print_tabs($rows, $type);
    ?>
    <div id="tab">
        <h3 class="tab_title" style="text-align:left"><?php echo fullname($user); ?></h3>
        <?php
        $userform->display();
        ?>
    </div> <!-- Tab End -->
</div> <!-- Tab Area end -->

<?php
echo $OUTPUT->footer();
