<?php
require (dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once dirname(dirname (__FILE__)).'/lib/paging.php';
require_once dirname(dirname (__FILE__)).'/lib.php';

//년도가 없을 경우
if (!$year){
    $year = date('Y');
}
$excel_params = array();
$searchtext = optional_param('searchtext', '', PARAM_RAW);
$excel_params['searchtext'] = $searchtext;
$currpage     = optional_param('page', 1, PARAM_INT);
$perpage      = optional_param('perpage', 20, PARAM_INT);
$process = optional_param('process', 0, PARAM_INT);
$excel_params['process'] = $process;

//검색으로 나오는 년도, 학기
$syear = optional_param('syear', 0, PARAM_INT);
$sterm = optional_param('sterm', 0, PARAM_INT);
if(!$syear) {
    $syear = get_config('moodle', 'haxa_year'); 
}
if(!$sterm) {
    $sterm = get_config('moodle', 'haxa_term');
}
$excel_params['syear'] = $syear;
$excel_params['sterm'] = $sterm;

// 파라미터
$sql_params = array(
        'year'=>$syear,
        'term'=>$sterm,
        'contextlevel1'=>CONTEXT_COURSE
    );
// where절 부분
if (!empty($searchtext)) {
    $sql_conditions[] = ' (u.firstname like :searchtext1 or u.username like :searchtext2 ) or c.fullname like :searchtext3 ';
    $sql_params['searchtext1'] = '%'.$searchtext.'%';
    $sql_params['searchtext2'] = '%'.$searchtext.'%';
     $sql_params['searchtext3'] = '%'.$searchtext.'%';
}


    $sql_conditions[] = ' lc.isnonformal = :process ';
    $sql_params['process'] = $process;


if(!empty($sql_conditions)) {
    $sql_conditions = ' WHERE '.implode(' and ', $sql_conditions);
}else {
    $sql_conditions = '';
}
// 교수,강의,학과에 해당하는 웹디스크 자료목록, 무들 기본 자료 목록
$select = "select CONCAT(c.id, u.id) as pkid, c.id, u.id as userid, c.fullname, lc.subject_id, lc.bunban ,u.firstname ,u.username , lc.ohakkwa, lc.year, lc.term, u.institution, u.department , 
    notefile.notefilesize, notefile.notefilecount, notefile.notefirstdate , notefile.notelastdate,
    moodlefile.moodlefilesize, moodlefile.moodlefilecount, moodlefile.moodlefirstdate , moodlefile.moodlelastdate
    "; 
$from = " from {course} c 
        join {lmsdata_class} lc on lc.course = c.id and lc.year = :year and lc.term = :term
        join {context} con on con.instanceid = c.id and con.contextlevel = :contextlevel1
        join {role_assignments} ra on ra.contextid = con.id 
        join {role} r on r.id = ra.roleid and r.shortname = 'editingteacher' 
        join {user} u on u.id = ra.userid and u.deleted = 0
        left join (
        select cf.course,fi.userid, sum(fi.filesize) as notefilesize, count(fi.id) as notefilecount, min(fi.timemodified) as notefirstdate, max(fi.timemodified) as notelastdate 
            from {coursenote_forder} cf
            join {coursenote_file} fi on fi.forderid = cf.id
            group by cf.course
        ) notefile on notefile.course = c.id and notefile.userid = u.id
        left join (
                select r.course,f.userid , sum(f.filesize) as moodlefilesize, count(f.id) as moodlefilecount, min(f.timemodified) as moodlefirstdate, max(f.timemodified) as moodlelastdate 
            from {resource} r
            join {modules} m on m.name = 'resource'
            join {course_modules} cm on cm.instance = r.id and cm.module = m.id
            join {context} ctx on ctx.instanceid = cm.id and ctx.contextlevel = 70
            join {files} f on f.contextid = ctx.id and f.filename != '.' 
            group by r.course
        ) moodlefile on moodlefile.course = c.id  and moodlefile.userid = u.id ";

$groupby = " group by c.id, u.id ";
$orderby = "order by lc.ohakkwa asc ,c.id asc , u.id asc  ";

$course_lists = $DB->get_records_sql($select.$from.$sql_conditions.$groupby.$orderby,$sql_params, ($currpage-1)*$perpage, $perpage);
$count_courses = $DB->count_records_sql('select count(distinct c.id) '.$from.$sql_conditions ,$sql_params);

$count_coursenote = $count_courses-(($currpage-1)*$perpage);
?>

<?php include_once ('../inc/header.php');?>
<div id="contents">
    <?php include_once ('../inc/sidebar_stats.php');?>
    <div id="content">
        <h3 class="page_title">
            강의노트 현황
        </h3>
        <form class="search_area" id="note_search" style="text-align: left;">
            <input type="hidden" name="page" value="1" />
            <div class="stat_search_area">
                <b>년도</b> <select name = "syear" title="year">
                    <?php
                        for ($y = $year; $y >= 2015; $y--) {
                            if ($y == $syear) {
                                $selecte_y = "selected";
                            } else {
                                $selecte_y = "";
                            }
                            echo '<option value="' . $y . '" ' . $selecte_y . '>' . get_string('year', 'local_okregular', $y) . '</option>';
                        }
                    ?>
                </select>
                <b>학기</b> <select name = "sterm" title="term">
                <?php
                    $terms = lmsdata_get_terms();
                    foreach ($terms as $k => $t) {
                        if ($k == $sterm) {
                            $selecte_t = "selected";
                        } else {
                            $selecte_t = "";
                        }
                        echo '<option value="' . $k . '" ' . $selecte_t . '>' . $t . '</option>';
                    }
                ?>
                </select>
                <b>과정</b> <select name = "process" title="process">
                    <option value = "0" <?php if($process==0){ echo 'selected';} ?> >정규과정</option>
                    <option value = "1" <?php if($process==1){ echo 'selected';} ?> >비정규과정</option>
                </select>
                <input type ="text" name = "searchtext" placeholder="이름/교번 검색" value = "<?php echo $searchtext; ?>">
                <input type ="submit" class ="blue-btn" value = "검색">
            </div>
        </form>
        <div class="left">
            <form class="stat_form">
                <?php
                    echo '<input type ="hidden" name ="page" value="'.$currpage.'">';
                    echo '<input type ="hidden" name ="syear" value="'.$syear.'">';
                    echo '<input type ="hidden" name ="sterm" value="'.$sterm.'">';
                    echo '<input type ="hidden" name ="searchtext" value="'.$searchtext.'">';
                    echo '<input type ="hidden" name ="process" value="'.$process.'">';
                ?>
                <select name = "perpage" id = "perpage" onchange="perpagechange();">
                    <option value = "10">10</option>
                    <option value = "20">20</option>
                    <option value = "30">30</option>
                    <option value = "50">50</option>
                    <option value = "100">100</option>
                </select>
            </form>
        </div>
        <div class="right">
            <input type ="button" class ="blue-btn" value = "엑셀다운로드" onclick = "excel_list();">
        </div>
        <table>
            <thead>
                <tr rowspan="2">
                    <th rowspan="2"><?php echo get_string('number', 'local_lmsdata'); ?></th>
                    <th rowspan="2">교과목명</th>
                    <th rowspan="2">교과목코드</th>
                    <th rowspan="2">분반</th>
                    <th rowspan="2"><?php echo get_string('course_code', 'local_lmsdata'); ?></th>
                    <th rowspan="2">개설학과</th>
                    <th rowspan="2"><?php echo get_string('teachername', 'local_lmsdata'); ?></th>
                    <th rowspan="2"><?php echo get_string('stats_alternation', 'local_lmsdata'); ?></th>
                    <th rowspan="2">소속학과</th>
                    <th rowspan="2">직종</th>
                    <th colspan="17">주차(KB)</th>
                </tr>
                <tr>
                    <th>통입력</th>
                    <th>공통</th>
                    <th>1주차</th>
                    <th>2주차</th>
                    <th>3주차</th>
                    <th>4주차</th>
                    <th>5주차</th>
                    <th>6주차</th>
                    <th>7주차</th>
                    <th>8주차</th>
                    <th>9주차</th>
                    <th>10주차</th>
                    <th>11주차</th>
                    <th>12주차</th>
                    <th>13주차</th>
                    <th>14주차</th>
                    <th>15주차</th>
                </tr>
            </thead>
            <tbody>
                <?php
                    if($count_courses == 0){
                        echo '<tr>';
                            echo '<td colspan = "18">등록된 강의노트가 없습니다.</td>';
                        echo '</tr>';
                    }else{
                        $coursearrays = array();
                        foreach ($course_lists as $course_list){
                            
                           if($course_list->notefirstdate == null){
                                    $course_list->notefirstdate = '-';
                                }
                                if($course_list->notelastdate == null){
                                    $course_list->notelastdate = '-';
                                }
                            
                            if(!$course_list->notefilecount){
                                $course_list->notefilecount = '-';
                            }
                            if(!$course_list->moodlefilecount){
                                $course_list->moodlefilecount = '-';
                            }
                            
                            if(!empty($coursearrays[$course_list->id]['courseid'])) {
                                $coursearrays[$course_list->id]['profusername'] .= ','.$course_list->username;
                                $coursearrays[$course_list->id]['firstname'] .= ','.$course_list->firstname;
                                $coursearrays[$course_list->id]['userid'] .= ','. $course_list->userid;
                                
                                if(empty($coursearrays[$course_list->id]['notefilecount'])) {
                                    $coursearrays[$course_list->id]['notefilecount'] = $course_list->notefilecount;
                                }
                                if(empty($coursearrays[$course_list->id]['notefilecount'])) {
                                    $coursearrays[$course_list->id]['notefilecount'] = $course_list->notefilecount;
                                }
                                if(empty($coursearrays[$course_list->id]['notefilesize'])) {
                                    $coursearrays[$course_list->id]['notefilesize'] =(round($course_list->notefilesize/1024));
                                }
                                if(empty($coursearrays[$course_list->id]['notefirstdate'])) {
                                    $coursearrays[$course_list->id]['notefirstdate'] = $course_list->notefirstdate;
                                }
                                if(empty($coursearrays[$course_list->id]['notelastdate'])) {
                                    $coursearrays[$course_list->id]['notelastdate'] = $course_list->notelastdate;
                                }
                                if(empty($coursearrays[$course_list->id]['moodlefilecount'])) {
                                    $coursearrays[$course_list->id]['moodlefilecount'] = $course_list->moodlefilecount;
                                }
                                if(empty($coursearrays[$course_list->id]['moodlefilesize'])) {
                                    $coursearrays[$course_list->id]['moodlefilesize'] = $course_list->moodlefilesize;
                                }
                                if(empty($coursearrays[$course_list->id]['moodlefirstdate'])) {
                                    $coursearrays[$course_list->id]['moodlefirstdate'] = $course_list->moodlefirstdate;
                                }
                                if(empty($coursearrays[$course_list->id]['moodlelastdate'])) {
                                    $coursearrays[$course_list->id]['moodlelastdate'] = $course_list->moodlelastdate;
                                }
                            } else { 
                                $subject_id = explode('-', $course_list->subject_id);
                                $coursearrays[$course_list->id]['courseid'] =$course_list->id;
                                $coursearrays[$course_list->id]['fullname'] =$course_list->fullname;
                                $coursearrays[$course_list->id]['profusername'] = $course_list->username;
                                $coursearrays[$course_list->id]['firstname'] = $course_list->firstname;
                                $coursearrays[$course_list->id]['bunban'] = $course_list->bunban;
                                $coursearrays[$course_list->id]['subject_id'] = $subject_id[0];
                                $coursearrays[$course_list->id]['subjectid'] = $course_list->subject_id;
                                $coursearrays[$course_list->id]['department'] = $course_list->department;
                                $coursearrays[$course_list->id]['ohakkwa'] = $course_list->ohakkwa;
                                $coursearrays[$course_list->id]['institution'] = $course_list->institution;
                                $coursearrays[$course_list->id]['userid'] = $course_list->userid;
                                $coursearrays[$course_list->id]['notefilesize'] = round($course_list->notefilesize/1024); 
                            }

                        }
                    }
                    foreach($coursearrays as $coursearray) {
                        echo '<tr>';
                                echo '<td>'.$count_coursenote--.'</td>';
                                echo '<td>'.$coursearray['fullname'].'</td>';
                                $subject_id = explode('-', $course_list->subject_id);
                                echo '<td>'.$coursearray['subject_id'].'</td>';
                                echo '<td>'.$coursearray['bunban'].'</td>';
                                echo '<td>'.$coursearray['subjectid'].'</td>';
                                echo '<td>'.$coursearray['ohakkwa'].'</td>';
                                echo '<td>'.$coursearray['firstname'].'</td>';
                                echo '<td>'.$coursearray['profusername'].'</td>';
                                echo '<td>'.$coursearray['department'].'</td>';
                                echo '<td>'.$coursearray['institution'].'</td>';
                                echo '<td>'.$coursearray['notefilesize'].'</td>';
                                for($i = 0; $i<16; $i++){
                                    $select_section = " select r.course, sum(f.filesize) as filesize
                                        from {resource} r
                                        join {modules} m on m.name = 'resource'
                                        join {course_modules} cm on cm.instance = r.id and cm.module = m.id
                                        join {context} ctx on ctx.instanceid = cm.id and ctx.contextlevel = 70
                                        join {files} f on f.contextid = ctx.id and f.filename != '.' 
                                        join {course_sections} cs on cs.course = r.course and cs.section = :count and cs.id = cm.section
                                        where r.course = :courseid and f.userid in (:userid)
                                        group by r.course,cs.section, f.userid ";
                                    $courses_section = $DB->get_record_sql($select_section,array('count'=>$i,'courseid'=>$coursearray['courseid'],'userid'=>$coursearray['userid']));
                                    if(empty($courses_section)){
                                        $courses_section->filesize = '-';
                                    }
                                    echo '<td>'.round($courses_section->filesize/1024).'</td>';
                                }
                            echo '</tr>';
                    }
                ?>
            </tbody>
        </table>
        <?php
            print_paging_navbar_script($count_courses, $currpage, $perpage, 'javascript:cata_page(:page);');    
            // 엑셀에 보낼 파라미터값 정리
            $query_string = '';
            if (!empty($excel_params)) {
                $query_array = array();
                foreach ($excel_params as $key => $value) {
                    $query_array[] = urlencode($key) . '=' . urlencode($value);
                }
                $query_string = '?' . implode('&', $query_array);
            }
        ?>
    </div>
</div>

<script>
    
    $(document).ready(function(){
        // 페이지 리스트 갯수 select box 표기
        var perpage = <?php echo $perpage; ?>;

        if(perpage === 10){ $('#perpage option:eq(0)').attr("selected","selected");
        }else if(perpage === 20){ $('#perpage option:eq(1)').attr("selected","selected");
        }else if(perpage === 30){ $('#perpage option:eq(2)').attr("selected","selected");
        }else if(perpage === 50){ $('#perpage option:eq(3)').attr("selected","selected");
        }else if(perpage === 100){ $('#perpage option:eq(4)').attr("selected","selected");} 
    });
    // 엑셀 동작
    function excel_list(){
        loadingOn();
        window.open("note_list.excel.php<?php echo $query_string; ?>");
        loadingOff();
        
    }
    // 페이지 리스트 변경
    function perpagechange(){
        $('form.stat_form').submit();
    }
    // 페이징
    function cata_page(page) {
        $('[name=page]').val(page);
        $('form.stat_form').submit();
    }
    
    function loadingOn() {
        if ($("#loading").length == 0) {
            $("body").append("<div id='loading'></div>");
        }
    }
    function loadingOff() {
        $("#loading").remove();
    }
</script>
