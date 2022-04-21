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
    $SESSION->wantsurl = (string) new moodle_url('/siteadmin/manage/sync.user.import.php');
    redirect(get_login_url());
}
$context = context_system::instance();
require_capability('moodle/site:config', $context);

$tab = 0;

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
                if ($t['page'] == 'user') {
                    $css_class .= ' ' . $css_class . '_selected';
                    $tab = $i;
                }
                echo '<a href="sync.php?tab=' . $i . '"><p class="' . $css_class . '">' . $t['text'] . '</p></a>';
            }
            ?>
        </div><!--Content Navigation End-->

        <h4 class="page_sub_title"><?php echo get_string('user_sync','local_lmsdata'); ?></h4>
        <div class="extra_information">
            <p>학사시스템에서 사용자를 동기화하는 중입니다.</p>
            <p><?php echo get_string('wait_complete','local_lmsdata'); ?></p>
        </div>

        <div class="course_imported">
            <?php
            local_haksa_println('&nbsp;');

            $count = 0;
            $timesync = time();

            $existingusers = $DB->get_records_menu('user', array('deleted' => 0), '', 'username, id');

            $count_created = 0;

            $sql_st1 = "SELECT 
                lu.USERNAME AS username, 
                lu.BIRTH_DT AS password, 
                lu.USERNAME_KOR AS user_nm,
                lu.USERNAME_ENG AS eng_name,
                lu.EMAIL AS email,
                lu.TEL_NO AS phone1, 
                lu.HP_NO AS phone2,
                lu.USERNAME AS idnumber,
                lu.USERGROUP_CD,
                lu.USERGROUP,
                lu.UNIV_CD AS univ_cd, 
                lu.UNIV AS univ,
                lu.MAJOR_CD AS major_cd, 
                lu.DEPT_CD as dept_cd, 
                lu.DEPT_NM as dept,
                lu.DEPT_NM as department,
                lu.USERGROUP as institution,
                lu.MAJOR_NM as major,
                lu.DAY_TM_CD,
                lu.NATION_CD, 
                0 AS b_temp, 
                0 AS b_mobile, 
                0 AS b_email,  
                0 AS b_tel, 
                0 AS b_univ, 
                0 AS b_major, 
                ' ' AS ehks, 
                ' ' AS edhs, 
                ' ' AS domain, 
                ' ' AS hyhg, 
                ' ' AS persg, 
                lu.DEPT_NM AS psosok, 
                'manual' AS auth,
                0 AS suspended,
                0 AS maildisplay,
                1 AS mailformat,
                0 AS maildigest,
                1 AS autosubscribe,
                1 AS trackforums,
                99 AS timezone,
                ' ' AS calendartype,
                0 AS descriptiontrust,
                ' ' AS description,
                1 AS descriptionformat,
                1 AS mnethostid,
                1 AS confirmed,
                0 AS timemodified,
                ' ' AS address,
                lu.SEX AS sex,	
                lu.HYEAR AS hyear, 
                lu.EXAM_ID AS ex_num,
                vh.STAUTS AS status,
                lu.STATE_NM as status_nm
            FROM LMS_USER lu
            LEFT JOIN VHSC_HAKSA vh ON vh.USER_ID = lu.USERNAME";
            
            // sex 10 = 남자 20 = 여자
             
            //  EXAN_NUMB   EMAIL 추가 되어야함 아직 없음  NATION_CD 관련 테이블도 없음
            
            // status 1 = 재학, 2 = 휴학, 3 = 제적(퇴학등), 4 = 수료, 5 = 졸업
            
            $conn = siteadmin_sync_db_connect();
            $rs = odbc_exec ($conn, $sql_st1);
            // text가 잘려나오지 않도록 1 MB로 설정
            odbc_longreadlen($rs, 1048576);
            while($row = odbc_fetch_array($rs)) {
                $usernew = (object) array_change_key_case($row, CASE_LOWER);
                
                $usernew->password = substr(str_replace('-','',$usernew->password),2,6);
               
               if(empty($usernew->department)) continue;
                if(strtolower(trim($usernew->usergroup_cd)) == 'sl'){
                    $usernew->usergroup = 'rs';
                } else {
                    $usernew->usergroup = 'pr';
                } 
                $usernew->univ = siteadmin_sync_encode($usernew->univ);

                $usernew->firstname = $usernew->user_nm; 
                $usernew->lastname = '　'; 
                $usernew->timemodified = $timesync; 
                $usernew->timecreated = $usernew->timemodified; 
                 
                $usernew->email = siteadmin_sync_validate_email($usernew->email);
                
                if(empty(trim($usernew->email))){
                    $usernew->email = $usernew->username.'@hit.ac.kr';
                }
                
                $usernew->phone1 = siteadmin_sync_validate_phonenumber($usernew->phone1);
                $usernew->phone2 = siteadmin_sync_validate_phonenumber($usernew->phone2);
                if(empty($usernew->eng_name)) {
                    $usernew->eng_name = $usernew->user_nm;
                }
                
                if(empty($usernew->hyear)) {
                    $usernew->hyear = 0;
                }
                
                if($usernew->nation_cd == 'KO'){
                    $usernew->nation_cd = '410';
                } else {
                    $usernew->nation_cd = '';
                }
                // nation_cd
                $usernew->lang = 'ko';
                
                if($usernew->username != $usernew->ex_num && $usernew->usergroup == 'rs'){
                    $ex_user = $DB->get_record('user',array('username'=>$usernew->ex_num,'firstname'=>$usernew->firstname ,'lastname'=>$usernew->lastname));
                        if($ex_user){
                                $DB->update_record('user',array('id'=>$ex_user->id,'username'=>$usernew->username));
                                $existingusers[$usernew->username] = $ex_user->id;
                            }
                }  
                    //수험번호
                    $userid = local_haksa_create_user($usernew, $existingusers);                
                    if ($userid > 0) {
                        $usernew->userid = $userid;
                        siteadmin_insert_or_update_lmsuserdata($usernew);
                        
                        $count_created += 1; 
                        
                        $DB->update_record('user',array('id'=>$userid,'idnumber'=>$usernew->idnumber
                                ,'firstname'=>$usernew->firstname,'lastname'=>$usernew->lastname,'email'=>$usernew->email,'department'=>$usernew->department
                                ,'institution'=>$usernew->institution));
                        
                        local_haksa_println($count_created . '. 아이디:' . $usernew->username . ', 이름:' . $usernew->firstname.' '.$usernew->lastname);
                        
                        
                        $allow = array(11,12,13,16,19,'SL');
                            if(!in_array($usernew->usergroup_cd,$allow)){
                                
                               
                            $cate = array();
                            local_haksa_get_course_categories($cate);
                            $path = array();
                            $path[] = $DB->get_field_sql('SELECT name FROM {course_categories} WHERE idnumber = :idnumber', array('idnumber'=>'oklass_regular'));
                            $path[] = $DB->get_field_sql('SELECT name FROM {course_categories} WHERE idnumber = :idnumber', array('idnumber'=>'HIT'));
                            $path[] = $usernew->dept;
                            $haksa_class->category = local_haksa_find_or_create_category($path, $cate);
                        }
                        
                    }
            }
            odbc_free_result($rs);
            siteadmin_sync_db_close($conn);
            
            $history = new stdClass();
            $history->timestart = $timesync;
            $history->timeend = time();
            $history->usercount = $count_created;
            $DB->insert_record('haksa_user_history', $history);
            ?>
        </div>

        <div class="extra_information">
            <p><?php echo $count_created; ?> 명의 사용자를 동기화했습니다.</p>
        </div>
        <div id="btn_area">
            <input type="button" class="blue_btn" style="float:left; margin-right: 10px;" value="<?php echo get_string('okay','local_lmsdata'); ?>" onclick="location.href = 'sync.php?tab=<?php echo $tab; ?>'"/>
        </div>
        <?php
        local_haksa_scroll_down();
        ?>
    </div><!--Content End-->
</div> <!--Contents End-->
<?php
include_once ('../inc/footer.php');
