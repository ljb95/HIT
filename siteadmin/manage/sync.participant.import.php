<?php
//ini_set('display_errors', '1');
ini_set('memory_limit', '1024M');
ini_set('max_execution_time', 0);

require(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once dirname(dirname(__FILE__)) . '/lib.php';
require_once $CFG->dirroot . '/local/haksa/lib.php';
require_once $CFG->dirroot . '/siteadmin/manage/synclib.php';

// Check for valid admin user - no guest autologin
require_login(0, false);
if (isguestuser()) {
    $SESSION->wantsurl = (string) new moodle_url('/siteadmin/manage/sync.participant.import.php');
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
        } else {
            $terms = siteadmin_get_terms_sync();
            ?>
            <h4 class="page_sub_title">강의 참여자 동기화</h4>
            <div class="extra_information">
                <p>학사시스템에서 <?php echo $year; ?> <?php echo get_string('year2','local_lmsdata'); ?> <?php echo $terms[$term]; ?> 강의 참여자를 가져오는 중입니다.</p>
                <p><?php echo get_string('wait_complete','local_lmsdata'); ?></p>
            </div>

            <div class="course_imported">
                <?php
                local_haksa_flushdata();

                $param = array('year'=>$year, 'term'=>$term);
                
                $sql_existingprofs = "SELECT p.id,
                    CONCAT(CONCAT(p.LEC_CD, '-'), p.prof_cd) AS prof_key
            FROM {haksa_class_professor} p
            WHERE p.year = :year
              AND p.term = :term";

                // 이전에 가져온 교수 데이터
                $profs = $DB->get_records_sql_menu($sql_existingprofs, array('year' => $year, 'term' => $term));

                $sql_prof = "SELECT 
                                YEAR,
                                TERM,
                                LEC_CD AS LEC_CD,
                                PROF_CD,
                                PROF_NM,
                                LEC_CD AS HAKNO 
                            FROM LMS_SO_PROF 
                            WHERE YEAR = $year 
                              AND TERM = $term";
                 
                
                // 교수
                $conn = siteadmin_sync_db_connect();
                $rs = odbc_prepare ($conn, $sql_prof);
                // article_text가 잘려나오지 않도록 1M로 설정
                odbc_longreadlen($rs, 1048576);
                $success = odbc_execute($rs, $param);
                if($success) {
                    $count_prof = 0;
                    $count_prof_deleted = 0;
                    while ($row = odbc_fetch_array($rs)) {
                        $prof = (object) array_change_key_case($row, CASE_LOWER);
                        // 비어있는 것들은 무시
                        if(empty($prof->lec_cd)) {
                            continue;
                        }
                         
                        $prof->prof_name = $prof->prof_nm;
                        $prof->univ = ' ';
                        $prof->major = ' ';

                        // 없는 경우에만 넣어야 함.
                        $prof_key = $prof->lec_cd.'-'.$prof->prof_cd;
                        if (in_array($prof_key, $profs)) {
                            $prof_id = array_search($prof_key, $profs);
                            if ($record = $DB->get_record('haksa_class_professor', array('id' => $prof_id))) {
                                $prof->id = $record->id;
                                $prof->haksa = $haksa->id;
                                $DB->update_record('haksa_class_professor', $prof);
                            }
                            unset($profs[$prof_id]);
                        } else {
                            $prof->haksa = $haksa->id;
                            $DB->insert_record('haksa_class_professor', $prof);

                            $count_prof += 1;

                            local_haksa_println('교수 추가(강의코드:' . $prof->lec_cd .'-'. $prof->prof_cd . ', 이름:' . $prof->prof_nm . ')'); 
                        }
                    }
                    odbc_free_result($rs);
                    siteadmin_sync_db_close($conn);
                    
                    local_haksa_println('');
                    
                    // 삭제된 교수 처리
                    foreach ($profs as $deleted_prof_id => $prof_key) {
                        if ($deleted_prof = $DB->get_record('haksa_class_professor', array('id' => $deleted_prof_id))) {
                            // deleted = 1 로 만듬.
                            if ($deleted_prof->deleted == 0) {
                                $deleted_prof->deleted = 1;
                                $DB->update_record('haksa_class_professor', $deleted_prof);

                                $count_prof_deleted += 1;

                                local_haksa_println('교수 삭제(강의코드:' .  $deleted_prof->lec_cd .'- 교번:' . $deleted_prof->prof_cd . ', 이름:' . $deleted_prof->prof_name . ')');
                            }
                        }
                    }
                    local_haksa_println('');
                } else {
                    local_haksa_println('Error(Professor): '.odbc_errormsg($conn));
                }

                
                

                $sql_existingstuds = "SELECT s.id, 
                                 CONCAT(CONCAT(s.LEC_CD, '-'), s.hakbun) AS stud_key
                                        FROM {haksa_class_student} s
                                        WHERE s.year = :year
                                          AND s.term = :term ";

                // 이전에 가져온 학생 데이터
                $studs = $DB->get_records_sql_menu($sql_existingstuds, array('year' => $year, 'term' => $term));

                $sql_stud = "SELECT 
                                YEAR,
                                TERM,
                                LEC_CD,
                                HAKBUN,
                                USER_NM AS NAME,
                                LEC_CD AS HAKNO, 
                                DELETED
                            FROM LMS_SU 
                            WHERE YEAR = $year  
                              AND TERM = $term"; 
                // 학생 
                $conn = siteadmin_sync_db_connect();
                $rs = odbc_prepare ($conn, $sql_stud);
                // article_text가 잘려나오지 않도록 1M로 설정
                odbc_longreadlen($rs, 1048576);
                $success = odbc_execute($rs, $param);
                if($success) {
                    $count_stud = 0;
                    $count_stud_deleted = 0;
                    while ($row = odbc_fetch_array($rs)) {
                        $stud = (object) array_change_key_case($row, CASE_LOWER);
                        // 비어있는 것들은 무시
                        if(empty($stud->lec_cd)) {
                            continue;
                        }
                        if($stud->deleted == 'Y'){
                            $stud->deleted = 0;
                        }
                        

                        // 없는 경우에만 넣어야 함.
                        $stud_key = $stud->lec_cd.'-'.$stud->hakbun;
                        if (in_array($stud_key, $studs)) {
                            $stud_id = array_search($stud_key, $studs);
                            if ($record = $DB->get_record('haksa_class_student', array('id' => $stud_id))) {
                                $stud->id = $record->id;
                                $stud->haksa = $haksa->id;
                                $DB->update_record('haksa_class_student', $stud);
                            }
                            if ($stud->deleted == 0) {
                                unset($studs[$stud_id]);
                            } 
                                
                        } else { 
                            $stud->haksa = $haksa->id;
                            $DB->insert_record('haksa_class_student', $stud);

                            $count_stud += 1;

                            local_haksa_println('학생 추가(강의코드:' . $stud->lec_cd.', 학번:' . $stud->hakbun . ', 이름:' . $stud->name . ')');
                        }
                    }
                    odbc_free_result($rs);
                    siteadmin_sync_db_close($conn);
                    
                    local_haksa_println('');

                    // 삭제된 학생 처리
                    foreach ($studs as $deleted_stud_id => $stud_key) {
                        if ($deleted_stud = $DB->get_record('haksa_class_student', array('id' => $deleted_stud_id))) {
                            // deleted = 1 로 만듬.
                            if ($deleted_stud->deleted == 0) {
                                $deleted_stud->deleted = 1;
                                $DB->update_record('haksa_class_student', $deleted_stud);

                                $count_stud_deleted += 1;

                                local_haksa_println('학생 삭제(강의코드:' . $deleted_stud->lec_cd.'-, 학번:' . $deleted_stud->hakbun . ', 이름:' . $deleted_stud->name . ')');
                            }
                        }
                    }
                } else {
                    local_haksa_println('Error(Student): '.odbc_errormsg($conn));
                }

                $haksa->timesyncparticipant = time();
                $DB->update_record('haksa', $haksa);
                ?>
            </div>

            <div class="extra_information">
                <p><?php echo $count_prof; ?> 명의 교수, <?php echo $count_stud; ?> 명의 학생 참여자를 가져왔습니다.</p>
                <p><?php echo $count_prof_deleted; ?> 명의 교수, <?php echo $count_stud_deleted; ?> 명의 학생 참여자가 삭제되었습니다.</p>
            </div>
            <div id="btn_area">
                <input type="button" class="blue_btn" style="float:left; margin-right: 10px;" value="<?php echo get_string('okay','local_lmsdata'); ?>" onclick="location.href = 'sync.php?tab=<?php echo $tab; ?>&year=<?php echo $year; ?>&term=<?php echo $term; ?>'"/>
            </div>
            <?php
            local_haksa_scroll_down();
        }
        ?>
    </div><!--Content End-->

</div> <!--Contents End-->

<?php
include_once ('../inc/footer.php');
