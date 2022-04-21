<?php
require(dirname(dirname(dirname(__FILE__))) . '/config.php');

// Check for valid admin user - no guest autologin
require_login(0, false);
if (isguestuser()) {
    $SESSION->wantsurl = (string) new moodle_url('/siteadmin/support/sms.php');
    redirect(get_login_url());
}
$context = context_system::instance();
require_capability('moodle/site:config', $context);
require_once dirname(dirname(__FILE__)) . '/lib/paging.php';
require_once dirname(dirname (__FILE__)).'/lib.php';

$field = optional_param('field', '', PARAM_RAW);
$search_val = optional_param('search_val', '', PARAM_RAW);
$startd = optional_param('startd', '', PARAM_RAW);
$endd = optional_param('endd', '', PARAM_RAW);

$currpage = optional_param('page', 1, PARAM_INT);
$perpage = optional_param('perpage', 10, PARAM_INT);

$PAGE->set_context($context);
$PAGE->set_url('/siteadmin/support/exams.php');


$conditions = array();
$params = array();

$value = $search_val;

$term_arr =lmsdata_get_terms();

$year         = optional_param('year', date('Y'), PARAM_INT);
$term         = optional_param('term', get_config('moodle', 'haxa_term'), PARAM_RAW);

$hyear        = optional_param('hyear', '', PARAM_RAW);
$dept       = optional_param('dept', '', PARAM_RAW);
$juya         = optional_param('juya', 0, PARAM_INT);
$bunban       = optional_param('bunban', '', PARAM_RAW);

$search       = optional_param('search', 0, PARAM_INT);
$searchtext   = optional_param('searchtext', '', PARAM_TEXT);
$tag_searchtext   = optional_param('tag_searchtext', '', PARAM_TEXT);

$dept_sql = "select distinct ohakkwa from {lmsdata_class} ORDER BY ohakkwa ASC ";
$dept_lists = $DB->get_records_sql($dept_sql,array());

$bunban_sql = "select distinct bunban from {lmsdata_class} ORDER BY bunban ASC ";
$bunban_lists = $DB->get_records_sql($bunban_sql,array());

if(!empty($year)) {
    $conditions[] = " lcl.year = :year ";
    $params['year'] = $year;
}

if(!empty($term)) {
    $conditions[] = " lcl.term = :term ";
    $params['term'] = $term;
}

if(!empty($searchtext)) {
    
    switch($search) {
        case 0: // 전체
            $conditions[]= '( '.$DB->sql_like('lcl.subject_id', ':subject_id').' or '.$DB->sql_like('c.fullname', ':fullname').' or '.$DB->sql_like('firstname', ':profname_fi') .' or '.$DB->sql_like('lastname', ':profname_la') . ')';
            $params['subject_id'] = '%'.$searchtext.'%';
            $params['fullname'] = '%'.$searchtext.'%';
            $params['profname_fi'] = '%'.$searchtext.'%';
            $params['profname_la'] = '%'.$searchtext.'%';
            break;
        case 1: // 강의코드
            $conditions[]= $DB->sql_like('lcl.subject_id', ':subject_id');
            $params['subject_id'] = '%'.$searchtext.'%';
            break;
        case 2: // 강의명
            $conditions[] = $DB->sql_like('c.fullname', ':fullname');
            $params['fullname'] = '%'.$searchtext.'%';
            break;
        case 3: // 교수명
            $conditions[] = '( '.$DB->sql_like('firstname', ':profname_fi').' or '.$DB->sql_like('lastname', ':profname_la').')';
            $params['profname_fi'] = '%'.$searchtext.'%';
            $params['profname_la'] = '%'.$searchtext.'%';
            break;
        default:
            break;
    }
}

if ($hyear!='') {
        $conditions[] = $DB->sql_like('lcl.hyear', ':hyear');
        $params['hyear'] = '%' . $hyear . '%';
}
if ($juya!='') {
        $conditions[] = $DB->sql_like('lcl.day_tm_cd', ':juya');
        $params['juya'] = '%' . $juya . '%';
}
if ($bunban!='') {
        $conditions[] = $DB->sql_like('lcl.bunban', ':bunban');
        $params['bunban'] = '%' . $bunban . '%';
}
if ($dept!='') {
        $conditions[] = $DB->sql_like('lcl.ohakkwa', ':dept');
        $params['dept'] ='%' .$dept. '%';
}

if ($search_val) {
    if ($field == 'all') {
        $value1 = $value;
        $value2 = $value;
        $conditions[] = $DB->sql_like('q.intro', ':value1');
        $conditions[] = $DB->sql_like('q.name', ':value2');
        $params['value1'] = '%' . $value1 . '%';
        $params['value2'] = '%' . $value2 . '%';
    } else {
        $conditions[] = $DB->sql_like($field, ':search_val');
        $params['search_val'] = '%' . $value . '%';
    }
}

