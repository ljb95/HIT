<?php
require (dirname(dirname(dirname(__FILE__))) . '/config.php');
$PAGE->set_context($context);

$searchtext = optional_param('value', '', PARAM_RAW);
$juya = optional_param('juya', '', PARAM_RAW);
$dept = optional_param('dept', '', PARAM_RAW);
$hyear = optional_param('hyear', '', PARAM_RAW);
//강좌 아이디
$courseid    = optional_param('courseid', 0, PARAM_INT);

$sql_select = "SELECT mu.*, lu.dept, mr.id as shortname, lu.hyear, lu.day_tm_cd ";
$sql_from = "FROM {context} ct
            JOIN {role_assignments} ra on ra.contextid = ct.id AND (ra.roleid = :roleid OR ra.roleid = :roleid2)
            JOIN {user} mu on mu.id = ra.userid
            JOIN {lmsdata_user} lu on lu.userid = ra.userid 
            JOIN {role} mr on mr.id = ra.roleid ";
$sql_where = "WHERE ct.contextlevel = :contextlevel AND ct.instanceid = :instanceid ";
$group_by_sql = " group by mu.id ";
$dept_sql = "select distinct ohakkwa from {lmsdata_class} order by ohakkwa asc ";
$dept_lists = $DB->get_records_sql($dept_sql,array());

if (!empty($searchtext)) {
    $where = ' and  ((mu.firstname like :searchtxt1 or mu.lastname like :searchtxt2) or concat(mu.firstname,mu.lastname) like :searchtxt3) ';
}
$where = $where.' and lu.dept like "%'.$dept.'%" and lu.day_tm_cd like "%'.$juya.'%" and lu.hyear like "%'.$hyear.'%" ';

