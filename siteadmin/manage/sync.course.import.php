<?php
ini_set('display_errors', '1');
ini_set('memory_limit', '2048M');
ini_set('max_execution_time', 0);

require(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once dirname(dirname(__FILE__)) . '/lib.php';
require_once $CFG->dirroot . '/local/haksa/lib.php';
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
            <div class="extra_information"><?php echo get_string('msg5','local_lmsdata'); ?></div>
            <div id="btn_area">
                <input type="submit" class="blue_btn" style="float:left; margin-right: 10px;" value="<?php echo get_string('okay','local_lmsdata'); ?>" onclick="sync_goto_config('<?php echo $key; ?>')"/>
            </div>
            <?php
        } else {
            $terms = siteadmin_get_terms_sync();
            ?>
            <h4 class="page_sub_title">강의 동기화</h4>
            <div class="extra_information">
                <p>학사시스템에서 <?php echo $year; ?> <?php echo get_string('year2','local_lmsdata'); ?> <?php echo $terms[$term]; ?> 강의를 가져오는 중입니다.</p>
                <p><?php echo get_string('wait_complete','local_lmsdata'); ?></p>
            </div>

            <div class="course_imported">
                <?php
                local_haksa_println('');

                $sql_haksa =  "SELECT 
                'HIT' as domain
                , so.YEAR AS year
                , so.TERM AS term
                , so.LEC_CD as hakno 
                , sodp.CLS_DIV AS BB
                , ' ' AS SBB 
                , so.KOR_LEC_NAME AS kor_lec_name
                , so.ENG_LEC_NAME AS eng_lec_name
                , ' ' AS summary
                , so.HAKJUM AS hakjum
                , 1 AS gubun  
                , CASE when deptnm.dept_nm is not null THEN deptnm.dept_nm ELSE 'commonculture' end AS cata2
                , so.LEC_CD AS lec_cd
                , so.LEC_CD AS ohakkwa 
                , sodp.SCHL_YR AS hyear
                , sodp.DAY_TM_CD AS day_tm_cd 
                , 0 AS deleted
                , KOR_LEC_NAME AS shortname 
            FROM SMARTCAMPUS.dbo.LMS_SO so 
            JOIN SMARTCAMPUS.dbo.LMS_SO_DEPT sodp on sodp.LEC_CD = so.LEC_CD and sodp.YEAR = so.YEAR and sodp.term = so.term 
            LEFT JOIN DHDB.dbo.COT_DEPT deptnm  on deptnm.DEPT_CD = sodp.DEPT_CD 
            WHERE so.YEAR = $year and so.term = $term "; 
                //$param = array('year'=>$year, 'term'=>$term);
                
                $conn = siteadmin_sync_db_connect();
                $rs = odbc_prepare ($conn, $sql_haksa);
                // article_text가 잘려나오지 않도록 1M로 설정 
                odbc_longreadlen($rs, 1048576);
                $success = odbc_execute($rs);
                if($success) {
                    $count = 0;
                    $ucount = 0;
                    while ($row = odbc_fetch_array($rs)) {
                        $class = (object) array_change_key_case($row, CASE_LOWER);
                        $view_class[$class->lec_cd.'_'.$class->bb]= $class;
                        
                        $class->haksa = $haksa->id;
//                        $class->univ = $class->univ;
//                        $class->major = $class->major;
                        
                        $bb = explode('-',$class->lec_cd)[1];
                        
                        $class->kor_lec_name = trim($class->kor_lec_name).'-'.$bb;
                        $class->eng_lec_name = trim($class->eng_lec_name);
                        $class->cata1 = '대전보건대학교';
                        $class->cata2 = $class->cata2;
                        
                        if($class->cata2 == 'commonculture') {
                            $class->cata2 = '공통교양';
                        }
                        // 이미 가져왔으면 update, 없으면 insert
                        $haksaclass = $DB->get_record('haksa_class', array(
                            'year' => $class->year,
                            'term' => $class->term,
                            'lec_cd' => $class->lec_cd,
                            'bb' => $class->bb));
                        if (!empty($haksaclass)) {

                            $haksaclass->kor_lec_name = $class->kor_lec_name;
                            $haksaclass->eng_lec_name = $class->eng_lec_name;
                            $haksaclass->prof_name = $class->prof_name;
                            $haksaclass->prof_cd = $class->prof_cd;
                            $haksaclass->gubun = $class->gubun;
                            $haksaclass->cata2 = $class->cata2;
                            $haksaclass->hyear = $class->hyear;
                            $haksaclass->day_tm_cd = $class->day_tm_cd;
                            $haksaclass->deleted = 0; 
                            
                            $DB->update_record('haksa_class', $haksaclass);
                            $ucount++;
                        } else {
                            $DB->insert_record('haksa_class', $class);
                            $count += 1;

                            local_haksa_println($class->kor_lec_name . '(과목코드:' . $class->lec_cd . ')');
                        }
                    }
                    odbc_free_result($rs);
                    siteadmin_sync_db_close($conn);

                    $haksa->timesynccourse = time();
                    $DB->update_record('haksa', $haksa);
                } else {
                    echo 'Error: '.odbc_error($conn);
                }
                
                //삭제 로직 추가
                $haksa_deletes = $DB->get_records('haksa_class', array('year'=>$year, 'term'=>$term));
                foreach($haksa_deletes as $haksa_delete) {
                    if(empty($view_class[$haksa_delete->lec_cd.'_'.$haksa_delete->bb])) {
                        $haksa_delete->deleted = 1;
                        $DB->update_record('haksa_class', $haksa_delete);
                    }
                }
                ?>
            </div>

            <div class="extra_information">
                <p><?php echo $count; ?> 개의 강의를 가져왔습니다.</p>
                <p><?php echo $ucount; ?> 개의 강의를 업데이트 했습니다.</p>
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