if ($startd) {
    $sendtime1 = strtotime($startd);
    $send1 = intval($sendtime1);

    $conditions[] = 'lcl.timestart >= :sendtime1';
    $params['sendtime1'] = $sendtime1;
}

if ($endd) {
    $sendtime2 = strtotime($endd);
    $send2 = intval($sendtime2);

    $conditions[] = 'lcl.timeend <= :sendtime2';
    $params['sendtime2'] = $sendtime2;
}




if (!empty($conditions)) {
    $where = " WHERE " . implode(" AND ", $conditions);
}else{
    $where = "";
}

$sort = ' order by c.id desc';

$query = "select q.id, q.id as qid, q.name, q.timeopen, q.timeclose, c.id as cid, c.fullname, lcl.subject_id , lcl.bunban, lcl.hyear, lcl.day_tm_cd, lcl.ohakkwa
from {quiz} q 
join {course} c on q.course = c.id 
join {lmsdata_class} lcl on c.id = lcl.course 
left JOIN {user} ur ON ur.id = lcl.prof_userid ";
$cquery = "select count(*) from {quiz} q 
join {course} c on q.course = c.id 
join {lmsdata_class} lcl on c.id = lcl.course 
left JOIN {user} ur ON ur.id = lcl.prof_userid ";

$countquiz = $DB->count_records_sql($cquery . $where, $params);
$allquiz = $DB->get_records_sql($query . $where, $params, ($currpage - 1) * $perpage, $perpage);

