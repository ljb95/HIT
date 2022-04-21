<?php
require_once dirname(dirname(dirname (__FILE__))).'/config.php';
require_once dirname(dirname (__FILE__)).'/lib/paging.php';
require_once dirname(dirname (__FILE__)).'/lib.php';

// Check for valid admin user - no guest autologin
require_login(0, false);
if (isguestuser()) {
    $SESSION->wantsurl = (string)new moodle_url('/siteadmin/manage/enrol_basic_students.php');
    redirect(get_login_url());
}

$courseid   = optional_param('course', 0, PARAM_INT);
$context = context_course::instance($courseid);
$mode = optional_param('mode', '', PARAM_RAW);

//user 검색 옵션
$hyear      = optional_param('hyear', null, PARAM_RAW); //학년
$search       = optional_param('search', 0, PARAM_INT);
$searchtext   = optional_param('searchtext', '', PARAM_TEXT);

if($mode == 'search'){
    
    $params = array(
                    'contextid' => $context->id,
                    'roleid' => 5,
                    'usergroup' => 'rs',
                    );
    
    $sql_select  = "SELECT ur.*, ur.firstname as fullname ";
    
    if(!empty($hyear)) {
        $sub_where[] = ' univ = :univ ';
        $params['univ'] = strpos($hyear, 'p') !== false ? 1 : 2;

        $sub_where[]= $DB->sql_like('hyear', ':hyear');
        $params['hyear'] = '%'.str_replace('p', '', $hyear).'%';
    }
    
    $sub_where['usergroup'] = ' usergroup = :usergroup';
    
    if(!empty($sub_where)) {
        $sub_where = ' WHERE '.implode(' and ', $sub_where);
    }else {
        $sub_where = '';
    }
    
    $sub_sql = ' SELECT userid FROM {lmsdata_user} '.$sub_where;
    
    $sql_from    = " FROM {user} ur
                     JOIN ( ".$sub_sql."
                         ) lu ON lu.userid = ur.id  
                     LEFT JOIN(
                        SELECT userid 
                        FROM {role_assignments} 
                        WHERE contextid = :contextid and roleid = :roleid ) ra ON ra.userid = ur.id ";


    $sql_orderby = " ORDER BY ur.firstname||ur.lastname ";
    if(!empty($searchtext)) {
        switch($search) {
            case 0: // <?php echo get_string('all','local_lmsdata'); ?>
                $sql_where[]= '('.$DB->sql_like('ur.firstname||ur.lastname', ':fullname').' or '.$DB->sql_like('ur.username', ':username').')';
                $params['fullname'] = '%'.$searchtext.'%';
                $params['username'] = '%'.$searchtext.'%';
                break;
            case 1: // 이름
                $sql_where[]= $DB->sql_like('ur.firstname||ur.lastname', ':fullname');
                $params['fullname'] = '%'.$searchtext.'%';
                break;
            case 2: // 학번
                $sql_where[] = $DB->sql_like('ur.username', ':username');
                $params['username'] = '%'.$searchtext.'%';
                break;
            default:
                break;
        }
    }
    $sql_where[] =" ra.userid is null ";
    $sql_where[] =" ur.suspended = 0 ";
    
    if(!empty($sql_where)) {
        $sql_where = ' WHERE '.implode(' and ', $sql_where);
    }else {
        $sql_where = '';
    }
    
    $enrol_users = $DB->get_records_sql($sql_select.$sql_from.$sql_where.$sql_orderby, $params);
}

$lms_course = $DB->get_record('lmsdata_class', array('course'=>$courseid));

$term = lmsdata_get_terms();
$course_info  = '> '.$lms_course->year.'년 ';
$course_info .= $term[$lms_course->term].' ';
$course_info .= get_hyear_str($lms_course->hyear).'학년 ';
$course_info .= ' ['.$lms_course->subject_id.'] ';
$course_info .= $lms_course->kor_lec_name.' - ';
$course_info .= get_lectype_str($lms_course->lectype);

