<?php 
require_once dirname(dirname(dirname (__FILE__))).'/config.php';
require_once dirname(dirname (__FILE__)).'/lib/paging.php';
require_once dirname(dirname (__FILE__)).'/lib.php';

// Check for valid admin user - no guest autologin
require_login(0, false);
if (isguestuser()) {
    $SESSION->wantsurl = (string)new moodle_url('/siteadmin/manage/enrol_basic_course.php');
    redirect(get_login_url());
}

$test = $DB->get_field_sql('SELECT max(groupnum) as mnum FROM {lmsdata_group}');

$courseid = optional_param('course', 0, PARAM_INT);

$group_chk = $DB->record_exists('groups', array('courseid'=>$courseid));

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
        <h3 class="page_title">수강생 조편성</h3>
        <div class="page_navbar"><a href="./enrol_basic_course.php">수강생</a> > <a href="./enrol_basic_course.php">기본의학 및 특과</a> > 수강생 조편성</div>
        <div class="students_header">
            <div class="students_header_content" ><?php echo $course_info;?></div>
            <input type="button" class="blue_btn students_header_button" style="margin-right: 10px;" value="수강생목록" onclick="javascript:location.href='enrol_basic_students.php?course=<?php echo $courseid;?>';"/>    
            <input type="button" class="blue_btn students_header_button" style="margin-right: 10px;" value="<?php echo get_string('course_list','local_lmsdata'); ?>" onclick="javascript:location.href='enrol_basic_course.php';"/>    
        </div>
<?php 
    if(!$group_chk) {
?>
        <form name="frm_basic_excel" id="frm_third_excel" class="search_area" action="<?php echo $CFG->wwwroot.'/siteadmin/manage/enrol_basic_group.execute.php'; ?>" method="post" enctype="multipart/form-data">
            <div class="group_notice">등록된 조가 없습니다. 조를 등록하세요.</div>
            <div class="input_excel">
                <input type="file" class="" name="group_excel" size="50"/>
                <input type="hidden" class="" name="course" value="<?php echo $courseid;?>"/>
                <input type="button" class="blue_btn" style="margin-right: 10px;" value="<?php echo get_string('add3','local_lmsdata'); ?>" onclick="enrol_basic_excel_submit()"/>
            </div>
            <div class="group_sample">
                >샘플 파일형식과 동일하게 등록해야 정상적으로 조편성이 생성됩니다. <a href="<?php echo $CFG->wwwroot;?>/siteadmin/manage/group_sample.xlsx" >[<?php echo get_string('download_sample','local_lmsdata'); ?>]</a>
            </div>
        </form>
<?php 
    } else {
        
        $member_sql = ' SELECT rownum, ur.id as userid, ur.firstname as fullname, lg.groupnum, lg.mgroupid, lg.id as groupid, ro.shortname  
                        FROM {groups} gr 
                        JOIN {lmsdata_group} lg ON lg.mgroupid = gr.id
                        JOIN {context} co ON co.instanceid = gr.courseid
                        LEFT JOIN {groups_members} gm ON gm.groupid = gr.id
                        LEFT JOIN {user} ur ON ur.id = gm.userid
                        LEFT JOIN {role_assignments} ra ON ra.userid = ur.id and ra.contextid = co.id
                        LEFT JOIN {role} ro ON ro.id = ra.roleid
                        WHERE gr.courseid = :courseid and co.contextlevel = :contextlevel ORDER BY lg.groupnum, ur.firstname, ur.lastname asc ';
        
        $members = $DB->get_records_sql($member_sql, array('courseid' => $courseid, 'contextlevel'=>CONTEXT_COURSE));

        foreach($members as $member) {
            $member_arr[$member->groupnum][] = $member;
        }
        
        ?>   
        <input type="button" class="blue_btn third_group_reset" style="margin-right: 10px;" value="초기화" onclick="enrol_basic_group_reset()"/>
        <table class="third_group_list">
            <tr>
                <td class="group_num">조</td>
                <td class="tutor" style="width: 15%"><?php echo get_string('tutor_teacher', 'local_lmsdata'); ?></td>
                <td class="stu_count">학생수</td>
                <td  class="group_content">조원(수강생)</td>
            </tr>   
        <?php
        if(!empty($member_arr)) {
            foreach($member_arr as $group_num => $grouping) {
                $stu_count = 0;
                $stu_name  = array();
                $eteacher01_name  = array();
                foreach($grouping as $student) {
                    if($student->shortname == 'editingteacher01' or $student->shortname == 'editingteacher') {
                        $eteacher01_name[$student->userid] = $student->fullname;
                    } else if ($student->shortname == 'student') {
                        if(!empty($student->userid)){
                            if(empty($stu_name[$student->userid])) {
                                $stu_name[$student->userid]= $student->fullname ; 
                                $stu_count++;
                            }
                        }
                    }
                        $mgroupid = $student->mgroupid;
                }
                
                foreach ($eteacher01_name as $key => $value) {
                    if(isset($stu_name[$key])){
                        unset($stu_name[$key]);
                    }
                }
                
        ?>
            <tr>
                <td class="group_num"><?php echo $group_num.'조';?></td>
                <td class="tutor"><?php echo implode(', ', $eteacher01_name); ?></td>
                <td class="stu_count"><?php echo $stu_count.'명';?></td>
                <td class="group_content">
                    <div class="group_member"><?php echo implode(', ', $stu_name); ?></div>
                    <div class="group_popup_button"><input type="button" class="blue_btn" value="변경" onclick="enrol_basic_group_popup('<?php echo $mgroupid;?>')"/></div>
                </td>
            </tr>
        <?php
            }
        }
        ?>    
        </table><!--Table End-->
<?php        
    }
?>
    <form  id="basic_group" method="post" action="enrol_basic_group.reset.php">
        <input type="hidden" class="" name="course" value="<?php echo $courseid;?>"/>
    </form>      
    </div><!--Content End-->
    
</div> <!--Contents End-->

 <?php include_once ('../inc/footer.php');?>

<script type="text/javascript">
    function enrol_basic_excel_submit() {
        if($.trim($("input[name='group_excel']").val()) != '') {
             var filename = $.trim($("input[name='group_excel']").val());
             var extension = filename.replace(/^.*\./, ''); 
             if(extension == filename) {
                 extension = "";
             } else {
                 extension = extension.toLowerCase();
             }
             
             if($.inArray( extension, [ "xls", "xlsx" ] ) == -1) {
                 alert("<?php echo get_string('onlyexcell','local_lmsdata'); ?>");
                 return false;
             } else {
                $("#frm_third_excel").submit();
             }
        }
    }
    
    function enrol_basic_group_reset() {
        if(confirm('<?php echo get_string('delete_confirm','local_lmsdata'); ?> ')){
            $('#basic_group').submit();
        }
    }
    
    function enrol_basic_group_popup(mgroupid) {
        var tag = $("<div id='basic_group_popup'></div>");
        $.ajax({
              url: 'enrol_basic_group_popup.php',
              method: 'POST',
              data : {
                mgroupid : mgroupid,
                course    : <?php echo $courseid;?>
              },
              success: function(data) {
                    tag.html(data).dialog({
                        title: '조편성 변경',
                        modal: true,
                        width: 1400,
                        resizable: false,
                        close: function () {
                            $('#frm_course_standard').remove();
                            $( this ).dialog('destroy').remove()
                        }
                    }).dialog('open');
              }
        });
    }
</script>    