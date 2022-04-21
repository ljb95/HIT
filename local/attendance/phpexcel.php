<?php

// 무들 기본 CFG 호출
require_once('../../config.php');
// 엑셀 라이브러리 호출
require_once('lib.php');
require_once("$CFG->libdir/excellib.class.php");
require_once $CFG->libdir . '/formslib.php';
require_once $CFG->dirroot . '/course/lib.php';

require_login();

$id = required_param('id', PARAM_INT); // course id
$search = optional_param('search', '', PARAM_CLEAN);
$nullcnt = optional_param('nullcnt', 0, PARAM_INT);

$course = get_course($id);

$section_cnt = $DB->get_record('course_format_options', array('courseid' => $id, 'name' => 'numsections', 'format' => $course->format));

$usersearch = '';
$params = array('courseid' => $id, 'nullcnt' => $nullcnt);
if ($search) {
    $usersearch = 'and (u.username like :search1 or concat(u.firstname,u.lastname) like :search2)';
    $params['search1'] = $params['search2'] = '%' . $search . '%';
}
$user_select = "select u.id ,c.id as course, IF(cnt.cnt is null ,0,cnt.cnt) as cnt";

for ($i = 1; $i <= $section_cnt->value; $i++) {
    if (date('Ymd', $course->startdate + (1 * 60 * 60 * 24 * 7 * ($i))) > date('Ymd')) {
        $params['sec'] = $i;
        break; 
    }
}

if(!isset($params['sec'])){
     $params['sec'] = 9999;
}
$user_query = " from {course} c
    join {context} ctx on ctx.contextlevel = 50 and ctx.instanceid = c.id 
    join {role_assignments} ra on ra.contextid = ctx.id 
    join {role} r on r.id = ra.roleid and r.shortname = 'student' 
    join {user} u on u.id = ra.userid $usersearch 
         left join (select mc.course,userid,count(*) as cnt from v_attend_mod_cnt mc
join (select userid,course,section,sum(progress) as nullcnt from v_user_attend group by userid,course,section) atd 
on atd.course = mc.course and atd.section = mc.section and modcnt != 0
where nullcnt = modcnt and mc.section < :sec group by mc.course,userid) cnt on cnt.userid = u.id and cnt.course = c.id  
where c.id = :courseid 
   ";
$order = ' order by u.firstname asc , u.lastname asc , u.username desc ';
$having = '';
if ($nullcnt) {
    $having = 'having cnt >= :nullcnt';
}
// 강의에 등록된 유저가져오기
$users = $DB->get_records_sql($user_select . $user_query . $having.$order, $params, $offset, $perpage);
$users_cnt = $DB->get_records_sql($user_select . $user_query. $having, $params);
$users_cnt = count($users_cnt);

$fields = array(
    '번호',
    '학과',
    '학번',
    '이름'
);

$activities = array();  // 주차별 액티비티명이 들어갈 배열
$sec_colspan = array(); // 섹션 콜스판(액티비티의 갯수) 를 담기위한 배열
for ($i = 1; $i <= $section_cnt->value; $i++) {
    if (date('Ymd', $course->startdate + (1 * 60 * 60 * 24 * 7 * ($i))) > date('Ymd')) {
        break;
    }
    $section_activities = $DB->get_records_sql('select * from v_attend_mod where course = :courseid and section = :section', array('courseid' => $id, 'section' => $i));
    $sec_colspan[$i] = 0; // 기본 콜스판 선언
    foreach ($section_activities as $section_activity) {
        $activities[$i][$section_activity->id] = $section_activity->act; // 생성된 뷰테이블의 액티비티명  tbl , lcms , off (오프라인출석부) 정보
        $sec_colspan[$i] ++; // 액티비티가 존재함으로 콜스판 증가
        if ($sec_colspan[$i] == 3) {
            break;
        }
    }
    if (!$section_activities) { // 등록된 액티비티가 없을경우 콜스판을 1로 늘리고 - 표시함
        // $activities[$i][0] = ' ';
        $sec_colspan[$i] = 3;
    }
}


$filename = 'user_attend_' . date('Y-m-d') . '.xls';


$workbook = new MoodleExcelWorkbook('-');
$workbook->send($filename);

$worksheet = array();

$worksheet[0] = $workbook->add_worksheet('');
$col = 0;

