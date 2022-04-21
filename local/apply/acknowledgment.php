<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * My Moodle -- a user's personal dashboard
 *
 * - each user can currently have their own page (cloned from system and then customised)
 * - only the user can see their own dashboard
 * - users can add any blocks they want
 * - the administrators can define a default site dashboard for users who have
 *   not created their own dashboard
 *
 * This script implements the user's view of the dashboard, and allows editing
 * of the dashboard.
 *
 * @package    moodlecore
 * @subpackage my
 * @copyright  2010 Remote-Learner.net
 * @author     Hubert Chathi <hubert@remote-learner.net>
 * @author     Olav Jordan <olav.jordan@remote-learner.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(__FILE__) . '/../config.php');
require_once($CFG->dirroot . '/my/lib.php');
require_once($CFG->dirroot . '/siteadmin/lib.php');
//require_once($CFG->dirroot . '/local/coursepoint/index.php');

redirect_if_major_upgrade_required();

// TODO Add sesskey check to edit
$edit = optional_param('edit', null, PARAM_BOOL);    // Turn editing on and off
$reset = optional_param('reset', null, PARAM_BOOL);
$type = optional_param('type', 0, PARAM_INT); // 페이지 타입
$coursetype = optional_param('coursetype', 3, PARAM_INT); // 강좌타입
$year = optional_param('courseyear', 0, PARAM_INT); // 강의년도
$term = optional_param('courseterm', 0, PARAM_INT); // 학기
$searchvalue = optional_param('searchvalue', '', PARAM_RAW); // 강좌명
$searchtype = optional_param('searchtype', 0, PARAM_INT); // 검색타입
$perpage = optional_param('perpage', 10, PARAM_INT); // 보여줄 개수
$page = optional_param('page', 1, PARAM_INT); // 페이지

require_login();

$hassiteconfig = has_capability('moodle/site:config', context_system::instance());
if ($hassiteconfig && moodle_needs_upgrading()) {
    redirect(new moodle_url('/admin/index.php'));
}
$PAGE->requires->jquery();
$PAGE->requires->jquery_plugin('ui');
$PAGE->requires->jquery_plugin('ui-css');
//$strmymoodle = get_string('myhome');
//페이지 브라우저 타이틀을 사이트명으로 변경(지노테크 정민정 - 2016/10/20)
$strmymoodle = $SITE->fullname;


// Get the My Moodle page info.  Should always return something unless the database is broken.
if (!$currentpage = my_get_page($userid, MY_PAGE_PRIVATE)) {
    print_error('mymoodlesetup');
}

// Start setting up the page
$params = array();
$PAGE->set_context($context);
//$PAGE->set_url('/my/allcourse.php', $params);
$strplural = get_string("major_auditor", "local_lmsdata");
$PAGE->navbar->add($strplural);
$PAGE->navbar->add(get_string('application','local_lmsdata'));
$PAGE->set_title($strplural);



$PAGE->set_pagelayout('standard');
$PAGE->set_pagetype('my-allcourse');
$PAGE->blocks->add_region('content');
$PAGE->set_subpage($currentpage->id);
$PAGE->set_title($pagetitle);
$PAGE->set_heading($header);

if (!isguestuser()) {   // Skip default home page for guests
    if (get_home_page() != HOMEPAGE_MY) {
        if (optional_param('setdefaulthome', false, PARAM_BOOL)) {
            set_user_preference('user_home_page_preference', HOMEPAGE_MY);
        } else if (!empty($CFG->defaulthomepage) && $CFG->defaulthomepage == HOMEPAGE_USER) {
            $frontpagenode = $PAGE->settingsnav->add(get_string('frontpagesettings'), null, navigation_node::TYPE_SETTING, null);
            $frontpagenode->force_open();
            $frontpagenode->add(get_string('makethismyhome'), new moodle_url('/my/', array('setdefaulthome' => true)), navigation_node::TYPE_SETTING);
        }
    }
}


