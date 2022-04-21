<?php
require (dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once dirname(dirname (__FILE__)).'/lib.php';
require_once("$CFG->libdir/excellib.class.php");

require_login();

$year         = optional_param('year', 0, PARAM_INT);
$term         = optional_param('term', 0, PARAM_INT);

$terms = lmsdata_get_terms();
/* 학기명 */
$term_name = $terms[$term];


$query = "select 
    concat(c.id,u.username) as pk
    ,u.username,concat(u.firstname,u.lastname) as uname 
    ,lu.univ 
    , lu.major 
    ,lc.subject_id
    ,lc.bunban
    ,c.fullname
, n_b.noticeboard
,assign.useassign
,lcms.uselcms
,test.usetest + quiz.usequiz as usequiz
,attend.attend_cnt 
,q_b.qnaboard
,forum.useforum
,questionnaire.usequestionnaire 
from {course} c
    join {context} ctx on ctx.contextlevel = 50 and ctx.instanceid = c.id 
    join {lmsdata_class} lc on lc.course = c.id 
    join {role_assignments} ra on ra.contextid = ctx.id 
    join {role} r on r.id = ra.roleid and r.shortname = 'editingteacher' 
    join {user} u on u.id = ra.userid and deleted = 0
    join {lmsdata_user} lu on lu.userid = u.id 
    
    /* 공지사항 카운트 */
    left join (select jc.id,b.course,jc.userid,count(jc.id) as noticeboard from {jinotechboard} b 
    join {jinotechboard_contents} jc on jc.board = b.id
    where b.type = 1 group by b.course) 
    n_b on n_b.userid = u.id and n_b.course = c.id 

    /* 과제관리 카운트 */
    left join (select cm.course,count(cm.id) as useassign from {course_modules} cm 
    join {modules} m on m.id = cm.module and m.name = 'assign' group by cm.course) 
    assign on assign.course = c.id 

    /* 강의 자료 관리 */
    left join (select cm.course,count(cm.id) as uselcms from {course_modules} cm 
    join {modules} m on m.id = cm.module and m.name = 'resource' group by cm.course) 
    lcms on lcms.course = c.id 
    
    /* 시험관리  */
    left join (select courseid,count(id) as usetest  from {grade_categories} where fullname = '중간' or fullname = '기말') test on test.courseid = c.id 
    left join (select cm.course,count(cm.id) as usequiz from {course_modules} cm 
    join {modules} m on m.id = cm.module and m.name = 'quiz' group by cm.course) 
    quiz on quiz.course = c.id 
    
    /* 출석체크 관리 */
    left join (select course,count(course) as attend_cnt from v_attend_mod_cnt attend where modcnt != 0 group by course) attend on attend.course = c.id  
    
    /* 질의응답 카운트 */
    left join (select jc.id,b.course,jc.userid,count(jc.id) as qnaboard from {jinotechboard} b 
	join {jinotechboard_contents} jc on jc.board = b.id and jc.step = 1 
	where b.type = 2 group by b.course) q_b on q_b.userid = u.id and q_b.course = c.id
    
    /* 토론실 카운트 */
    left join (select cm.course,count(cm.id) as useforum from {course_modules} cm 
    join {modules} m on m.id = cm.module and m.name = 'forum' group by cm.course) 
    forum on forum.course = c.id 
    
    /* 설문 카운트 */
    left join (select cm.course,count(cm.id) as usequestionnaire from {course_modules} cm 
    join {modules} m on m.id = cm.module and m.name = 'questionnaire' group by cm.course) 
    questionnaire on questionnaire.course = c.id 
    where lc.year = :year and lc.term = :term";

$datas = $DB->get_records_sql($query,array('year'=>$year,'term'=>$term));

/* 엑셀 파일명 */
$filename = $year.'학년도 '.$term_name . '-교수학습개발센터.xls';

/* 엑셀 생성 */
$workbook = new MoodleExcelWorkbook('-');
$workbook->send($filename);

$worksheet = array();

/* 워크시트 0번 생성 */
$worksheet[0] = $workbook->add_worksheet('교수학습_전체');

$worksheet[0]->set_row(0, 40, array('align' => 'center', 'v_align' => 'center', 'text_wrap' => true));
$worksheet[0]->merge_cells(0, 1, 0, 17);
$worksheet[0]->write_string(0, 1, '교육영역 실적 확인 (교육지원시스템 사용여부)',array('bold'=>1,'size'=>20));

$worksheet[0]->write_string(1, 1, '학년도 및 학기 :'.$year.'학년도 '.$term_name,array('bold'=>1,'size'=>11));
$worksheet[0]->write_string(2, 1, '제출부서명 : 교수학습개발센터',array('bold'=>1,'size'=>11));

$worksheet[0]->write_string(1, 15, '지표 구분: 선택지표',array('bold'=>1,'size'=>11));
$worksheet[0]->write_string(2, 15, '작성자(구내번호):',array('bold'=>1,'size'=>11));

$fields = array(
    '사번',
    '교수 성명',
    '대학/대학원 명',
    '학과(전공)',
    '과목코드',
    '강좌번호',
    '과목명',
    '공지사항(1회 이상)',
    '과제관리(1개 이상)',
    '강의자료 관리(1회이상)',
    '시험관리(1회 이상)',
    '출석체크(13주)',
    '질의응답(1회 이상)',
    '토론실 운영(1회 이상)',
    '설문조사(1회 이상)',
    '비 고'
);

$row = 3;

$worksheet[0]->set_column(1, 2, 20);
$worksheet[0]->set_column(3, 3, 15);
$worksheet[0]->set_column(4, 17, 25);
$worksheet[0]->set_row(3, 25, array('align' => 'center', 'v_align' => 'center','bold'=>1, 'text_wrap' => true));
$col = 1;
foreach($fields as $fieldname){
    $worksheet[0]->write_string($row, $col, $fieldname, array('border' => 1,'bg_color'=>'#C5D9F1','size'=>11));
    $col++;
}

$row++;
foreach($datas as $data){
    $col = 1;
    $worksheet[0]->write_string($row, $col++, $data->username, array('border' => 1));
    $worksheet[0]->write_string($row, $col++, $data->uname, array('border' => 1));
    $worksheet[0]->write_string($row, $col++, $data->univ, array('border' => 1));
    $worksheet[0]->write_string($row, $col++, $data->major, array('border' => 1));
    $worksheet[0]->write_string($row, $col++, $data->subject_id, array('border' => 1));
    $worksheet[0]->write_string($row, $col++, $data->bunban, array('border' => 1));
    $worksheet[0]->write_string($row, $col++, $data->fullname, array('border' => 1));
    $worksheet[0]->write_string($row, $col++, $data->noticeboard, array('border' => 1));
    $worksheet[0]->write_string($row, $col++, $data->useassign, array('border' => 1));
    $worksheet[0]->write_string($row, $col++, $data->uselcms, array('border' => 1));
    $worksheet[0]->write_string($row, $col++, $data->usequiz, array('border' => 1));
    $worksheet[0]->write_string($row, $col++, $data->attend_cnt, array('border' => 1));
    $worksheet[0]->write_string($row, $col++, $data->qnaboard, array('border' => 1));
    $worksheet[0]->write_string($row, $col++, $data->useforum, array('border' => 1));
    $worksheet[0]->write_string($row, $col++, $data->usequestionnaire, array('border' => 1));
    $worksheet[0]->write_string($row, $col++, '', array('border' => 1,'size'=>11));
    $row++;
}

/* 1번시트 생성 */
$worksheet[1] = $workbook->add_worksheet('교수학습1');
$worksheet[1]->set_row(0, 40, array('align' => 'center', 'v_align' => 'center', 'text_wrap' => true));
$worksheet[1]->merge_cells(0, 1, 0, 13);
$worksheet[1]->write_string(0, 1, '교육영역 실적 확인 (교육지원시스템 사용여부)',array('bold'=>1,'size'=>20));

/* 학기명 */
$worksheet[1]->write_string(1, 1, '학년도 및 학기 :'.$year.'학년도 '.$term_name,array('bold'=>1,'size'=>11));
$worksheet[1]->write_string(2, 1, '제출부서명 : 교수학습개발센터',array('bold'=>1,'size'=>11));

$worksheet[1]->write_string(1, 11, '지표 구분: 선택지표',array('bold'=>1,'size'=>11));
$worksheet[1]->write_string(2, 11, '작성자(구내번호):',array('bold'=>1,'size'=>11));

$fields = array(
    '사번',
    '교수 성명',
    '대학/대학원 명',
    '학과(전공)',
    '과목코드',
    '강좌번호',
    '과목명',
    '출석체크(15주)',
    '질의응답(1회 이상)',
    '토론실 운영(1회 이상)',
    '설문조사(1회 이상)',
    '비 고'
);

$row = 3;

$worksheet[1]->set_column(1, 2, 20);
$worksheet[1]->set_column(3, 3, 15);
$worksheet[1]->set_column(4, 13, 25);
$worksheet[1]->set_row(3, 25, array('align' => 'center', 'v_align' => 'center','bold'=>1, 'text_wrap' => true));
$col = 1;
foreach($fields as $fieldname){
    $worksheet[1]->write_string($row, $col, $fieldname, array('border' => 1,'bg_color'=>'#C5D9F1','size'=>11));
    $col++;
}

$row++;
foreach($datas as $data){
    $col = 1;
    $worksheet[1]->write_string($row, $col++, $data->username, array('border' => 1));
    $worksheet[1]->write_string($row, $col++, $data->uname, array('border' => 1));
    $worksheet[1]->write_string($row, $col++, $data->univ, array('border' => 1));
    $worksheet[1]->write_string($row, $col++, $data->major, array('border' => 1));
    $worksheet[1]->write_string($row, $col++, $data->subject_id, array('border' => 1));
    $worksheet[1]->write_string($row, $col++, $data->bunban, array('border' => 1));
    $worksheet[1]->write_string($row, $col++, $data->fullname, array('border' => 1));
    $worksheet[1]->write_string($row, $col++, $data->attend_cnt, array('border' => 1));
    $worksheet[1]->write_string($row, $col++, $data->qnaboard, array('border' => 1));
    $worksheet[1]->write_string($row, $col++, $data->useforum, array('border' => 1));
    $worksheet[1]->write_string($row, $col++, $data->usequestionnaire, array('border' => 1));
    $worksheet[1]->write_string($row, $col++, '', array('border' => 1,'size'=>11));
    $row++;
}


/* 2번시트 생성 */
$worksheet[2] = $workbook->add_worksheet('교수학습2');
$worksheet[2]->set_row(0, 40, array('align' => 'center', 'v_align' => 'center', 'text_wrap' => true));
$worksheet[2]->merge_cells(0, 1, 0, 13);
$worksheet[2]->write_string(0, 1, '교육영역 실적 확인 (교육지원시스템 사용여부)',array('bold'=>1,'size'=>20));

/* 학기명 */
$worksheet[2]->write_string(1, 1, '학년도 및 학기 :'.$year.'학년도 '.$term_name,array('bold'=>1,'size'=>11));
$worksheet[2]->write_string(2, 1, '제출부서명 : 교수학습개발센터',array('bold'=>1,'size'=>11));

$worksheet[2]->write_string(1, 11, '지표 구분: 선택지표',array('bold'=>1,'size'=>11));
$worksheet[2]->write_string(2, 11, '작성자(구내번호):',array('bold'=>1,'size'=>11));

$fields = array(
    '사번',
    '교수 성명',
    '대학/대학원 명',
    '학과(전공)',
    '과목코드',
    '강좌번호',
    '과목명',
    '공지사항(1회 이상)',
    '과제관리(1개 이상)',
    '강의자료 관리(1회이상)',
    '시험관리(1회 이상)',
    '비 고'
);

$row = 3;

$worksheet[2]->set_column(1, 2, 20);
$worksheet[2]->set_column(3, 3, 15);
$worksheet[2]->set_column(4, 13, 25);
$worksheet[2]->set_row(3, 25, array('align' => 'center', 'v_align' => 'center','bold'=>1, 'text_wrap' => true));
$col = 1;
foreach($fields as $fieldname){
    $worksheet[2]->write_string($row, $col, $fieldname, array('border' => 1,'bg_color'=>'#C5D9F1','size'=>11));
    $col++;
}
$row++;
foreach($datas as $data){
    $col = 1;
    $worksheet[2]->write_string($row, $col++, $data->username, array('border' => 1));
    $worksheet[2]->write_string($row, $col++, $data->uname, array('border' => 1));
    $worksheet[2]->write_string($row, $col++, $data->univ, array('border' => 1));
    $worksheet[2]->write_string($row, $col++, $data->major, array('border' => 1));
    $worksheet[2]->write_string($row, $col++, $data->subject_id, array('border' => 1));
    $worksheet[2]->write_string($row, $col++, $data->bunban, array('border' => 1));
    $worksheet[2]->write_string($row, $col++, $data->fullname, array('border' => 1));
    $worksheet[2]->write_string($row, $col++, $data->noticeboard, array('border' => 1));
    $worksheet[2]->write_string($row, $col++, $data->useassign, array('border' => 1));
    $worksheet[2]->write_string($row, $col++, $data->uselcms, array('border' => 1));
    $worksheet[2]->write_string($row, $col++, $data->usequiz, array('border' => 1));
    $worksheet[2]->write_string($row, $col++, '', array('border' => 1,'size'=>11));
    $row++;
}


/* close 하지 않으면 엑셀이 다운로드 되지 않음. */
$workbook->close();

die();
//http://open.jinotech.com:63180/siteadmin/stats/course_all_excel.php?year=2017&term=1