?>
<?php include_once('../inc/header.php'); ?>
<div id="contents">
    <?php include_once('../inc/sidebar_support.php'); ?>
    <div id="content">
        <h3 class="page_title">시험일정 조회</h3>
        <div class="page_navbar"><a href="<?php echo $CFG->wwwroot . '/siteadmin/support/popup.php'; ?>"><?php echo get_string('site_management', 'local_lmsdata'); ?></a> > <a href="<?php echo $CFG->wwwroot . '/siteadmin/support/exams.php'; ?>">시험일정 조회</a></div>

        <form name="search_form" class="search_area">
            <?php if($coursetype == 0){
                                    $year_arr = lmsdata_get_years();
                    ?>
            <select title="year" name="year" class="w_160">
                <option value="0"  <?php echo $year == 0 ? 'selected' : ''?>><?php echo get_string('all','local_lmsdata'); ?></option>
                <?php
                    foreach($year_arr as $tg_year) {
                        $selected = "";
                        if($tg_year == $year) {
                           $selected = "selected";
                        } 
                        echo '<option value="'.$tg_year.'"  '.$selected.'>'. get_string('year','local_lmsdata',$tg_year) . '</option>';
                    }
                ?>
            </select>
            <select title="term" name="term" class="w_160">
                <option value="0" <?php echo $term == 0 ? 'selected' : ''?>>- <?php echo get_string('contents_all', 'local_lmsdata'); ?> -</option>
                <?php
                    $term_arr =lmsdata_get_terms();
                    foreach($term_arr as $term_key=> $tg_term) {
                        $selected = "";
                        $term_key = (string)$term_key;
                        if($term_key === $term) {
                           $selected = "selected";
                        } 
                        echo '<option value="'.$term_key.'"  '.$selected.'>'.$tg_term.'</option>';
                    }
                ?>
            </select> 
            
            <b>학과 : </b> 
            <select title="dept" name="dept" class="w_160">
                <option value="">- <?php echo get_string('contents_all', 'local_lmsdata'); ?> -</option>
                <?php
                    foreach($dept_lists as $dept_list) {
                        if($dept_list->ohakkwa == '' ){
                            continue;
                        }
                        
                        echo $dept_list->ohakkwa.'1<br>';
                        $selected = "";
                        if($dept_list->ohakkwa == $dept) {
                           $selected = "selected";
                        } 
                        echo '<option value="'.$dept_list->ohakkwa.'"  '.$selected.'>'.$dept_list->ohakkwa.'</option>';
                    }
                ?>
            </select>
            <b>학년 : </b> 
            <select title="lecture" name="hyear" class="w_160">
                <option value="" >- <?php echo get_string('contents_all', 'local_lmsdata'); ?> -</option>
                <option value="1" <?php echo (!empty($hyear) && ($hyear == 1)) ? 'selected' : ''?> ><?php echo '1학년'; ?></option>
                <option value="2" <?php echo (!empty($hyear) && ($hyear == 2)) ? 'selected' : ''?> ><?php echo '2학년'; ?></option>
                <option value="3" <?php echo (!empty($hyear) && ($hyear == 3)) ? 'selected' : ''?>><?php echo '3학년'; ?></option>
                <option value="4" <?php echo (!empty($hyear) && ($hyear == 4)) ? 'selected' : ''?>><?php echo '4학년'; ?></option>                
            </select> 
            <b>주야구분 : </b> 
            <select title="lecture" name="juya" class="w_160">
                <option value="" >- <?php echo get_string('contents_all', 'local_lmsdata'); ?> -</option>
                <option value="10" <?php echo (!empty($juya) && ($juya == 10)) ? 'selected' : ''?> ><?php echo '주간'; ?></option>
                <option value="20" <?php echo (!empty($juya) && ($juya == 20)) ? 'selected' : ''?> ><?php echo '야간'; ?></option>           
            </select>
            <b>반 : </b> 
            <select title="lecture" name="bunban" class="w_160">
                <option value="" >- <?php echo get_string('contents_all', 'local_lmsdata'); ?> -</option>
                <?php
                    foreach ($bunban_lists as $bunban_list){
                        if( $bunban_list->bunban == '' ){
                            continue;
                        }

                        $select = '';
                        if($bunban_list->bunban == $bunban){
                           $select = 'selected';
                       }
                       echo '<option value="'.$bunban_list->bunban.'" '.$select.'>'.$bunban_list->bunban.'</option>';
                    }
                ?>
            </select>
            <br>
            <?php }?>
            <label>시험기간 : </label>
            <input type="text" name="startd" id="id_startd" size="10" value="<?php echo $startd; ?>"/> <span>~</span> <input type="text" name="endd" id="id_endd" size="10" value="<?php echo $endd; ?>"/>
            <?php if($coursetype == 1){?>
            <input type="text" title="tag_search" name="tag_searchtext" value="<?php echo $tag_searchtext; ?>" placeholder="<?php echo get_string('tag_search_placeholder','local_lmsdata'); ?>"  class="search-text"/>
            <?php }?>
            <select title="lecture" name="search" class="w_160">
                <option value="0" <?php echo (!empty($search) && ($search == 0)) ? 'selected' : ''?> ><?php echo get_string('all','local_lmsdata'); ?></option>
                <?php if($coursetype != 2){?>
                <option value="1" <?php echo (!empty($search) && ($search == 1)) ? 'selected' : ''?> ><?php echo get_string('course_code', 'local_lmsdata'); ?></option>
                <?php }?>
                <option value="2" <?php echo (!empty($search) && ($search == 2)) ? 'selected' : ''?>><?php echo get_string('course_name', 'local_lmsdata'); ?></option>
                <option value="3" <?php echo (!empty($search) && ($search == 3)) ? 'selected' : ''?>><?php echo get_string('teacher', 'local_lmsdata'); ?></option>
            </select> 
            <input type="text" title="search" name="searchtext" value="<?php echo $searchtext; ?>" placeholder="<?php echo get_string('search_placeholder','local_lmsdata'); ?>"  class="search-text"/>
            <br/>
            <label>검색조건 : </label>
            <select class="w_160" name="field" style="margin: 5px 10px 5px 0">
                <option value="name" <?php if ($field == 'name') echo 'selected'; ?>>퀴즈명</option>
                <option value="intro" <?php if ($field == 'intro') echo 'selected'; ?>>소개문</option> 
            </select>
            <input type="text" name="search_val" value="<?php echo $value; ?>" class="w_300" placeholder="검색어를 입력하세요."/>
            <input type="submit" class="blue_btn" id="search" value="검색" />
        </form> <!-- Search Form End -->

        <table cellspacing="0" cellpadding="0">
            <thead>
                <tr>
                    <th style="width:5%;"><?php echo get_string('number', 'local_lmsdata'); ?></th>
                    <th style="width:6%;">강좌코드</th>
                    <th style="width:10%;">강좌명</th>
                    <th style="width:10%;">교수</th>
                    <th style="width:5%;">문제수</th>
                    <th>시험명</th>
                    <th style="width:5%;">총 인원</th>
                    <th style="width:5%;">참여자</th>
                    <th style="width:5%;">미참여자</th>
                    <th style="width:12%;">시험시작기간</th>
                    <th style="width:12%;">시험종료기간</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $count = $countquiz-$currpage*10+10;
                if ($allquiz) {
                    foreach ($allquiz as $quiz) {
                        $teachersql = "select u.id, u.firstname, u.lastname from {lmsdata_class} lcl  
                        join {user} u on u.id = lcl.prof_userid and lcl.course = :courseid ";
                        $teacher = $DB->get_record_sql($teachersql,array('courseid'=>$quiz->cid));
                        $qscount = $DB->count_records_sql('select count(id) from {quiz_slots} where quizid =:quizid',array('quizid'=>$quiz->id));
                        $cmid = $DB->get_field('course_modules','id', array('course'=>$quiz->cid, 'instance'=>$quiz->id));
                        
                        $qcountsql = "SELECT count(*) FROM {grade_items} gi 
                        join {grade_grades} gg on gi.id = gg.itemid 
                        where gi.itemtype='mod' and gi.iteminstance = :qinstanceid ";
                        $whereallcount = " and gg.finalgrade <> ''";
                        $quizalluser = $DB->count_records_sql($qcountsql,array('qinstanceid'=>$quiz->id));
                        $quizinuser = $DB->count_records_sql($qcountsql.$whereallcount,array('qinstanceid'=>$quiz->id));
                        
                        $user_query = "select u.* from {course} c
                        join {context} ctx on ctx.contextlevel = :contextlevel and ctx.instanceid = c.id 
                        join {role_assignments} ra on ra.contextid = ctx.id 
                        join {role} r on r.id = ra.roleid and r.shortname = 'editingteacher' 
                        join {user} u on u.id = ra.userid 
                        where c.id = :courseid  ";
                       $teachers = $DB->get_records_sql($user_query, array('courseid' => $quiz->cid, 'contextlevel'=> CONTEXT_COURSE));
                        
                        ?>
                        <tr>
                            <td><?php echo $count ?></td>
                            <td><?php echo $quiz->subject_id; ?></td>
                            <td><a href="/course/view.php?id=<?php echo $quiz->cid ?>"><?php echo $quiz->fullname; ?></a></td>
                            <td><?php  
                                foreach($teachers as $teacher) {
                                    echo fullname($teacher).'('.$teacher->username.')<br>'; 
                                }
                            ?></td>
                            <td><?php echo $qscount?></td>
                            <td><a href="/course/modedit.php?update=<?php echo $cmid ?>&return=1"><?php echo $quiz->name; ?></a></td>
                            <td><?php echo $quizalluser; ?></td>
                            <td><?php echo $quizinuser; ?></td>
                            <td><?php echo $quizalluser-$quizinuser; ?></td>
                            <td><?php
                                if ($quiz->timeopen == 0 ){
                                    echo '-';
                                }else{
                                    echo date('Y.m.d'.' '.'H:i',$quiz->timeopen);
                                }
                                ?></td>
                            <td><?php
                                if ($quiz->timeclose == 0 ){
                                    echo '-';
                                }else{
                                    echo date('Y.m.d'.' '.'H:i',$quiz->timeclose);
                                }
                                ?></td>
                        </tr>
                        <?php
                        $count--;
                    }
                } else {
                    echo '<tr><td colspan="11">검색 내역이 없습니다.</td></tr>';
                }
                ?>


            </tbody>
        </table> 
        <?php
        
        $page_params = array();
                if($field) {
                    $page_params['field'] = $field;
                }
                if($search_val) {
                    $page_params['search_val'] = $search_val;
                }
                if($search) {
                    $page_params['search'] = $search;
                }
                if($searchtext) {
                    $page_params['searchtext'] = $searchtext;
                }
                if($year) {
                    $page_params['year'] = $year;
                }
                if($term) {
                    $page_params['term'] = $term;
                }
                if($hyear) {
                    $page_params['hyear'] = $hyear;
                }
                if($juya) {
                    $page_params['juya'] = $juya;
                }
                if($dept) {
                    $page_params['dept'] = $dept;
                }
                if($bunban) {
                    $page_params['bunban'] = $bunban;
                }
                if($startd) {
                    $page_params['startd'] = $startd;
                }
                if($endd) {
                    $page_params['endd'] = $endd;
                }
        print_paging_navbar($countquiz, $currpage, $perpage, 'exams.php', $page_params);
        ?>
        <!-- Breadcrumbs End -->
    </div> <!-- Table Footer Area End -->
</div>
<script type="text/javascript">
    $(function () {
        $("#id_startd").datepicker({
            showOn: "focus",
            dateFormat: "yy-mm-dd",
            onClose: function (selectedDate) {
                $("#id_endd").datepicker("option", "minDate", selectedDate);
            }
        });
        $("#id_endd").datepicker({
            showOn: "focus",
            dateFormat: "yy-mm-dd",
            onClose: function (selectedDate) {
                $("#id_startd").datepicker("option", "maxDate", selectedDate);
            }
        });
    });
</script>
<?php include_once('../inc/footer.php'); ?>
