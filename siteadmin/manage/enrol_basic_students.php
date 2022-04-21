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
$context = context_system::instance();
require_capability('moodle/site:config', $context);

$courseid   = optional_param('course', 0, PARAM_INT);
$searchtext = optional_param('searchtext', '', PARAM_RAW);
$currpage     = optional_param('page', 1, PARAM_INT);
$perpage      = optional_param('perpage', 20, PARAM_INT);

$sql_select  = "SELECT  ur.*, lu.hyear, ue.id as ueid, ue.status, en.enrol ";

$sql_from    = " FROM {context} ctx
                 JOIN {role_assignments} ra ON ctx.id = ra.contextid
                 JOIN {role} ro ON ro.id = ra.roleid
                 JOIN {user} ur ON ur.id = ra.userid
                 JOIN {user_enrolments} ue ON ue.userid = ur.id
                 JOIN {enrol} en ON en.id = ue.enrolid and en.courseid = ctx.instanceid
                 JOIN {lmsdata_user} lu ON lu.userid = ur.id ";

$sql_where[] = ' ctx.contextlevel = :contextlevel ';  
$sql_where[] = ' ctx.instanceid = :courseid ';  
$sql_where[] = ' ro.shortname = :shortname ';  
$params = array(
                'courseid' => $courseid,
                'contextlevel' => CONTEXT_COURSE,
                'shortname' => 'student'
                );

if(!empty($searchtext)) {
    $sql_where[]= '('.$DB->sql_like('ur.firstname||ur.lastname', ':fullname').' OR '.$DB->sql_like('ur.username', ':username').')';
    $params['fullname'] = '%'.$searchtext.'%';
    $params['username'] = '%'.$searchtext.'%';
}

$sql_orderby = " ORDER BY ur.firstname||ur.lastname ";

if(!empty($sql_where)) {
    $sql_where = ' WHERE '.implode(' and ', $sql_where);
}else {
    $sql_where = '';
}

$users = $DB->get_records_sql($sql_select.$sql_from.$sql_where.$sql_orderby, $params, ($currpage-1)*$perpage, $perpage);
$count_users = $DB->count_records_sql("SELECT COUNT(*) ".$sql_from.$sql_where, $params);

$ag_sql = " SELECT gm.userid, lg.groupnum 
            FROM {groups} gr
            JOIN {lmsdata_group} lg ON lg.mgroupid = gr.id
            JOIN {groups_members} gm ON gm.groupid = gr.id
            where gr.courseid = :course";
$attach_groups = $DB->get_records_sql_menu($ag_sql, array('course'=>$courseid));

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
        <form id="course_search"  action="enrol_basic_students.php" method="post">
            <div class="students_search">
                <input type="text" name="searchtext" placeholder="학번/이름을 입력하세요" size="30" value="<?php echo !empty($searchtext) ? $searchtext : ''; ?>"/>
                <input type="hidden" name="page" value="1" />
                <input type="hidden" name="course" value="<?php echo $courseid; ?>" />
                <input type="submit" value="<?php echo get_string('search','local_lmsdata'); ?>" class="gray_btn" onclick="search_category_popup()"/>
                <input type="button" class="blue_btn students_header_button" style="margin-right: 10px;" value="조편성" onclick="javascript:location.href='enrol_basic_group.php?course=<?php echo $courseid?>';"/>    
                <input type="button" class="blue_btn students_header_button" style="margin-right: 10px;" value="수강생등록" onclick="javascript:location.href='enrol_basic_select.php?course=<?php echo $courseid?>';"/>    
            </div>
        </form>
        
        <table>
            <tr>
                <th><input type="checkbox" onclick="check_course_id(this, 'userid')"/></th>
                <th><?php echo get_string('student_number','local_lmsdata'); ?></th>
                <th><?php echo get_string('class','local_lmsdata'); ?></th>
                <th><?php echo get_string('name','local_lmsdata'); ?></th>
                <th>조</th>
                <th>이메일</th>
                <th>연락처</th>
                <th>상태</th>
            </tr>
            <?php
                if($count_users === 0) { ?>
                <tr>
                    <td colspan="8">등록 된 수강생이 없습니다.</td>
                </tr>
                <?php 
                } else {
                    $startnum = $count_users - (($currpage - 1) * $perpage);
                    foreach($users as $user) {
                        if($user->status == 1 && $user->enrol == 'apply') {
                            $status =  '<input type="button" class="blue_btn" value="승인" onclick="course_apply_enrol('.$user->id.')">';
                        } else {
                            $status = '등록';
                        }
                        
                        $groupnum = "-";
                        if(isset($attach_groups[$user->id])) {
                            $groupnum = $attach_groups[$user->id].'조';
                        }
                ?>
                <tr>
                    <td><input type="checkbox" class="userid" name="userid" value="<?php echo $user->ueid; ?>"/></td>
                    <td><?php echo $user->username; ?></td>
                    <td><?php echo $user->hyear; ?></td>
                    <td><?php echo fullname($user); ?></td>
                    <td><?php echo $groupnum;?></td>
                    <td><?php echo $user->email; ?></td>
                    <td><?php echo $user->phone1; ?></td>
                    <td><?php echo $status;?></td>
                </tr>
                <?php
                    }
                }
                ?>    
        </table><!--Table End-->
        <div id="btn_area">
            <div style="float:left;">
                <input type="submit" class="blue_btn" style="margin-: 10px;" value="선택삭제" onclick="course_unenrol_users()"/>
            </div>
        </div>
        <?php
            print_paging_navbar_script($count_users, $currpage, $perpage, 'javascript:cata_page(:page);');       
        ?>
     </div><!--Content End-->
    
</div> <!--Contents End-->

<?php include_once ('../inc/footer.php');?>

<script type="text/javascript">
    function course_unenrol_users(){
        
        var user_list =[];
        var count = 0;
        $(".userid").each(function(index, element){
          if($(this).is(":checked")){
              user_list.push($(this).val()) ;
              count += 1;
          }
        });

        if(count == 0){
            alert("삭제하려는 수강생을 선택해 주세요.");
            return false;
        }
        
        if(!confirm('<?php echo get_string('delete_confirm','local_lmsdata'); ?>')) return false;
        
        $.ajax({
            url : "./enrol_basic_unenrol.ajax.php",
            type: "post",
            data : {
                users : user_list,
                course : <?php echo $courseid;?>
            },
            async: false,
            success: function(data){
               document.location.href = "<?php echo 'enrol_basic_students.php?course='.$courseid?>";
            },
            error:function(e){
                console.log(e.responseText);
            }
        });
    }
    
    function course_apply_enrol(userid) {
      $('#course_search').append('<input type="hidden" name="enrol_list[]" value="'+userid+'" />');
      $('#course_search').attr('action', '<?php echo $CFG->wwwroot.'/siteadmin/manage/enrol_basic_select.execute.php'?>');
      $('#course_search').submit();
    }
</script>

         