$js = array(
    $CFG->wwwroot.'/siteadmin/manage/course_list.js'
);
?>
<?php include_once (dirname(dirname (__FILE__)).'/inc/header.php'); ?>
<div id="contents">
    <?php include_once  (dirname(dirname (__FILE__)).'/inc/sidebar_manage.php');?>
    <div id="content">
        <h3 class="page_title">수강생 목록</h3>
        <div class="page_navbar"><a href="./enrol_basic_course.php">수강생</a> > <a href="./enrol_basic_course.php">기본의학 및 특과</a> > 수강생 목록</div>
        <div class="students_header">
            <div class="students_header_content" ><?php echo $course_info;?></div>
            <input type="button" class="blue_btn students_header_button" style="margin-right: 10px;" value="<?php echo get_string('course_list','local_lmsdata'); ?>" onclick="javascript:location.href='enrol_basic_course.php';"/>    
        </div>
        <form id="enrol_basic_search"  action="enrol_basic_select.php" method="post">
            <div class="students_search" style="border: 1px solid #bfbfbf;margin-left:20%;text-align:center;width: 54%;">
                <select name="hyear" class="w_160">
                <option value="0" <?php echo $hyear == '' ? 'selected' : ''?>>학년선택</option>
                <?php
                    $hyear_arr = lmsdata_get_hyears();
                    foreach($hyear_arr as $hak_key => $tg_hyear) {
                        $selected = "";
                        if($hak_key == trim($hyear)) {
                           $selected = "selected";
                        } 
                        echo '<option value="'.$hak_key.'"  '.$selected.'>'.$tg_hyear.'학년</option>';
                    }
                ?>
                </select>
                <select name="search" class="w_160">
                    <option value="0" <?php echo !empty($search) && ($search == 0) ? 'selected' : ''?> ><?php echo get_string('all','local_lmsdata'); ?></option>
                    <option value="1" <?php echo !empty($search) && ($search == 1) ? 'selected' : ''?> ><?php echo get_string('name','local_lmsdata'); ?></option>
                    <option value="2" <?php echo !empty($search) && ($search == 2) ? 'selected' : ''?>><?php echo get_string('student_number','local_lmsdata'); ?></option>
                </select>
                <input type="text" name="searchtext" value="<?php echo !empty($searchtext) ? $searchtext : ''; ?>" placeholder="이름/학번을 입력하세요"  class="search-text"/>
                <input type="hidden" name="mode" class="blue_btn" value="search"/>    
                <input type="hidden" name="course" class="blue_btn" value="<?php echo $courseid;?>"/>    
                <input type="submit" class="blue_btn" value="<?php echo get_string('search','local_lmsdata'); ?>"/>    
            </div>
        </form>
        <div class="enrol_explain">
            *추가할 수강생을 왼쪽에서 선택해서 추가 버튼을 클릭하여 오른쪽으로 이동시킵니다.</br>
            *복수 선택을 하려면 마우스로 클릭한 후, 드래그하면 됩니다.
        </div>
        <div class="guard_line"></div>
            
        <form class="multiselectbox_area" id="frm_enrol_basic" action="<?php echo $CFG->wwwroot."/siteadmin/manage/enrol_basic_select.execute.php"; ?>" method="post" >
            <input type="hidden" name="course" value="<?php echo $courseid;?>"/>
            <div class="left_selete_area">
                <div><label><?php echo get_string('searching_user','local_lmsdata'); ?></label></div>
                <select name="user_list[]" class="left_sel" id="left_sel" multiple size="20">
                    <optgroup label="<?php echo !empty($enrol_users) ? ('=============검색된 수강생('.count($enrol_users).')============') : ('=========검색된 수강생이 없습니다.========');?>">
                        <?php
                            if(!empty($enrol_users)) {
                                foreach ($enrol_users as $euser) {
                                    echo '<option value="'.$euser->id.'" >'.$euser->fullname.' / '.$euser->username.'</option>';
                                }
                            }
                        ?>
                    </optgroup>
                </select>
            </div>
            <div class="center_button_area" >
                <input type="button" class="blue_btn"  value="<?php echo get_string('etc_string','local_lmsdata'); ?>" onclick="mutiselecte_change('left_sel', 'right_sel', null)"/>
                <input type="button" class="blue_btn"  value="제외" onclick="mutiselecte_change('right_sel', 'left_sel', null)"/>
            </div>    
            <div class="right_selete_area">
                <div><label><?php echo get_string('selecting_user','local_lmsdata'); ?></label></div>
                <select name="enrol_list[]" class="right_sel" id="right_sel" multiple size="20">
                    <optgroup label="=============<?php echo get_string('selecting_user2','local_lmsdata'); ?>============">
                    </optgroup>
                </select>
            </div>    
        </form>
        <div id="btn_area">
            <div>
                <input type="button" class="blue_btn"  value="<?php echo get_string('okay','local_lmsdata'); ?>" onclick="enrol_basic_submit()"/>
                <input type="button" class="blue_btn"  value="<?php echo get_string('cancle','local_lmsdata'); ?>" onclick="javascript:location.href='enrol_basic_students.php?course=<?php echo $courseid?>';"/>
            </div>
        </div>
     </div><!--Content End-->
    
</div> <!--Contents End-->

<?php include_once ('../inc/footer.php');?>

<script type="text/javascript">
    function enrol_basic_submit(){
        $('#right_sel option').each(function(i, selected){
            $('#frm_enrol_basic').append('<input type="hidden" name="new_list[]" value="'+selected.value+'" >');
        });
        $('#frm_enrol_basic').submit();
    }
</script>