echo $OUTPUT->header();
?>
        <div class="table_group">
            <b style="font-size:30px; float:left;">조교/청강생 승인</b><br><br>
        </div>
        
        <div class="table_group">
            <table class="generaltable regular-courses">
                <thead>
                    <tr>
                        <th scope="row" width="5%">신청구분</th>
                        <th scope="row" width="5%">이름(학번)</th>
                        <th scope="row" width="5%"><?php echo get_string('email','local_lmsdata') ?></th>
                        <th scope="row" width="5%"><?php echo get_string('phone','local_lmsdata') ?></th>
                        <th scope="row" width="5%"><?php echo get_string('apply_handle','local_lmsdata') ?></th>
                        <th scope="row" width="5%">상태</th>
                        <th scope="row" width="5%">승인</th>
                    </tr>   
                </thead>
                <tbody>
                    <?php
                    $offset = ($currpage - 1) * $perpage;
                    $sql_like = "";
                    if (!empty($searchtext)) {
                        $sql_like .= 'and f.title like :searchtxt ';
                    }
                    $params = array('type' => 1, 'searchtxt' => "%" . $searchtext . "%", 'time1' => time(), 'time2' => time());
                    if (!is_siteadmin()) {
                        $myusergroup = $DB->get_field('lmsdata_user', 'usergroup', array('userid' => $USER->id));
                        $params['usergroup'] = '%' . $myusergroup . '%';
                        $sql_like .= ' and e.course in '.$course_in;
                    } 
                    $cnt2 = 0;
                    $sql = 'select e.*,c.fullname, f.title '
                            . 'from {lmsdata_evaluation} e '
                            . 'join {course} c on c.id=e.course '
                            . 'join {lmsdata_evaluation_forms} f on f.id = e.formid '
                            . 'where e.timestart <= :time1 and e.timeend > :time2  and e.type = :type ' . $sql_like;
                    $orderby = ' ORDER BY e.timestart DESC ';
                    $evaluations = $DB->get_records_sql($sql . $orderby, $params, $offset, $perpage);
                    $evaluations_cnt = $DB->count_records_sql('select count(*) from {lmsdata_evaluation} e where e.timestart <= :time1 and e.timeend > :time2  and e.type = :type' . $sql_like, $params);
                    foreach ($evaluations as $evaluation) {
                        ?>
                            <tr>
                                <td scope="col"><?php echo $evaluations_cnt--; ?></td>
                                <td scope="col"><?php echo $evaluation->title; ?></td>
                                <td scope="col" class="title"><?php echo $evaluation->fullname; ?></td>
                                <td scope="col"><?php echo date("Y-m-d", $evaluation->timestart) . " ~ " . date("Y-m-d", $evaluation->timeend); ?></td>
                        <?php
                        $answers = $DB->get_records('lmsdata_evaluation_submits', array('evaluation' => $evaluation->id, 'userid' => $USER->id, 'completion' => 1));
                        if (!($answers) && !is_siteadmin()) {
                            ?>
                                    <td scope="col"><input type="button" class="blue_btn_small" onclick="location.href = '<?php echo $CFG->wwwroot . "/local/evaluation/survey.php?id=" . $evaluation->id; ?>'" value="<?php echo get_string('participation', 'local_evaluation')?>"></td>
                                <?php } else { ?>
                                    <td scope="col"><input type="button" class="gray_btn_small" onclick="location.href = '<?php echo $CFG->wwwroot . "/local/evaluation/answers.php?id=" . $evaluation->id; ?>'" value="<?php echo get_string('viewresults', 'local_evaluation')?>"></td>
                                <?php } ?>
                            </tr>
                                <?php
                                $cnt2++;
                            }
                            if ($cnt2 <= 0) {
                                ?>
                            <tr>
                                <td scope="col" colspan="8"><?php echo get_string('Explanation', 'local_evaluation')?></td>
                            </tr>
                        <?php } ?>
            </tbody>
            </table>
        </div>  
        
<?php
echo $OUTPUT->footer();
?>