$params = array('searchtxt1' => '%' . $searchtext . '%', 'searchtxt2' => '%' . $searchtext . '%', 'searchtxt3' => '%' . $searchtext . '%','contextlevel'=>CONTEXT_COURSE, 'instanceid'=>$courseid, 'roleid'=>5, 'roleid2'=>9);
$users = $DB->get_records_sql($sql_select.$sql_from.$sql_where.$where.$group_by_sql, $params);
$count_users = $DB->count_records_sql("SELECT COUNT(distinct mu.id) ".$sql_from.$sql_where.$where, $params);
?>
<div class="popup_content" id="course_stu">
    <form id="frm_course_stu" class="search_area" onsubmit="course_prof_search(); return false;" method="POST">
        <!--<input type="hidden" name="search" value="name" />-->
        <input type="hidden" name="courseid" value="<?php echo $courseid; ?>" />
        <b>주야구분 : </b> 
        <select title="주야" name="juya" class="w_160">
            <option value="">- <?php echo get_string('contents_all', 'local_lmsdata'); ?> -</option>
            <option value = '10' <?php if($juya == 10) echo 'selected'; ?>>주간</option>
            <option value = '20' <?php if($juya == 20) echo 'selected'; ?>>야간</option>
        </select> 
        <b>학과 : </b> 
        <select title="학과" name="dept" class="w_160">
            <option value="">- <?php echo get_string('contents_all', 'local_lmsdata'); ?> -</option>
            <?php
                foreach ($dept_lists as $dept_list){
                    $select = '';
                    if($dept == $dept_list->ohakkwa){
                        $select = 'selected'; 
                    }
                    if($dept_list->ohakkwa == '' || $dept_list->ohakkwa == null){
                        continue;
                    }else{
                        echo '<option value="'.$dept_list->ohakkwa.'" '.$select.'>'.$dept_list->ohakkwa.' </option>';
                    }
                }
            ?>
        </select> 
        <b>학년 : </b> 
        <select title="학년" name="hyear" class="w_160">
            <option value="">- <?php echo get_string('contents_all', 'local_lmsdata'); ?> -</option>
            <option <?php if ($hyear == '1') echo 'selected'; ?> value="1">1<?php echo get_string('class','local_lmsdata'); ?></option>
            <option <?php if ($hyear == '2') echo 'selected'; ?> value="2">2<?php echo get_string('class','local_lmsdata'); ?></option>
            <option <?php if ($hyear == '3') echo 'selected'; ?> value="3">3<?php echo get_string('class','local_lmsdata'); ?></option>
            <option <?php if ($hyear == '4') echo 'selected'; ?> value="4">4<?php echo get_string('class','local_lmsdata'); ?></option>
        </select> 
        <input type="text" name="value" value="<?php echo $searchtext; ?>" class="w_300" placeholder="<?php echo get_string('placeholder8','local_lmsdata'); ?>"/>   
        <input type="submit" class="blue_btn" id="search" value="<?php echo get_string('search','local_lmsdata'); ?>"/>
    </form>
    <table>
        <caption class="hidden-caption">학생목록</caption>
        <thead>
        <tr>
            <th scope="row" width="10%"><?php echo get_string('number', 'local_lmsdata'); ?></th>
            <th scope="row" width="10%"><?php echo get_string('photo', 'local_lmsdata'); ?></th>
            <th scope="row"><?php echo get_string('major', 'local_lmsdata'); ?></th>
            <th scope="row" width="5%"><?php echo get_string('class', 'local_lmsdata'); ?></th>
            <th scope="row" width="15%"><?php echo get_string('student_number', 'local_lmsdata'); ?></th>
            <th scope="row" width="10%"><?php echo get_string('user_role', 'local_lmsdata'); ?></th>
            <th scope="row" width="15%"><?php echo get_string('name', 'local_lmsdata'); ?></th>
            <th scope="row" width="10%"><?php echo get_string('dayandnight', 'local_lmsdata'); ?></th>
        </tr>
        </thead>
        <?php
            if(empty($users)) { ?>
            <tr>
                <td colspan="9"><?php echo get_string('empty_student','local_lmsdata'); ?></td>
            </tr>
            <?php } else {
                $startnum = $count_users;
                foreach($users as $user) {
                    echo "<tr>";
                    echo "<td>".$startnum--."</td>";
                    echo '<td>'. $OUTPUT->user_picture($user).'</td>';
                    if($user->dept == ''){
                        $user->dept = '-';
                    }
                    echo '<td>'.$user->dept.'</td>';
                    if($user->hyear == '0' || $user->hyear == null || $user->hyear == ''){ $user->hyear = '-'; }
                    echo '<td>'.$user->hyear.'</td>';
                    echo '<td>'.$user->username.'</td>';
                    if($user->shortname == 1){ echo "<td>".get_string('manager', 'local_lmsdata')."</td>"; }
                    else if($user->shortname == 2){ echo "<td>".get_string('name', 'local_lmsdata')."</td>"; }
                    else if($user->shortname == 3){ echo "<td>".get_string('editingteacher', 'local_lmsdata')."</td>"; }
                    else if($user->shortname == 4){ echo "<td>".get_string('teacher', 'local_lmsdata')."</td>"; }
                    else if($user->shortname == 5){ echo "<td>".get_string('student', 'local_lmsdata')."</td>"; }
                    else if($user->shortname == 6){ echo "<td>".get_string('name', 'local_lmsdata')."</td>"; }
                    else if($user->shortname == 7){ echo "<td>".get_string('quest', 'local_lmsdata')."</td>"; }
                    else if($user->shortname == 9){ echo "<td>".get_string('editingteacher', 'local_lmsdata')."</td>"; }
                    echo '<td>'.fullname($user).'</td>';
                    if($user->day_tm_cd == '0' || $user->day_tm_cd == null || $user->day_tm_cd == ''){ $user->day_tm_cd = '-'; 
                    }else if($user->day_tm_cd == '10'){$user->day_tm_cd = '주간';
                    }else if($user->day_tm_cd == '20'){$user->day_tm_cd = '야간';}
                    echo '<td>'.$user->day_tm_cd.'</td>';
                    echo "</tr>";
                }
            }
            ?>    
    </table><!--Table End-->
</div>
<script type="text/javascript">
    function course_prof_search() {
        var searchstring = $( "#frm_course_stu input[name=value]" ).val();
        var courseid = $( "#frm_course_stu input[name=courseid]" ).val();
        var juya = $( "#frm_course_stu select[name=juya]" ).val();
        var hyear = $( "#frm_course_stu select[name=hyear]" ).val();
        var bunban = $( "#frm_course_stu select[name=bunban]" ).val();
        var dept = $( "#frm_course_stu select[name=dept]" ).val();

        $.ajax({
            url: '<?php echo $CFG->wwwroot.'/siteadmin/manage/role_list.php'; ?>',
            method: 'POST', 
            data: { 
                'value': searchstring,
                'courseid':courseid,
                'juya' : juya,
                'hyear' : hyear,
                'dept' : dept
            },
            success: function(data) {
                $("#course_stu").parent().html(data);
            },
            error: function(jqXHR, textStatus, errorThrown ) {
            }
        });
    }
</script>