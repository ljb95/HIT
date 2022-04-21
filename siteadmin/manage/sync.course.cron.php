<?php

ini_set('display_errors', '1');
ini_set('memory_limit', '2048M');
ini_set('max_execution_time', 0);

require(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once dirname(dirname(__FILE__)) . '/lib.php';
require_once $CFG->dirroot . '/local/haksa/lib.php';
require_once $CFG->dirroot . '/siteadmin/manage/synclib.php';

$year = get_config('moodle', 'haxa_year');
$term = get_config('moodle', 'haxa_term');

$i = date('G',time());
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
$hour_date = $DB->get_record('haksa_auto_sync',array('year'=>$year,'term'=>$term,'hour'=>$i));
if(!$hour_date){
    // 설정 동기화 시간에만 돌아야함
    die();
}

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
        while ($row = odbc_fetch_array($rs)) {
            $class = (object) array_change_key_case($row, CASE_LOWER);
            $view_class[$class->lec_cd.'_'.$class->bb]= $class;
            
            $class->haksa = $haksa->id;
//                        $class->univ = $class->univ;
//                        $class->major = $class->major;

            $bb = explode('-',$class->lec_cd)[1];

            $class->kor_lec_name = $class->kor_lec_name.'-'.$bb;
            $class->eng_lec_name = $class->eng_lec_name;
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
        
        //삭제 로직 추가
        $haksa_deletes = $DB->get_records('haksa_class', array('year'=>$year, 'term'=>$term));
        foreach($haksa_deletes as $haksa_delete) {
            if(empty($view_class[$haksa_delete->lec_cd.'_'.$haksa_delete->bb])) {
                $haksa_delete->deleted = 1;
                $DB->update_record('haksa_class', $haksa_delete);
            }
        }
    } else {
        echo 'Error: '.odbc_error($conn);
    }