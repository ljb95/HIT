<?php

require('../../config.php');

$all = optional_param('all', 0, PARAM_INT);
$haknum = optional_param('haknum', 0, PARAM_INT);

$strplural = get_string("final_check", "local_lmsdata");
$PAGE->navbar->add($strplural);
$PAGE->set_title($strplural);
$PAGE->set_heading($strplural);
//$course = get_course($id);
//$PAGE->set_course($course);
$PAGE->set_pagelayout('standard');

//$CFG->debug = (E_ALL | E_STRICT);
//$CFG->debugdisplay = 1;
//error_reporting($CFG->debug);
//ini_set('display_errors', '1');
//ini_set('log_errors', '1');

echo $OUTPUT->header();

$conn = odbc_connect($CFG->local_haksa_sid, $CFG->local_haksa_username, $CFG->local_haksa_password);
if ($haknum) {
    $uname = $haknum;
}
if ($conn) {
    if ($all) {
        $param = array('username' => $USER->username);
        $dates = "SELECT distinct YEAR,TERM FROM V_HAK_SCOR_INFO 
                             WHERE HAKBUN = :username";
        $count_sql1 = "SELECT SUM(HAKJUM) FROM V_HAK_SCOR_INFO   
                             WHERE CMPT_CD='00670002' AND HAKBUN = :username";
        $count_sql2 = "SELECT SUM(HAKJUM) FROM V_HAK_SCOR_INFO   
                             WHERE CMPT_CD='00670009' AND HAKBUN = :username";
        $count_sql3 = "SELECT SUM(HAKJUM) FROM V_HAK_SCOR_INFO   
                             WHERE CMPT_CD='00670003' AND HAKBUN = :username";
        $count_sql4 = "SELECT SUM(HAKJUM) FROM V_HAK_SCOR_INFO   
                             WHERE CMPT_CD='00670004' AND HAKBUN = :username";
    } else {
        $year = get_config('moodle', 'haxa_year');
        $term = get_config('moodle', 'haxa_term');
        $param = array('year' => $year, 'term' => $term, 'username' => $USER->username);
        $count_sql1 = "SELECT SUM(HAKJUM) as point FROM V_HAK_SCOR_INFO   
                             WHERE YEAR = :year 
                              AND TERM = :term AND CMPT_CD='00670002' AND HAKBUN = :username";
        $count_sql2 = "SELECT SUM(HAKJUM) as point FROM V_HAK_SCOR_INFO   
                             WHERE YEAR = :year 
                              AND TERM = :term AND CMPT_CD='00670009' AND HAKBUN = :username";
        $count_sql3 = "SELECT SUM(HAKJUM) as point FROM V_HAK_SCOR_INFO   
                             WHERE YEAR = :year 
                              AND TERM = :term AND CMPT_CD='00670003' AND HAKBUN = :username";
        $count_sql4 = "SELECT SUM(HAKJUM) as point FROM V_HAK_SCOR_INFO   
                             WHERE YEAR = :year 
                              AND TERM = :term AND CMPT_CD='00670004' AND HAKBUN = :username";
    }

    $cnt1 = odbc_prepare($conn, $count_sql1);
    $success = odbc_execute($cnt1, $param);
    if ($success) {
        $point1 = odbc_fetch_array($cnt1);
    }
    $cnt2 = odbc_prepare($conn, $count_sql2);
    $success2 = odbc_execute($cnt2, $param);
    if ($success2) {
        $point2 = odbc_fetch_array($cnt2);
    }
    $cnt3 = odbc_prepare($conn, $count_sql3);
    $success3 = odbc_execute($cnt3, $param);
    if ($success3) {
        $point3 = odbc_fetch_array($cnt3);
    }
    $cnt4 = odbc_prepare($conn, $count_sql4);
    $success4 = odbc_execute($cnt4, $param);
    if ($success4) {
        $point4 = odbc_fetch_array($cnt4);
    }
    $p1 = (int) $point1['point'];
    $p2 = (int) $point2['point'];
    $p3 = (int) $point3['point'];
    $p4 = (int) $point4['point'];
    $point5 = $point1['point'] + $point2['point'] + $point3['point'] + $point4['point'];
    //교양선택 00670002  $count_sql1
    //계열교양 00670009  $count_sql2
    //전공필수 00670003  $count_sql3
    //전공선택 00670004  $count_sql4
    //총 취득.
// 학생


    $usernaeme = fullname($USER);
    $checked ='';
    if($all){
        $checked = 'checked';
    }
    
    echo <<<EOD
    <div>
        <input type="radio" name="all" onclick="location.href='?all=0'" checked="">당학기조회 <input type="radio" onclick="location.href='?all=1'" name= "all" $checked>전체조회
    </div>
    <div>
    학번: $USER->username
    성명: $usernaeme 
        
    <table class="generaltable">
        <thead>
            <tr>
                <th>이수구분</th>
                <th>교양선택</th>
                <th>계열교양</th>
                <th>전공필수</th>
                <th>전공선택</th>
                <th>총 취득학점</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>취득학점</td>
                <td>$p1</td>
                <td>$p2</td>
                <td>$p3</td>
                <td>$p4</td>
                <td>$point5</td>
            </tr>
        </tbody>
    </table>    
</div>
EOD;
    if ($all) {
        $rs = odbc_prepare($conn, $dates);
        $success = odbc_execute($rs, $param);
        $data = array();
        if ($success) {
            $cnt = 1;
            while ($row = odbc_fetch_array($rs)) {
                $cnt = 0;
                $totalpoint = 0;
                $totalgpa = 0;
                $grade = (object) array_change_key_case($row, CASE_LOWER);
                if (!$grade->year || !$grade->term) {
                    continue;
                }
                $grade->year = iconv('euc-kr', 'utf-8', $grade->year);
                $grade->term = iconv('euc-kr', 'utf-8', $grade->term);
                switch ($grade->term) {
                    case 1: $term_txt = '1학기';
                        break;
                    case 2: $term_txt = '2학기';
                        break;
                    case 3: $term_txt = '여름학기';
                        break;
                    case 4: $term_txt = '겨울학기 당학기';
                        break;
                }
                
                 echo '<div style="text-align:center;"><h3>' . $grade->year . '학년도 ' . $term_txt . '</h3></div>';
                 echo <<<EOD_HEAD
        <table class="generaltable">
        <thead>
        <tr>
            <th>과목코드</th>
            <th>교과목명</th>
            <th>강좌</th>
            <th>이수</th>
            <th>학점</th>
            <th>등급</th>
            <th>평점</th>
            <th>특수</th>
            <th>복수</th>
        </tr>
        </thead>
        <tbody>
EOD_HEAD;

                $grades = "SELECT distinct * FROM V_HAK_SCOR_INFO 
                             WHERE YEAR = :year and TERM = :term and HAKBUN = :username";
                $params = array('year' => $grade->year, 'term' => $grade->term,'username'=>$USER->username);
                $op = odbc_prepare($conn, $grades);
                $exe = odbc_execute($op, $params);
                if ($exe) {
                    while($g = odbc_fetch_array($op)){
                        foreach ($g as $k => $v) {
                            $$k = iconv('euc-kr', 'utf-8', $v);
                            //echo $k . '=====>' . $$k . '<br>';
                        }
                        ?>
 <tr>
            <td><?php echo $LEC_CD; ?></td>
            <td><?php echo $KOR_LEC_NAME; ?></td>
            <td><?php echo $BB; ?></td>
            <td><?php echo $CMPT_CD; ?></td>
            <td><?php $totalpoint+=$HAKJUM; echo $HAKJUM; ?></td>
            <td><?php echo $GRADE; ?></td>
            <td><?php $totalgpa+=$GPA; echo $GPA; ?></td>
            <td><?php echo $SPCL_SUBJ; ?></td>
            <td> </td>
        </tr>
<?php
            $cnt++;
                    }
                    ?>
                </tbody>
        <tfoot>
            <tr>
                <th>합계</th>
                <th><?php echo $cnt; ?></th>
                <th> </th>
                <th>학점</th>
                <th><?php echo $totalpoint; ?></th>
                <th>평점</th>
                <th><?php echo $totalgpa; ?></th>
                <th> </th>
                <th> </th>
            </tr>
        </tfoot>
        </table>
        <?php
                }
            }
        }
    } else {

        $grades = "SELECT distinct * FROM V_HAK_SCOR_INFO 
                             WHERE YEAR = :year and TERM = :term and HAKBUN = :username";
        $params = array('year' => $year, 'term' => $term, 'username' => $USER->username);
        $op = odbc_prepare($conn, $grades);
        $exe = odbc_execute($op, $params);
        switch ($term) {
            case 1: $term_txt = '1학기 당학기 성적조회';
                break;
            case 2: $term_txt = '2학기 당학기 성적조회';
                break;
            case 3: $term_txt = '여름학기 당학기 성적조회';
                break;
            case 4: $term_txt = '겨울학기 당학기 성적조회';
                break;
        }
        echo '<div style="text-align:center;"><h3>' . $year . '학년도 ' . $term_txt . '</h3></div>';
echo <<<EOD_HEAD
        <table class="generaltable">
        <thead>
        <tr>
            <th>과목코드</th>
            <th>교과목명</th>
            <th>강좌</th>
            <th>이수</th>
            <th>학점</th>
            <th>등급</th>
            <th>평점</th>
            <th>특수</th>
            <th>복수</th>
        </tr>
        </thead>
        <tbody>
EOD_HEAD;
        if ($exe) {
            $cnt = 0;
            $totalpoint = 0;
            $totalgpa = 0;
            while ($g = odbc_fetch_array($op)) {
                foreach ($g as $k => $v) {
                    $$k = iconv('euc-kr', 'utf-8', $v);
            //        echo $k . '=====>' . $$k . '<br>';
                }
                ?>
        <tr>
            <td><?php echo $LEC_CD; ?></td>
            <td><?php echo $KOR_LEC_NAME; ?></td>
            <td><?php echo $BB; ?></td>
            <td><?php echo $CMPT_CD; ?></td>
            <td><?php $totalpoint+=$HAKJUM; echo $HAKJUM; ?></td>
            <td><?php echo $GRADE; ?></td>
            <td><?php $totalgpa+=$GPA; echo $GPA; ?></td>
            <td><?php echo $SPCL_SUBJ; ?></td>
            <td> </td>
        </tr>
<?php   
    $cnt++;
            }
        }
        ?>
        </tbody>
        <tfoot>
            <tr>
                <th>합계</th>
                <th><?php echo $cnt; ?></th>
                <th> </th>
                <th>학점</th>
                <th><?php echo $totalpoint; ?></th>
                <th>평점</th>
                <th><?php echo $totalgpa; ?></th>
                <th> </th>
                <th> </th>
            </tr>
        </tfoot>
        </table>
        <?php
    }
}

echo $OUTPUT->footer();