// 로우 스타일 지정
//$worksheet[0]->set_row(0,30,array('color'=>'white','bg_color'=>'black'));
$worksheet[0]->set_row(0, 20, array('align' => 'center', 'v_align' => 'center', 'text_wrap' => true));
$worksheet[0]->set_row(1, 20, array('align' => 'center', 'v_align' => 'center', 'text_wrap' => true));
$worksheet[0]->set_row(2, 20, array('align' => 'center', 'v_align' => 'center', 'text_wrap' => true));
$worksheet[0]->set_column(0, 0, 8);
$worksheet[0]->set_column(1, 1, 12);
$worksheet[0]->set_column(2, 2, 12);
$worksheet[0]->set_column(3, 3, 8);
$worksheet[0]->merge_cells(0, 0, 2, 0);
$worksheet[0]->merge_cells(0, 1, 2, 1);
$worksheet[0]->merge_cells(0, 2, 2, 2);
$worksheet[0]->merge_cells(0, 3, 2, 3);

foreach ($fields as $fieldname) {
    $worksheet[0]->write_string(0, $col, $fieldname, array('border' => 1));
    $worksheet[0]->write_string(1, $col, '', array('border' => 1));
    $worksheet[0]->write_string(2, $col, '', array('border' => 1));
    $col++;
}
$firstcol = $col;
for ($i = 1; $i <= $section_cnt->value; $i++) {
    if (date('Ymd', $course->startdate + (1 * 60 * 60 * 24 * 7 * ($i))) > date('Ymd')) {
        break;
    }
    $worksheet[0]->write_string(0, $col, $i . get_string('week','local_attendance'), array('border' => 1));
    $worksheet[0]->write_string(0, $col + 1, '', array('border' => 1));
    $worksheet[0]->write_string(0, $col + 2, '', array('border' => 1));
    $worksheet[0]->merge_cells(0, $col, 0, $col + 2);
    $col += 3;
}
$col = $firstcol;
for ($i = 1; $i <= $section_cnt->value; $i++) {
    if (date('Ymd', $course->startdate + (1 * 60 * 60 * 24 * 7 * ($i))) > date('Ymd')) {
        break;
    }
    $sectionname = get_section_name($course, $i);
    $worksheet[0]->write_string(1, $col, $sectionname, array('border' => 1));
    $worksheet[0]->write_string(1, $col + 1, '', array('border' => 1));
    $worksheet[0]->write_string(1, $col + 2, '', array('border' => 1));
    $worksheet[0]->merge_cells(1, $col, 1, $col + 2);
    $col += 3;
}
$col = $firstcol;
for ($i = 1; $i <= $section_cnt->value; $i++) {
    if (date('Ymd', $course->startdate + (1 * 60 * 60 * 24 * 7 * ($i))) > date('Ymd')) {
        break;
    }
    $first = current($activities[$i]);
    $second = next($activities[$i]);
    $third = next($activities[$i]);
        
    switch($first){
        case 'tbl': $first = 'SC강의'; break;
        case 'lcms': $first = 'VOD'; break;
        default: $first = '출석'; break;
    }
    switch($second){
        case 'tbl': $second = 'SC강의'; break;
        case 'lcms': $second = 'VOD'; break;
        default: $second = '출석'; break;
    }
    switch($third){
        case 'tbl': $third = 'SC강의'; break;
        case 'lcms': $third = 'VOD'; break;
        default: $third = '출석'; break;
    } 
    
    if (count($activities[$i]) > 2 && $first == $second && $second == $third) { // 1 to 3 같은경우
        $worksheet[0]->write_string(2, $col, current($activities[$i]), array('border' => 1));
        $worksheet[0]->write_string(2, $col + 1, '', array('border' => 1));
        $worksheet[0]->write_string(2, $col + 2, '', array('border' => 1));
        $worksheet[0]->merge_cells(2, $col, 2, $col + 2);
        $col += 3;
    } else if (count($activities[$i]) > 2) {
        $worksheet[0]->write_string(2, $col++, $first, array('border' => 1));
        $worksheet[0]->write_string(2, $col++, $second, array('border' => 1));
        $worksheet[0]->write_string(2, $col++, $third, array('border' => 1));
    } else if (count($activities[$i]) == 1) {  // 하나만 있을경우
        $worksheet[0]->write_string(2, $col, $first, array('border' => 1));
        $worksheet[0]->write_string(2, $col + 1, '', array('border' => 1));
        $worksheet[0]->write_string(2, $col + 2, '', array('border' => 1));
        $worksheet[0]->merge_cells(2, $col, 2, $col + 2);
        $col += 3;
    } else if (count($activities[$i]) == 2) {
        if ($first == $second) {
            $worksheet[0]->write_string(2, $col, $first, array('border' => 1));
            $worksheet[0]->write_string(0, $col + 1, '', array('border' => 1));
            $worksheet[0]->merge_cells(2, $col, 2, $col + 1);
            $worksheet[0]->write_string(2, $col + 2, ' ', array('border' => 1));
            $col += 3;
        } else {
            $worksheet[0]->write_string(2, $col++, $first, array('border' => 1));
            $worksheet[0]->write_string(2, $col++, $second, array('border' => 1));
            $worksheet[0]->write_string(2, $col++, ' ', array('border' => 1));
        }
    } else {
        $worksheet[0]->write_string(2, $col, ' ', array('border' => 1));
        $worksheet[0]->write_string(2, $col + 1, '', array('border' => 1));
        $worksheet[0]->write_string(2, $col + 2, '', array('border' => 1));
        $worksheet[0]->merge_cells(2, $col, 2, $col + 2);
        $col += 3;
    }
}
$worksheet[0]->set_column(4, $col, 8);

