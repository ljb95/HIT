<?php
require('../../config.php');
require_once($CFG->dirroot . "/local/lmsdata/lib.php");
require_once($CFG->dirroot . "/message/lib.php");

//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
$type = optional_param('type', "info", PARAM_TEXT);
$id = optional_param('id', 0, PARAM_INT);
require_login();


if (!empty($id)) {
    $userid = $id;
} else {
    $userid = $USER->id;
}

$usercase = get_usercase($userid);

$sql = ' SELECT u.*, yu.userid '
        . ', yu.eng_name , yu.usergroup , yu.b_temp, yu.b_mobile , yu.b_email, yu.univ,yu.major,yu.b_tel,yu.b_univ,yu.b_major,yu.ehks,yu.edhs,yu.domain,yu.hyhg,yu.persg,yu.psosok,yu.sex FROM {user} u ' .
        ' LEFT JOIN {lmsdata_user} yu on u.id = yu.userid ' .
        ' WHERE u.id = :id';
$userinfo = $DB->get_record_sql($sql, array('id' => $userid));
//$userinfo = $DB->get_record_sql('SELECT u.* FROM {user} u WHERE u.id = :userid', array('userid' => $userid));

$personalcontext = context_user::instance($userid);
// Print the header
$strplural = '';
if ($userid == $USER->id) {
    $strplural = get_string('personal_information_manage', 'local_lmsdata');
} else {
    $strplural = get_string('personal_information', 'local_lmsdata');
}

$PAGE->set_url('/local/lmsdata/user_info.php', array(
    'id' => $id));
$PAGE->set_pagelayout('standard');
$PAGE->set_context($personalcontext);

if ($userid == $USER->id) {
    $PAGE->navbar->add(get_string('title:mypage', 'local_lmsdata'));
    $PAGE->set_title(get_string('title:mypage', 'local_lmsdata'));
} else {
    $PAGE->navbar->add($strplural);
    $PAGE->set_title($strplural);
}

$PAGE->set_heading($strplural);

echo $OUTPUT->header();

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

$enrol_courses = enrol_get_my_courses_by_my_lmsdata(NULL, array(), 1, 1000, NULL, 'visible DESC,sortorder ASC', $userid);
?>

