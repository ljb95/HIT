<?php
ini_set('memory_limit', '1024M');
ini_set('max_execution_time', 0);

require(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once dirname(dirname(__FILE__)) . '/lib.php';
require_once $CFG->dirroot . '/local/haksa/config.php';
require_once $CFG->dirroot . '/local/haksa/lib.php';
require_once($CFG->dirroot . '/course/lib.php');
require_once($CFG->dirroot . '/lib/accesslib.php');
require_once($CFG->dirroot . '/lib/sessionlib.php');
require_once($CFG->dirroot . '/enrol/locallib.php');
require_once $CFG->dirroot . '/siteadmin/manage/synclib.php';

// Check for valid admin user - no guest autologin
require_login(0, false);
if (isguestuser()) {
    $SESSION->wantsurl = (string) new moodle_url('/siteadmin/manage/course_list.php');
    redirect(get_login_url());
}
$context = context_system::instance();
require_capability('moodle/site:config', $context);


$year = optional_param('year', 0, PARAM_INT);
$term = optional_param('term', 0, PARAM_INT);

if ($year == 0) {
    $year = get_config('moodle', 'haxa_year');
}
if ($term == 0) {
    $term = get_config('moodle', 'haxa_term');
}

$tab = 0;

$haksa = $DB->get_record('haksa', array('year' => $year, 'term' => $term));
if ($haksa == false) {
    $haksa = new stdClass();
    $haksa->year = $year;
    $haksa->term = $term;
    $haksa->timesynccourse = 0;
    $haksa->timesyncparticipant = 0;
    $haksa->timecreatecourse = 0;
    $haksa->timeassignparticipant = 0;

    $haksa->id = $DB->insert_record('haksa', $haksa);
}

$js = array('/siteadmin/manage/sync.js');
include_once ('../inc/header.php');
?>

<div id="contents">
    <?php include_once ('../inc/sidebar_manage.php'); ?>

    <div id="content">
        <h3 class="page_title"><?php echo get_string('synchronization', 'local_lmsdata'); ?></h3>
        <p class="page_sub_title"> <?php echo get_string('msg4','local_lmsdata'); ?></p>

        <div class="content_navigation">
            <?php
            $tabs = siteadmin_get_sync_tabs();
            foreach ($tabs AS $i => $t) {
                $css_class = $t['class'];
                if ($t['page'] == 'participant') {
                    $css_class .= ' ' . $css_class . '_selected';
                    $tab = $i;
                }
                echo '<a href="sync.php?tab=' . $i . '"><p class="' . $css_class . '">' . $t['text'] . '</p></a>';
            }
            ?>
        </div><!--Content Navigation End-->

        <?php
        if ((empty($year) || empty($term)) && $tabs[$tab]['page'] != 'config') {
            end($tabs);         // move the internal pointer to the end of the array
            $key = key($tabs);
            ?>
            <div class="extra_information"><?php echo get_string('msg5','local_lmsdata'); ?></div>
            <div id="btn_area">
                <input type="submit" class="blue_btn" style="float:left; margin-right: 10px;" value="<?php echo get_string('okay','local_lmsdata'); ?>" onclick="sync_goto_config('<?php echo $key; ?>')"/>
            </div>
            <?php
        } else if (!$haksa->timecreatecourse) {
            $terms = siteadmin_get_terms_sync();
            ?>
            <div class="extra_information">
                <p>?????? <?php echo $year; ?> <?php echo get_string('year2','local_lmsdata'); ?> <?php echo $terms[$term]; ?> ????????? ???????????? ?????????.</p>
            </div>
            <div id="btn_area">
                <input type="button" class="blue_btn" style="float:left; margin-right: 10px;" value="<?php echo get_string('okay','local_lmsdata'); ?>" onclick="location.href = 'sync.php?tab=<?php echo $tab; ?>&year=<?php echo $year; ?>&term=<?php echo $term; ?>'"/>
            </div>
            <?php
        } else if (!$haksa->timesyncparticipant) {
            $terms = siteadmin_get_terms_sync();
            ?>
            <div class="extra_information">
                <p>?????? ????????????????????? <?php echo $year; ?> <?php echo get_string('year2','local_lmsdata'); ?> <?php echo $terms[$term]; ?> ?????? ???????????? ???????????? ?????????.</p>
            </div>
            <div id="btn_area">
                <input type="button" class="blue_btn" style="float:left; margin-right: 10px;" value="<?php echo get_string('okay','local_lmsdata'); ?>" onclick="location.href = 'sync.php?tab=<?php echo $tab; ?>&year=<?php echo $year; ?>&term=<?php echo $term; ?>'"/>
            </div>
            <?php
        } else {
            $terms = siteadmin_get_terms_sync();
            ?>
            <h4 class="page_sub_title">?????? ????????? ??????</h4>

            <div class="extra_information">
                <p>LMS??? <?php echo $year; ?> <?php echo get_string('year2','local_lmsdata'); ?> <?php echo $terms[$term]; ?> ????????? ???????????? ???????????????.</p>
                <p><?php echo get_string('wait_complete','local_lmsdata'); ?></p>
            </div>

            <div class="course_imported">
                <?php
                local_haksa_flushdata();

                $timeassignstart = time();

                $count_participant = 0;
                $count_participant_deleted = 0; 

                $roles = array();
                // Professor, ??????, pr
                $roles['pr'] = $DB->get_record('role', array('shortname' => 'editingteacher'));
                // Teaching assistant, ??????, as
                //$roles['as'] = $DB->get_record('role', array('shortname'=>'teacher'));
                // Student, ?????????, rs
                $roles['rs'] = $DB->get_record('role', array('shortname' => 'student'));
                // Auditor, ?????????, au
                //$roles['au'] = $DB->get_record('role', array('shortname'=>'auditor'));
                $existingusers = $DB->get_records_menu('user', array('deleted' => 0), '', 'username, id');

                $courses = $DB->get_records_sql("SELECT co.id,
       co.shortname,
       co.fullname AS coursename,
       co.startdate,
       ctx.id AS contextid
FROM {haksa_class} hc
JOIN {course} co ON co.shortname = hc.shortname
                AND hc.deleted = 0
JOIN {context} ctx ON ctx.instanceid = co.id
                  AND ctx.contextlevel = :contextlevel
WHERE hc.year = :year
  AND hc.term = :term
ORDER BY co.sortorder", array('year' => $year, 'term' => $term, 'contextlevel' => CONTEXT_COURSE));

                foreach ($courses as $course) {
                    $courseid = $course->id;
                    $contextid = $course->contextid;
                    $mcourse = $DB->get_record('course',array('id'=>$courseid));

                    // ?????? ??????
                    $students = $DB->get_records_sql("SELECT hs.hakbun,
                                                             hs.deleted
                                                    FROM {haksa_class_student} hs
                                                    JOIN {haksa_class} hc ON hc.year  = hs.year
                                                                         AND hc.term  = hs.term
                                                                         AND hc.hakno = hs.hakno
                                                    WHERE hs.year = :year
                                                      AND hs.term = :term 
                                                      AND hc.shortname = :shortname", array('year' => $year, 'term' => $term, 'shortname' => $course->shortname));

                    $assigned_students = $DB->get_records_menu('role_assignments', array('roleid' => $roles['rs']->id, 'contextid' => $contextid), '', 'userid, id');

                    $count_student = 0;
                    $count_student_deleted = 0;
                    foreach ($students AS $student) {
                        $username = clean_param($student->hakbun, PARAM_USERNAME);
                        if (isset($existingusers[$username])) {
                            // ????????? ??????????????? ?????? ????????? ??? ????????? ??????.
                            if ($student->deleted) { // ????????? ????????? ??????
                                // ????????? ????????? ?????? ?????? ?????? ??????
                                    $enrol = $DB->get_field_sql("SELECT en.enrol FROM {user_enrolments} ue JOIN {enrol} en ON ue.enrolid = en.id WHERE en.courseid = :courseid AND ue.userid = :userid", array('courseid' => $course->id, 'userid' => $existingusers[$username]));
                                    $enrol = $DB->get_field_sql("SELECT en.enrol FROM {user_enrolments} ue "
                                        . "JOIN {enrol} en ON ue.enrolid = en.id "
                                        . "WHERE en.courseid = :courseid AND ue.userid = :userid", array('courseid' => $course->id, 'userid' => $existingusers[$username]));
                                if (isset($assigned_professors[$existingusers[$username]]) || $enrol == 'manual') {

                                        $count_participant_deleted++;
                                        $count_student_deleted++;
                                    }
                                
                            } else { // ????????? ????????? ??????
                                // ????????? ????????? ????????? ??????
                                if (!isset($assigned_students[$existingusers[$username]])) {
                                    // 2015. 8. 17. "?????? ?????? > ????????? > ????????? ?????????" ??????????????? ??????????????? ???????????? ????????? ??????,
                                    // "?????? ??????" ???????????? "?????? ?????? "???????????? ??????"??? ?????? ????????? ?????? ??????
                                    //local_haksa_assign_user($courseid, $existingusers[$username], $roles['rs'], $course->startdate, 0, $timeassignstart);
                                    local_haksa_assign_user($courseid, $existingusers[$username], $roles['rs'], 0, 0, $timeassignstart);

                                    $count_participant++;
                                    $count_student++;
                                }
                            }
                        }
                    }

                    // ?????? ??????
                    $professors = $DB->get_records_sql("SELECT hp.id,
                                                           hp.prof_cd,
                                                           hp.deleted
                                                    FROM {haksa_class_professor} hp
                                                    JOIN {haksa_class} hc ON hc.year  = hp.year
                                                                         AND hc.term  = hp.term
                                                                         AND hc.hakno = hp.hakno
                                                    WHERE hp.year = :year
                                                      AND hp.term = :term
                                                      AND hc.shortname = :shortname", array('year' => $year, 'term' => $term, 'shortname' => $course->shortname));

                    $assigned_professors = $DB->get_records_menu('role_assignments', array('roleid' => $roles['pr']->id, 'contextid' => $contextid), '', 'userid, id');

                    $count_professor = 0;
                    $count_professor_deleted = 0;

                    foreach ($professors AS $professor) {
                        $username = clean_param($professor->prof_cd, PARAM_USERNAME);
                        if (isset($existingusers[$username])) {
                            if ($professor->deleted) {
                                if (isset($assigned_professors[$existingusers[$username]])) {
                                    local_haksa_unassign_user($course, $existingusers[$username], $roles['pr']->id);

                                    $count_participant_deleted++;
                                    $count_professor_deleted++;
                                }
                            } else {
                                if (!isset($assigned_professors[$existingusers[$username]])) {
                                    local_haksa_assign_user($courseid, $existingusers[$username], $roles['pr'], 0, 0, $timeassignstart);
                                    $count_participant++;
                                    $count_professor++;
                                }
                            }
                        }
                    }

                    if($count_student > 0 || $count_professor > 0 || $count_student_deleted > 0 || $count_professor_deleted > 0) {
                    local_haksa_println('<a href="' . $CFG->wwwroot . '/course/view.php?id=' . $courseid . '">' . $course->coursename . '</a>: ??????(?????? ' . $count_student . ' ???, ?????? ' . $count_professor . ' ???), ????????????(?????? ' . $count_student_deleted . ' ???, ?????? ' . $count_professor_deleted . ' ???)');
                    }
                }

                $timeassignend = time();

                $haksa->timeassignparticipant = $timeassignstart;
                $DB->update_record('haksa', $haksa);
                ?>
            </div>

            <div class="extra_information">
                <p><?php echo count($courses); ?> ?????? ????????? <?php echo $count_participant; ?> ?????? ???????????? ??????, <?php echo $count_participant_deleted; ?> ?????? ???????????? ???????????? ????????????.</p>
            </div>
            <div id="btn_area">
                <input type="button" class="blue_btn" style="float:left; margin-right: 10px;" value="<?php echo get_string('okay','local_lmsdata'); ?>" onclick="location.href = 'sync.php?tab=<?php echo $tab; ?>&year=<?php echo $year; ?>&term=<?php echo $term; ?>'"/>
            </div>
            <?php
            local_haksa_scroll_down();

            purge_all_caches();
        }
        ?>

    </div><!--Content End-->
</div> <!--Contents End-->

<?php
include_once ('../inc/footer.php');