$row = 3;
foreach ($users as $user) {
    $worksheet[0]->set_row($row, 20, array('align' => 'center', 'v_align' => 'center', 'text_wrap' => true));
    $col = 0;
    $nullcnt = $user->nullcnt;
    $user = $DB->get_record('user', array('id' => $user->id));
    $lmsdata_user = $DB->get_record('lmsdata_user', array('userid' => $user->id));
    $worksheet[0]->write_string($row, $col++, $users_cnt--, array('border' => 1));
    $worksheet[0]->write_string($row, $col++, $lmsdata_user->major, array('border' => 1));
    $worksheet[0]->write_string($row, $col++, $user->username, array('border' => 1));
    $worksheet[0]->write_string($row, $col++, fullname($user), array('border' => 1));
    for ($i = 1; $i <= $section_cnt->value; $i++) {
        if (date('Ymd', $course->startdate + (1 * 60 * 60 * 24 * 7 * ($i))) > date('Ymd')) {
            break;
        }
        reset($activities[$i]);
        for($j=1; $j<= 3; $j++){
            
            $activity = current($activities[$i]);
            $instance = key($activities[$i]);
            $params  = array('id' => $instance, 'userid' => $user->id, 'courseid' => $id, 'section' => $i);
            $att = ' ';
            switch ($activity) {
                    case 'off':
                        $query = "select * from {local_off_attendance_section} oas
                        join {local_off_attendance_status} att on att.lastcode = oas.code 
                        where oas.id = :id and att.userid = :userid";
                        $attend = $DB->get_record_sql($query,$params);
                         switch ($attend->status) {
                            case '0':
                            case '2':
                                $att = 'X';
                                break;
                            case '1':
                                $att = 'O';
                                break;
                            case '4':
                                $att = '□';
                            case '3':
                                $att = '△';
                                break;
                            default:
                                $att = 'X';
                                break;
                        }
                        break;
                    case 'tbl':
                        $attend = $DB->get_record('tbl_attend',array('tblid'=>$instance,'userid'=>$user->id));
                        switch ($attend->finalstatus) {
                            case '0':
                            case '2':
                                $att = 'X';
                                break;
                            case '1':
                                $att = 'O';
                                break;
                            case '4':
                                $att = '□';
                            case '3':
                                $att = '△';
                                break;
                            default:
                                $att = 'X';
                                break;
                        }
                        break;
                    case 'lcms':
                        $attend = $DB->get_record('lcms_track',array('lcms'=>$instance,'userid'=>$user->id));
                         if ($attend->progress == 100) {
                            $att = 'O';
                        } else if ($attend->progress) {
                            $att = '△';
                        } else {
                            $att = 'X';
                        }
                        break;
                }
            
            if ($att) {
                $worksheet[0]->write_string($row, $col++, $att, array('border' => 1));
            } else {
                $worksheet[0]->write_string($row, $col++, ' ', array('border' => 1));
            }
            next($activities[$i]);
        }
    }
    $row++;
}


$workbook->close();
die;