<div id="tab_area">
    <?php
    print_tabs($rows, $type);
    ?>
    <div id="tab">
        <table cellpadding="0" cellspacing="0" class="detail" width="100%">
            <tbody>
                <tr>
                    <td class="user_info_box" rowspan="8" colspan="2" >
                        <?php
                        echo '<div class="user_info_picture">';
                        echo $OUTPUT->user_picture($userinfo, array('size' => 170, 'alt' => 'User Profile'));
                        echo "<div>" . fullname($userinfo) . "</div>";
                        echo '</div>';
                        ?>
                    </td>
                </tr>
                <tr>
                    <td class="option" colspan="2">
                        <?php echo get_string("attach", 'local_lmsdata'); ?>
                    </td>
                </tr>
                <?php
                $usergroup = $DB->get_field('lmsdata_user', 'usergroup', array('userid' => $userid));
                ?>

                <tr>
                    <td class="option">
                        <?php
                        if ($usergroup == 'ad' || $usergroup == 'pr') {
                            echo get_string("operate_course", 'local_lmsdata');
                        } else {
                            echo get_string("major", 'local_lmsdata');
                        }
                        ?>
                    </td>
                    <td class="value" >
                        <?php
                        if ($usergroup == 'ad' || $usergroup == 'pr') {
                            foreach ($enrol_courses as $course) {
                                $course_name[] = $course->fullname;
                            }
                            echo implode(", ", $course_name);
                        } else {

                            if (current_language() == 'en') {

                                $major = $userinfo->ehks;
                                if (($usercase == 'student' && empty($userinfo->b_major)) || ($usercase == 'temp' && empty($userinfo->b_major))) {
                                    $major = get_string('user:private', 'block_jino_course_current');
                                    ;
                                }
                                echo $major;
                                //echo $userinfo->ehks;
                            } else {
                                $major = $userinfo->major;
                                if (($usercase == 'student' && empty($userinfo->b_major)) || ($usercase == 'temp' && empty($userinfo->b_major))) {

                                    $major = get_string('user:private', 'block_jino_course_current');
                                    ;
                                }
                                echo $major;
                                //echo $userinfo->major;
                            }
                        }
                        ?></td>
                </tr>
                <tr>
                    <td class="option" colspan="2"><?php echo get_string("contact", 'local_lmsdata'); ?></td>
                </tr>
                <?php ?>
                <tr>
                    <td class="option"><?php echo get_string('email'); ?></td>
                    <td class="value" ><?php
                        $email = $userinfo->email;
                        if (($usercase == 'student' && empty($userinfo->b_email)) || ($usercase == 'temp' && empty($userinfo->b_email))) {
                            $email = get_string('user:private', 'block_jino_course_current');
                            ;
                        }
                        echo $email;
                        ?>
                    </td>
                </tr>
                <tr>
                    <td class="option"><?php echo get_string('phone'); ?></td>
                    <td class="value"><?php
                        $phone1 = $userinfo->phone1;
                        if (($usercase == 'student' && empty($userinfo->b_tel)) || ($usercase == 'temp' && empty($userinfo->b_tel))) {
                            $phone1 = get_string('user:private', 'block_jino_course_current');
                            ;
                        }
                        echo $phone1;
                        ?></td>
                </tr>
                <tr>
                    <td class="option"><?php echo get_string('phone2'); ?></td>
                    <td class="value"><?php
                        $phone2 = $userinfo->phone2;
                        if (($usercase == 'student' && empty($userinfo->b_mobile)) || ($usercase == 'temp' && empty($userinfo->b_mobile))) {

                            $phone2 = get_string('user:private', 'block_jino_course_current');
                            ;
                        }
                        echo $phone2;
                        ?></td>
                </tr>
                <tr>
                    <td class="option" colspan="2"><?php echo get_string("description", 'local_lmsdata'); ?></td>
                </tr>
                <tr>
                    <td class="value" colspan="4"><?php echo $userinfo->description; ?></td>
                </tr>
            </tbody>
        </table>
        <?php
        if (isloggedin() && has_capability('moodle/site:sendmessage', $personalcontext) && !empty($CFG->messaging) && !isguestuser() && !isguestuser($user) && ($USER->id != $userinfo->id)) {
            echo '<div class="messagebox">';
            $sendmessageurl = new moodle_url('/message/index.php', array('id' => $userinfo->id));
            if ($courseid) {
                $sendmessageurl->param('viewing', MESSAGE_VIEW_COURSE . $courseid);
            }
            echo html_writer::link($sendmessageurl, get_string('messageselectadd'));
            echo '</div>';
        }
        ?>
        <!--h4 class="sub_title"><?php /* echo get_string('access_history', 'local_lmsdata'); ?></h4>
          <ul class="history" style="height:200px">
          <?php
          $params = array('userid' => $userinfo->id, 'action1' => 'loggedin', 'action2' => 'loggedout', 'target' => 'user', 'component'=>'core');
          $logs = $DB->get_records_select('logstore_standard_log'
          , 'userid = :userid and component = :component and (action = :action1 or action = :action2)'
          , $params
          , 'timecreated desc', 'id, timecreated, action', 0, 20);
          if ($logs) {
          foreach ($logs as $log) {
          $lastaccess = userdate($log->timecreated, '%Y-%m-%d %H:%M');
          echo '<li style="height:18px">' . $lastaccess . ' ' . $log->action . '</li>';
          }
          } */
        ?>
        </ul-->

    </div> <!-- Tab End -->
</div> <!-- Tab Area end -->
<?php
echo $OUTPUT->footer();
