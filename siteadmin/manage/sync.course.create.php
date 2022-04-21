<?php
/*
  create or replace FUNCTION max_course_shortname
  RETURN NUMBER
  IS max_shortname NUMBER(11,0);
  BEGIN
  SELECT MAX(TO_NUMBER(shortname)) INTO max_shortname
  FROM m_course
  WHERE LENGTH(TRIM(TRANSLATE(shortname, ' +-.0123456789',' '))) IS NULL;

  RETURN (max_shortname);
  END max_course_shortname;
 */

//ini_set('display_errors', '1');
ini_set('memory_limit', '1024M');
ini_set('max_execution_time', 0);

require(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once dirname(dirname(__FILE__)) . '/lib.php';
require_once($CFG->dirroot . '/course/lib.php');
require_once($CFG->dirroot . '/lib/accesslib.php');
require_once($CFG->dirroot . '/lib/sessionlib.php');
require_once $CFG->dirroot . '/local/haksa/config.php';
require_once $CFG->dirroot . '/local/haksa/lib.php';
require_once $CFG->dirroot . '/siteadmin/manage/synclib.php';
require_once $CFG->dirroot . '/mod/jinotechboard/lib.php';

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

$newcoursesection = 15;

$haksa = $DB->get_record('haksa', array('year' => $year, 'term' => $term));

$js = array('/siteadmin/manage/sync.js',
    '/siteadmin/js/lib/jquery.numeric.min.js');
include_once ('../inc/header.php');
?>

<div id="contents">
    <?php include_once ('../inc/sidebar_manage.php'); ?>

    <div id="content">
        <h3 class="page_title"><?php echo get_string('synchronization', 'local_lmsdata'); ?></h3>
        <p class="page_sub_title"> <?php echo get_string('msg4', 'local_lmsdata'); ?></p>

        <div class="content_navigation">
            <?php
            $tabs = siteadmin_get_sync_tabs();
            foreach ($tabs AS $i => $t) {
                $css_class = $t['class'];
                if ($t['page'] == 'course') {
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
            <div class="extra_information"><?php echo get_string('msg5', 'local_lmsdata'); ?></div>
            <div id="btn_area">
                <input type="submit" class="blue_btn" style="float:left; margin-right: 10px;" value="<?php echo get_string('okay', 'local_lmsdata'); ?>" onclick="sync_goto_config('<?php echo $key; ?>')"/>
            </div>
            <?php
        } else if ($haksa == false) {
            ?>
            <div class="extra_information">
                <p>먼저 학사시스템에서 <?php echo $year; ?> <?php echo get_string('year2', 'local_lmsdata'); ?> <?php echo $terms[$term]; ?> 강의를 가져와야 합니다.</p>
            </div>
            <div id="btn_area">
                <input type="button" class="blue_btn" style="float:left; margin-right: 10px;" value="<?php echo get_string('okay', 'local_lmsdata'); ?>" onclick="location.href = 'sync.php?tab=<?php echo $tab; ?>&year=<?php echo $year; ?>&term=<?php echo $term; ?>'"/>
            </div>
            <?php
        } else {
            ?>
            <h4 class="page_sub_title">강의 동기화</h4>
            <?php
            $terms = siteadmin_get_terms_sync();

            $leccdstart = optional_param('leccdstart', 0, PARAM_INT);

            if ($leccdstart > 0) {
                ?>
                <div class="extra_information">
                    <p>LMS에 <?php echo $year; ?> <?php echo get_string('year2', 'local_lmsdata'); ?> <?php echo $terms[$term]; ?> 강의를 생성/업데이트 중입니다.</p>
                    <p><?php echo get_string('wait_complete', 'local_lmsdata'); ?></p>
                </div>

                <div class="course_imported">
                    <?php
                    local_haksa_flushdata();

                    $strtimestart = required_param('timestart', PARAM_RAW_TRIMMED);
                    $strtimeend = required_param('timeend', PARAM_RAW_TRIMMED);
                    $strtimeregstart = required_param('timeregstart', PARAM_RAW_TRIMMED);
                    $strtimeregend = required_param('timeregend', PARAM_RAW_TRIMMED);

                    $timestart = strtotime($strtimestart);
                    $timeend = strtotime($strtimeend);
                    $timeregstart = strtotime($strtimeregstart);
                    $timeregend = strtotime($strtimeregend);
                    $timemodified = time();

                    $timecreatestart = time();
                    $count_created = 0;
                    $count_updated = 0;
                    $count_deleted = 0;

                    $userids = $DB->get_records_menu('user', array('deleted' => 0), '', 'username, id');

                    // 강의 업데이트 시작
                    // 강의 이름, 언어, 시작일 업데이트 한다.
                    $haksa_classes = $DB->get_records_sql("
SELECT cl.id,
       cl.kor_lec_name,
       cl.eng_lec_name,
       cl.shortname,
       cl.prof_cd,
       cl.cata2 as ohakkwa, 
       cl.ohakkwa as ohakkwa_cd, 
       cl.hyear,
       cl.day_tm_cd,
       cl.bb,
       cl.gubun
FROM {haksa_class} cl
WHERE cl.shortname IS NOT NULL
  AND cl.YEAR = :year
  AND cl.TERM = :term
  AND cl.DELETED = :deleted", array('year' => $year, 'term' => $term, 'deleted' => 0));

                    foreach ($haksa_classes AS $haksa_class) {
                        $mdl_class = $DB->get_record('course', array('shortname' => $haksa_class->shortname));
                        if ($mdl_class !== false) {
                            $mdl_class->fullname = $haksa_class->kor_lec_name;
                            $mdl_class->lang = 'ko';
                            $mdl_class->enddate = $timeend;
                            /*
                             * 강의 시작/종료, 강의 등록 시작/종료는 업데이트 안하도록 변경
                             * 2015. 9. 4
                             */
                            // 시작일
                            $mdl_class->startdate     = $timestart;

//                            course_create_sections_if_missing($mdl_class, range(0, $newcoursesection));

                            $DB->update_record('course', $mdl_class);

                            //// lmsdata_class 업데이트
                            $lmsdata_class = $DB->get_record('lmsdata_class', array('course' => $mdl_class->id));
                            if ($lmsdata_class) {
                                $lmsdata_class->kor_lec_name = $haksa_class->kor_lec_name;
                                $lmsdata_class->bunban = $haksa_class->bb;
                                $lmsdata_class->eng_lec_name = $haksa_class->eng_lec_name;
                                $lmsdata_class->hyear = $haksa_class->hyear;
                                $lmsdata_class->day_tm_cd = $haksa_class->day_tm_cd;
                                $lmsdata_class->timemodified = $timemodified;
                                $lmsdata_class->ohakkwa = $haksa_class->ohakkwa;
                                $lmsdata_class->ohakkwa_cd = $haksa_class->ohakkwa_cd;
                                $lmsdata_class->timestart = $timestart;
                                $lmsdata_class->timeregstart = $timeregstart;
                                $lmsdata_class->timeend = $timeend;
                                $lmsdata_class->timeregend = $timeregend;
                                // 2016. 3. 7. gubun 값이 이제 제대로 들어온다고 해서 업데이트 되도록 함.
                                $lmsdata_class->gubun = $haksa_class->gubun;

                                $prof_cd = clean_param($haksa_class->prof_cd, PARAM_USERNAME);
                                if (isset($userids[$prof_cd])) {
                                    $lmsdata_class->prof_userid = $userids[$prof_cd];
                                }

                                $DB->update_record('lmsdata_class', $lmsdata_class);
                            }

                            $count_updated++;
                            local_haksa_println('강의(<a href="' . $CFG->wwwroot . '/course/view.php?id=' . $mdl_class->id . '">' . $mdl_class->fullname . '</a>)를 업데이트했습니다.');
                        } else {
                            // 2015. 9. 7. 교수가 강의를 삭제한 경우 다시 생성되지 않음. UIC1808-01, UIC1804-01, UIC1804-02
                            // 다시 생성되도록 shortname를 null로 설정
                            $DB->set_field('haksa_class', 'shortname', null, array('id' => $haksa_class->id));
                        }
                    }
// 강의 업데이트 끝
// 새로운 강의 생성 시작
                    $categories = array();
                    local_haksa_get_course_categories($categories);

                    // 생성할 강의 가져오기
                    $haksa_classes_new = $DB->get_records_sql("
SELECT cl.id AS class_id,
       cl.KOR_LEC_NAME AS fullname,
       cl.SUMMARY,
       1 AS summaryformat,
       1 AS visible,
       'topics' AS FORMAT,
       15 AS numsections,
       0 AS hiddensections,
       0 AS coursedisplay,
       'creativeband' AS theme,
       'ko' AS lang,
       ' ' AS calendartype,
       5 AS newsitems,
       1 AS showgrades,
       0 AS showreports,
       0 AS maxbytes,
       0 AS enablecompletion,
       1 AS enrol_guest_status_0,
       0 AS groupmode,
       0 AS groupmodeforce,
       0 AS defaultgroupingid,
       ' ' AS role_1,
       ' ' AS role_2,
       ' ' AS role_3,
       ' ' AS role_4,
       ' ' AS role_5,
       ' ' AS role_6,
       ' ' AS role_7,
       ' ' AS role_8,
       0 AS id,
       cl.YEAR,
       cl.TERM,
       cl.HAKNO AS SUBJECT_ID,
       cl.PROF_CD,
       cl.KOR_LEC_NAME,
       cl.ENG_LEC_NAME,
       cl.cata2 as ohakkwa, 
       cl.ohakkwa as ohakkwa_cd, 
       cl.DOMAIN,
       cl.HAKNO,
       cl.BB,
       cl.hyear,
       cl.day_tm_cd,
       cl.SBB,
       cl.HAKJUM,
       cl.GUBUN,
       0 AS timeend,
       0 AS timeregstart,
       0 AS timeregend,
       '0' AS isnonformal,
       0 AS timemodified,
       cl.CATA1,
       cl.CATA2,
       cl.CATA3
FROM {haksa_class} cl
WHERE (cl.shortname IS NULL OR cl.shortname = '')
  AND cl.YEAR = :year
  AND cl.TERM = :term
  AND cl.DELETED = :deleted
ORDER BY cl.CATA3, cl.KOR_LEC_NAME", array('year' => $year, 'term' => $term, 'deleted' => 0));

                    foreach ($haksa_classes_new AS $haksa_class) {
                        $haksa_class->shortname = $leccdstart++;
                        $haksa_class->startdate = $timestart;
                        $haksa_class->timemodified = $timemodified;

                        if (empty($haksa_class->summary)) {
                            $haksa_class->summary = '';
                        }

                        $path = local_haksa_get_category_path($haksa_class);
                        $haksa_class->category = local_haksa_find_or_create_category($path, $categories);

                        // Create Course
                        $course = local_haksa_create_course($haksa_class);
                        $course->enddate = $timeend;
                        course_create_sections_if_missing($course, range(0, $newcoursesection));


                        $newcourse = $DB->get_record('course', array('id' => $course->id));


                        $sections = $DB->get_records('course_sections', array('course' => $newcourse->id));
                        foreach ($sections as $section) {
                            $data = new stdClass();
                            $data->name = $section->section . ' 주차';
                            if ($section->section == 0) {
                                $data->name = '';
                                $data->summary = '';
                            }
                            course_update_section($newcourse, $section, $data);
                        }


                        new_course_create_activity_jinotechboard($newcourse, 1);
                        new_course_create_activity_jinotechboard($newcourse, 2);
                        new_course_create_activity_jinotechboard($newcourse, 3);
                        new_course_create_activity_jinotechboard($newcourse, 4);

                        // Update haksa_class->shortname
                        $DB->set_field('haksa_class', 'shortname', $haksa_class->shortname, array('id' => $haksa_class->class_id));

                        // Insert lmsdata_class table
                        $lmsdata_class = new stdClass();
                        $lmsdata_class->course = $course->id;
                        $lmsdata_class->subject_id = $haksa_class->subject_id;
                        $lmsdata_class->category = $haksa_class->category;
                        $lmsdata_class->kor_lec_name = $haksa_class->kor_lec_name;
                        $lmsdata_class->eng_lec_name = $haksa_class->eng_lec_name;
                        $lmsdata_class->prof_userid = 0;
                        $lmsdata_class->year = $haksa_class->year;
                        $lmsdata_class->term = $haksa_class->term;
                        $lmsdata_class->timestart = $timestart;
                        $lmsdata_class->timeend = $timeend;
                        $lmsdata_class->timeregstart = $timeregstart;
                        $lmsdata_class->timeregend = $timeregend;
                        $lmsdata_class->isnonformal = $haksa_class->isnonformal;
                        $lmsdata_class->gubun = $haksa_class->gubun;
                        $lmsdata_class->bunban = $haksa_class->bb;
                        $lmsdata_class->timemodified = $haksa_class->timemodified;
                        $lmsdata_class->ohakkwa = $haksa_class->ohakkwa;
                        $lmsdata_class->ohakkwa_cd = $haksa_class->ohakkwa_cd;
                        $lmsdata_class->domain = $haksa_class->domain;

                        $prof_cd = clean_param($haksa_class->prof_cd, PARAM_USERNAME);
                        if (isset($userids[$prof_cd])) {
                            $lmsdata_class->prof_userid = $userids[$prof_cd];
                        } else {
                            //$userid = $useryscec->id;
                            //local_haksa_println('Could not found user: '.$conew->prof_cd.', '.$conew->shortname, $logfile);
                        }



                        $DB->insert_record('lmsdata_class', $lmsdata_class);

                        // local/courselis/classes/observer.php의 course_created 함수에서 lmsdata_class 테이블에 넣는 것을 막기위해
                        // lmsdata_class 에 넣은 후 이벤트를 발생시킨다.
                        // Trigger a course created event.
                        $event = \core\event\course_created::create(array(
                                    'objectid' => $course->id,
                                    'context' => context_course::instance($course->id),
                                    'other' => array('shortname' => $course->shortname,
                                        'fullname' => $course->fullname)
                        ));
                        $event->trigger();

                        $count_created++;

                        local_haksa_println('강의(<a href="' . $CFG->wwwroot . '/course/view.php?id=' . $course->id . '">' . $course->fullname . '</a>)를 생성했습니다.');
                    }
// 새로운 강의 생성 끝
// 지워진 강의 삭제 시작
                    $deleted_classes = $DB->get_records_sql("SELECT id, shortname
                FROM {haksa_class} cl
WHERE cl.HAKNO IS NOT NULL
  AND cl.YEAR = :year
  AND cl.TERM = :term
  AND cl.DELETED = :deleted", array('year' => $year, 'term' => $term, 'deleted' => 1));

                    foreach ($deleted_classes as $deleted_class) {
                        $course = $DB->get_record('course', array('shortname' => $deleted_class->shortname));
                        if ($course !== false) {
                            if (delete_course($course->id, false)) {
                                $DB->set_field('haksa_class', 'shortname', NULL, array('id' => $deleted_class->id));
                                // lmsdata_class 테이블은 지우지 않아도 이벤트에 의해
                                // /local/yscec/lib.php 의 local_yscec_course_deleted 함수에서 지워짐
                                // $DB->delete_records('lmsdata_class', array('course' => $course->id));

                                $count_deleted++;
                                local_haksa_println('강의(' . $course->fullname . ')를 삭제했습니다.');
                            }
                        }
                    }
// 지워진 강의 삭제 끝


                    fix_course_sortorder();
                    cache_helper::purge_by_event('changesincourse');

                    $timecreateend = time();

                    $haksa->timecreatecourse = $timecreatestart;
                    $DB->update_record('haksa', $haksa);
                    ?>
                </div>

                <div class="extra_information">
                    <p><?php echo $count_created; ?> 개의 강의를 생성했습니다.</p>
                    <p><?php echo $count_updated; ?> 개의 강의를 업데이트했습니다.</p>
                    <p><?php echo $count_deleted; ?> 개의 강의가 삭제되었습니다.</p>
                </div>
                <div id="btn_area">
                    <input type="button" class="blue_btn" style="float:left; margin-right: 10px;" value="<?php echo get_string('okay', 'local_lmsdata'); ?>" onclick="location.href = 'sync.php?tab=<?php echo $tab; ?>&year=<?php echo $year; ?>&term=<?php echo $term; ?>'"/>
                </div>
                <?php
                local_haksa_scroll_down();
            } else {
                $max_shortname = siteadmin_get_max_course_shortname(); //$DB->get_field_sql("SELECT max_course_shortname() " . $DB->sql_null_from_clause());

                if ($max_shortname < 10000) {
                    $max_shortname = 10000;
                }

                $timestart = "";
                $timeend = "";
                $timeregstart = "";
                $timeregend = "";

                // 이전에 만든 강의가 있는지 <?php echo get_string('okay','local_lmsdata');
                if ($maxid = $DB->get_field_sql('SELECT MAX(id) FROM {lmsdata_class} WHERE year = :year AND term = :term', array('year' => $year, 'term' => $term))) {
                    $class = $DB->get_record('lmsdata_class', array('id' => $maxid));

                    $timestart = strftime('%Y-%m-%d', $class->timestart);
                    $timeend = strftime('%Y-%m-%d', $class->timeend);
                    $timeregstart = strftime('%Y-%m-%d', $class->timeregstart);
                    $timeregend = strftime('%Y-%m-%d', $class->timeregend);
                }
                ?>
                <div class="extra_information">
                    <p>LMS에 <?php echo $year; ?> <?php echo get_string('year2', 'local_lmsdata'); ?> <?php echo $terms[$term]; ?> 강의를 생성/업데이트 합니다.</p>
                    <p>학사시스템에서 지워진 강의는 삭제됩니다.</p>
                </div>

                <form id="sync_course_create" class="sync_area" method="POST" style="clear:both;">
                    <input type="hidden" name="tab" value="<?php echo $tab; ?>" />
                    <input type="hidden" name="year" value="<?php echo $year; ?>" />
                    <input type="hidden" name="term" value="<?php echo $term; ?>" />
                    <input type="hidden" name="leccdstart" id="leccdstart" value="<?php echo $max_shortname; ?>" />
                    <label style="margin-right: 60px;"><font color="#F00A0D" size="3px;"><strong>*</strong></font> 강의 시작일</label>
                    <input type="text" name="timestart" id="timestart" placeholder="YYYY-MM-DD" value="<?php echo $timestart; ?>" /> <br/>
                    <label style="margin-right: 60px;"><font color="#F00A0D" size="3px;"><strong>*</strong></font> 강의 종료일</label>
                    <input type="text" name="timeend" id="timeend" placeholder="YYYY-MM-DD" value="<?php echo $timeend; ?>" /> <br/>
                    <label style="margin-right: 60px;"><font color="#F00A0D" size="3px;"><strong>*</strong></font> 등록 시작일</label>
                    <input type="text" name="timeregstart" id="timeregstart" placeholder="YYYY-MM-DD" value="<?php echo $timeregstart; ?>" /> <br/>
                    <label style="margin-right: 60px;"><font color="#F00A0D" size="3px;"><strong>*</strong></font> 등록 종료일</label>
                    <input type="text" name="timeregend" id="timeregend" placeholder="YYYY-MM-DD" value="<?php echo $timeregend; ?>" /> <br/>
                </form>

                <div id="btn_area">
                    <input type="button" class="blue_btn" style="float:left; margin-right: 10px;" value="생성" onclick="sync_course_create_submit()"/>
                    <input type="button" class="blue_btn" style="float:left; margin-right: 10px;" value="<?php echo get_string('cancle', 'local_lmsdata'); ?>" onclick="location.href = 'sync.php?tab=<?php echo $tab; ?>&year=<?php echo $year; ?>&term=<?php echo $term; ?>'"/>
                </div>
                <?php
            }
        }
        ?>
    </div><!--Content End-->

</div> <!--Contents End-->

<script type="text/javascript">
    function sync_course_create_submit() {
        if ($.trim($("input[name='timestart']").val()) == '') {
            alert("강의 시작일을 입력하세요.");
            return false;
        }

        if ($.trim($("input[name='timeend']").val()) == '') {
            alert("강의 종료일를 입력하세요.");
            return false;
        }

        if ($.trim($("input[name='timeregstart']").val()) == '') {
            alert("등록 시작일을 입력하세요.");
            return false;
        }

        if ($.trim($("input[name='timeregend']").val()) == '') {
            alert("등록 종료일을 입력하세요.");
            return false;
        }

        if (($.trim($("input[name='leccdstart']").val()) == '')) {
            alert("강의코드 시작 값을 입력하세요.");
            return false;
        }

        $('#sync_course_create').submit();
    }

    $(document).ready(function () {
        $("#timestart").datepicker({
            dateFormat: "yy-mm-dd",
            onClose: function (selectedDate) {
                $("#timeend").datepicker("option", "minDate", selectedDate);
            }
        });
        $("#timeend").datepicker({
            dateFormat: "yy-mm-dd",
            onClose: function (selectedDate) {
                $("#timestart").datepicker("option", "maxDate", selectedDate);
            }
        });
        $("#timeregstart").datepicker({
            dateFormat: "yy-mm-dd",
            onClose: function (selectedDate) {
                $("#timeregend").datepicker("option", "minDate", selectedDate);
            }
        });
        $("#timeregend").datepicker({
            dateFormat: "yy-mm-dd",
            onClose: function (selectedDate) {
                $("#timeregstart").datepicker("option", "maxDate", selectedDate);
            }
        });

        $("#leccdstart").numeric(
                false,
                function () {
                    alert('숫자만 입력 가능합니다.');
                    this.value = "";
                    this.focus();
                }
        );
    });
</script>

<?php
include_once ('../inc/footer.php');
