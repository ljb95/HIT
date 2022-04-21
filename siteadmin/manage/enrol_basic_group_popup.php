<?php 
require (dirname(dirname(dirname(__FILE__))) . '/config.php');

$search       = optional_param('search', 0, PARAM_INT);
$searchtext   = optional_param('searchtext', '', PARAM_TEXT);
$mgroupid     = required_param('mgroupid', PARAM_INT);  
$courseid     = required_param('course', PARAM_INT);  
$tab          = optional_param('tab', 0, PARAM_INT);

if(!is_siteadmin($USER)){
    redirect($CFG->wwwroot);
}

// 튜터 등록 탭
if($tab == 0) {
    // 현재 강의에서 그룹에 등록된 lmsdata_user->usergroup 값이 ra인 사용자는 mb.usid is not 
    $user_sql = ' SELECT lu.id, ur.id as userid, lu.psosok, ur.firstname as fullname, ur.username, mb.usid, mb.shortname 
                    FROM {lmsdata_user} lu
                    JOIN {user} ur ON ur.id = lu.userid
                    LEFT JOIN (
                      SELECT gm.userid as usid, ro.shortname 
                      FROM {groups} gr
                      JOIN {groups_members} gm ON gm.groupid = gr.id
                      JOIN {context} co ON co.instanceid = gr.courseid
                      JOIN {user} ur ON ur.id = gm.userid
                      JOIN {role_assignments} ra ON ra.userid = ur.id and ra.contextid = co.id
                      JOIN {role} ro ON ro.id = ra.roleid WHERE ro.shortname = :shortname AND co.instanceid = :courseid AND co.contextlevel =:contextlevel 
                    ) mb ON mb.usid = ur.id ';
    $user_param = array(
                        'usergroup' => 'pr',
                        'contextlevel' => 50,
                        'courseid' => $courseid,
                        'shortname' => 'editingteacher01',
                        'suspended'  => 0
                    );

    $user_where[] = ' ur.suspended = :suspended ';  
    $user_where[] = ' mb.usid is null ';  
    $user_where[] = ' lu.usergroup = :usergroup ';  
    
    if(!empty($searchtext)) {
        switch($search) {
            case 0: // <?php echo get_string('all','local_lmsdata'); ?>
                $user_where[]= '('.$DB->sql_like('ur.firstname||ur.lastname', ':fullname').' or '.$DB->sql_like('ur.username', ':username').')';
                $user_param['fullname'] = '%'.$searchtext.'%';
                $user_param['username'] = '%'.$searchtext.'%';
                break;
            case 1: // 이름
                $user_where[]= $DB->sql_like('ur.firstname||ur.lastname', ':fullname');
                $user_param['fullname'] = '%'.$searchtext.'%';
                break;
            case 2: // 학번
                $user_where[] = $DB->sql_like('ur.username', ':username');
                $user_param['username'] = '%'.$searchtext.'%';
                break;
            default:
                break;
        }
    }

    $user_where = ' WHERE '.implode(' and ', $user_where);
    $user_orderby = ' ORDER BY ur.firstname||ur.lastname ASC ';
    $users = $DB->get_records_sql($user_sql.$user_where.$user_orderby, $user_param);
    
    foreach($users as $user) {
        if(empty($user->usid)) {
            $user_tutor[$user->username] = $user;
        }
    }

    // 현재강의 현재 조 등록된 사람
    $group_sql = ' SELECT ra.id, ur.id as userid, ur.firstname as fullname, ur.username, ro.shortname, lu.psosok
                   FROM {groups} gr
                   JOIN {groups_members} gm ON gm.groupid = gr.id
                   JOIN {context} co ON co.instanceid = gr.courseid
                   JOIN {user} ur ON ur.id = gm.userid
                   JOIN {lmsdata_user} lu ON lu.userid = gm.userid
                   JOIN {role_assignments} ra ON ra.userid = ur.id and ra.contextid = co.id
                   JOIN {role} ro ON ro.id = ra.roleid ';

    $group_where[] = ' gr.id = :mgroupid ';  
    $group_where[] = ' ( ro.shortname = :shortname1 or ro.shortname = :shortname2 ) ';  
    $group_param = array(
                        'mgroupid'   => $mgroupid,
                        'contextlevel'=>CONTEXT_COURSE,
                        'shortname1' => 'editingteacher01',
                        'shortname2' => 'editingteacher'
                        );
    $group_where = ' WHERE '.implode(' and ', $group_where);
    $group_orderby = ' ORDER BY ur.firstname||ur.lastname ASC ';
    $group_members = $DB->get_records_sql($group_sql.$group_where.$group_orderby, $group_param);

    foreach($group_members as $member) {
        $member_tutor[] = $member;
    }
?>
    <div id="popup_content">
        <div class="content_navigation">
            <a href="#" onclick="popup_tab_change(0);"><p class="black_btn <?php echo empty($tab) ? 'black_btn_selected' : "" ;?>"><?php echo get_string('tutor_teacher_change', 'local_lmsdata'); ?></p></a>
            <a href="#" onclick="popup_tab_change(1);"><p class="black_btn <?php echo !empty($tab) ? 'black_btn_selected' : "" ;?>">수강생 변경</p></a>
        </div><!--Content Navigation End-->
        <form id="popup_enrol_basic_search"  onsubmit="basic_member_search(); return false;" method="post">
            <div class="popup_students_search" >
                <select name="search" class="w_160">
                    <option value="0" <?php echo !empty($search) && ($search == 0) ? 'selected' : ''?> ><?php echo get_string('all','local_lmsdata'); ?></option>
                    <option value="1" <?php echo !empty($search) && ($search == 1) ? 'selected' : ''?> ><?php echo get_string('name','local_lmsdata'); ?></option>
                    <option value="2" <?php echo !empty($search) && ($search == 2) ? 'selected' : ''?>><?php echo get_string('student_number','local_lmsdata'); ?></option>
                </select>
                <input type="text" name="searchtext" value="<?php echo !empty($searchtext) ? $searchtext : ''; ?>" placeholder="이름/학번을 입력하세요"  class="popup_search_text"/>
                <input type="hidden" name="mode" class="blue_btn" value="search"/>    
                <input type="hidden" name="tab" class="blue_btn" value="<?php echo $tab;?>"/>    
                <input type="hidden" name="mgroupid" class="blue_btn" value="<?php echo $mgroupid;?>"/>    
                <input type="hidden" name="course" class="blue_btn" value="<?php echo $courseid;?>"/>    
                <input type="submit" class="blue_btn" value="<?php echo get_string('search','local_lmsdata'); ?>"/>    
            </div>
        </form>
        <div class="popup_enrol_explain">
            *추가할 수강생을 왼쪽에서 선택해서 추가 버튼을 클릭하여 오른쪽으로 이동시킵니다.</br>
            *복수 선택을 하려면 마우스로 클릭한 후, 드래그하면 됩니다.
        </div>
        <div class="popup_guard_line"></div>

        <form class="popup_multiselectbox_area" id="frm_enrol_basic_popup" action="<?php echo $CFG->wwwroot."/siteadmin/manage/enrol_basic_group_popup.edit.php"; ?>" method="post" >
            <input type="hidden" name="mgroupid" class="blue_btn" value="<?php echo $mgroupid;?>"/>
            <input type="hidden" name="course" class="blue_btn" value="<?php echo $courseid;?>"/>    
            <div class="popup_left_selete_area">
                <div><label><?php echo get_string('searching_user','local_lmsdata'); ?></label></div>
                <select name="user_list[]" class="popup_left_sel" id="left_sel" multiple size="20">
                    <optgroup class="student" label="<?php echo get_string('searching_user2','local_lmsdata'); ?>">
                        <?php
                            if(!empty($user_tutor)) {
                                foreach ($user_tutor as $ut) {
                                    echo '<option value="'.$ut->userid.'" >'.$ut->fullname.'/'.$ut->username.'/'.$ut->psosok.'</option>';
                                }
                            }
                        ?>
                    </optgroup>
                </select>
            </div>
            <div class="popup_center_button_area" >
                <input type="button" class="blue_btn"  value="<?php echo get_string('etc_string','local_lmsdata'); ?>" onclick="mutiselecte_change('left_sel', 'right_sel', null)"/>
                <input type="button" class="blue_btn"  value="제외" onclick="mutiselecte_change('right_sel', 'left_sel', null)"/>
            </div>    
            <div class="popup_right_selete_area">
                <div><label><?php echo get_string('selecting_user','local_lmsdata'); ?></label></div>
                <select name="enrol_list[]" class="popup_right_sel" id="right_sel" multiple size="20">
                    <optgroup class="student" label="<?php echo get_string('selecting_user2','local_lmsdata'); ?>">
                    <?php
                        if(!empty($member_tutor)) {
                            foreach ($member_tutor as $mt) {
                                echo '<option value="'.$mt->userid.'" >'.$mt->fullname.'/'.$mt->username.'/'.$mt->psosok.'</option>';
                            }
                        }
                    ?>
                    </optgroup>
                </select>
            </div>    
        </form>
        <div id="btn_area">
            <div>
                <input type="button" class="blue_btn"  value="<?php echo get_string('okay','local_lmsdata'); ?>" onclick="basic_group_popup_execute('tutor')"/>
                <input type="button" class="blue_btn"  value="<?php echo get_string('cancle','local_lmsdata'); ?>" onclick="basic_group_popup_close()"/>
            </div>
        </div>
     </div><!--Content End-->
<?php
// 학생 등록 탭  
} else {
    // 현재 강의 사용자 중, mgroupid에 등록된 사용자는 mb.usid is not 
    $user_sql = ' SELECT ra.id, ur.id as userid, ur.firstname as fullname, ur.username, mb.usid, ro.shortname, lu.hyear
                    FROM {role_assignments} ra
                    JOIN {context} co ON co.id = ra.contextid
                    JOIN {role} ro ON ro.id = ra.roleid
                    JOIN {user} ur ON ur.id = ra.userid
                    JOIN {lmsdata_user} lu ON lu.userid = ur.id
                    LEFT JOIN (
                      SELECT gm.userid as usid 
                      FROM {groups} gr
                      JOIN {groups_members} gm ON gm.groupid = gr.id WHERE gr.courseid = :courseid1
                    ) mb ON mb.usid = ur.id ';
    $user_param = array(
                        'mgroupid' => $mgroupid,
                        'contextlevel' => 50,
                        'courseid1' => $courseid,
                        'courseid2' => $courseid,
                        'suspended'  => 0,
                        'shortname' => 'student',
                    );

    $user_where[] = ' co.contextlevel = :contextlevel ';
    $user_where[] = ' co.instanceid = :courseid2 ';  
    $user_where[] = ' ro.shortname = :shortname ';  
    $user_where[] = ' suspended = :suspended ';  
    $user_where[] = ' mb.usid is null ';  

    if(!empty($searchtext)) {
        switch($search) {
            case 0: // <?php echo get_string('all','local_lmsdata'); ?>
                $user_where[]= '('.$DB->sql_like('ur.firstname||ur.lastname', ':fullname').' or '.$DB->sql_like('ur.username', ':username').')';
                $user_param['fullname'] = '%'.$searchtext.'%';
                $user_param['username'] = '%'.$searchtext.'%';
                break;
            case 1: // 이름
                $user_where[]= $DB->sql_like('ur.firstname||ur.lastname', ':fullname');
                $user_param['fullname'] = '%'.$searchtext.'%';
                break;
            case 2: // 학번
                $user_where[] = $DB->sql_like('ur.username', ':username');
                $user_param['username'] = '%'.$searchtext.'%';
                break;
            default:
                break;
        }
    }
    $user_where = ' WHERE '.implode(' and ', $user_where);
    $users = $DB->get_records_sql($user_sql.$user_where, $user_param);

    foreach($users as $user) {
        $user_student[$user->username] = $user;
    }

    // 현재강의 현재 조 등록된 사람
    $group_sql = ' SELECT ra.id, ur.id as userid, ur.firstname as fullname, ur.username, ro.shortname, lu.hyear
                   FROM {groups} gr
                   JOIN {groups_members} gm ON gm.groupid = gr.id
                   JOIN {context} co ON co.instanceid = gr.courseid
                   JOIN {user} ur ON ur.id = gm.userid
                   JOIN {lmsdata_user} lu ON lu.userid = ur.id
                   JOIN {role_assignments} ra ON ra.userid = ur.id and ra.contextid = co.id
                   JOIN {role} ro ON ro.id = ra.roleid ';

    $group_where[] = ' gr.id = :mgroupid ';  
    $group_where[] = ' ro.shortname = :shortname  ';  
    $group_param = array(
                        'mgroupid'   => $mgroupid,
                        'contextlevel'=>CONTEXT_COURSE,
                        'shortname' => 'student'
                        );
    $group_where = ' WHERE '.implode(' and ', $group_where);
    $group_members = $DB->get_records_sql($group_sql.$group_where, $group_param);

    foreach($group_members as $member) {
        $member_student[] = $member;
    }
?>
    <div id="popup_content">
        <div class="content_navigation">
            <a href="#" onclick="popup_tab_change(0);"><p class="black_btn <?php echo empty($tab) ? 'black_btn_selected' : "" ;?>"><?php echo get_string('tutor_teacher_change', 'local_lmsdata'); ?></p></a>
            <a href="#" onclick="popup_tab_change(1);"><p class="black_btn <?php echo !empty($tab) ? 'black_btn_selected' : "" ;?>">수강생 변경</p></a>
        </div><!--Content Navigation End-->
        <form id="popup_enrol_basic_search"  onsubmit="basic_member_search(); return false;" method="post">
            <div class="popup_students_search" >
                <select name="search" class="w_160">
                    <option value="0" <?php echo !empty($search) && ($search == 0) ? 'selected' : ''?> ><?php echo get_string('all','local_lmsdata'); ?></option>
                    <option value="1" <?php echo !empty($search) && ($search == 1) ? 'selected' : ''?> ><?php echo get_string('name','local_lmsdata'); ?></option>
                    <option value="2" <?php echo !empty($search) && ($search == 2) ? 'selected' : ''?>><?php echo get_string('student_number','local_lmsdata'); ?></option>
                </select>
                <input type="text" name="searchtext" value="<?php echo !empty($searchtext) ? $searchtext : ''; ?>" placeholder="이름/학번을 입력하세요"  class="popup_search_text"/>
                <input type="hidden" name="mode" class="blue_btn" value="search"/>    
                <input type="hidden" name="mgroupid" class="blue_btn" value="<?php echo $mgroupid;?>"/>    
                <input type="hidden" name="course" class="blue_btn" value="<?php echo $courseid;?>"/>
                <input type="hidden" name="tab" class="blue_btn" value="<?php echo $tab;?>"/>    
                <input type="submit" class="blue_btn" value="<?php echo get_string('search','local_lmsdata'); ?>"/>    
            </div>
        </form>
        <div class="popup_enrol_explain">
            *추가할 수강생을 왼쪽에서 선택해서 추가 버튼을 클릭하여 오른쪽으로 이동시킵니다.</br>
            *복수 선택을 하려면 마우스로 클릭한 후, 드래그하면 됩니다.
        </div>
        <div class="popup_guard_line"></div>

        <form class="popup_multiselectbox_area" id="frm_enrol_basic_popup" action="<?php echo $CFG->wwwroot."/siteadmin/manage/enrol_basic_group_popup.edit.php"; ?>" method="post" >
            <input type="hidden" name="mgroupid" class="blue_btn" value="<?php echo $mgroupid;?>"/>
            <input type="hidden" name="course" class="blue_btn" value="<?php echo $courseid;?>"/>    
            <div class="popup_left_selete_area">
                <div><label><?php echo get_string('searching_user','local_lmsdata'); ?></label></div>
                <select name="user_list[]" class="popup_left_sel" id="left_sel" multiple size="20">
                    <optgroup class="student" label="<?php echo get_string('searching_user2','local_lmsdata'); ?>">
                        <?php
                            if(!empty($user_student)) {
                                foreach ($user_student as $us) {
                                    echo '<option value="'.$us->userid.'" >'.$us->fullname.'('.$us->hyear.')/'.$us->username.'</option>';
                                }
                            }
                        ?>
                    </optgroup>
                </select>
            </div>
            <div class="popup_center_button_area" >
                <input type="button" class="blue_btn"  value="<?php echo get_string('etc_string','local_lmsdata'); ?>" onclick="mutiselecte_change('left_sel', 'right_sel', null)"/>
                <input type="button" class="blue_btn"  value="제외" onclick="mutiselecte_change('right_sel', 'left_sel', null)"/>
            </div>    
            <div class="popup_right_selete_area">
                <div><label><?php echo get_string('selecting_user','local_lmsdata'); ?></label></div>
                <select name="enrol_list[]" class="popup_right_sel" id="right_sel" multiple size="20">
                    <optgroup class="student" label="<?php echo get_string('selecting_user2','local_lmsdata'); ?>">
                    <?php
                        if(!empty($member_student)) {
                            foreach ($member_student as $ms) {
                                echo '<option value="'.$ms->userid.'" >'.$ms->fullname.'('.$ms->hyear.')/'.$ms->username.'</option>';
                            }
                        }
                    ?>
                    </optgroup>
                </select>
            </div>    
        </form>
        <div id="btn_area">
            <div>
                <input type="button" class="blue_btn"  value="<?php echo get_string('okay','local_lmsdata'); ?>" onclick="basic_group_popup_execute('student')"/>
                <input type="button" class="blue_btn"  value="<?php echo get_string('cancle','local_lmsdata'); ?>" onclick="basic_group_popup_close()"/>
            </div>
        </div>
     </div><!--Content End-->
<?php
}
?>   

 
 
 <script type="text/javascript">
    function basic_member_search() {
        var search = $("#popup_enrol_basic_search select[name=search]").val();
        var searchtext = $( "#popup_enrol_basic_search input[name=searchtext]" ).val();
        var mgroupid = $( "#popup_enrol_basic_search input[name=mgroupid]" ).val();
        var courseid = $( "#popup_enrol_basic_search input[name=course]" ).val();
        var tab = $( "#popup_enrol_basic_search input[name=tab]" ).val();
        $.ajax({
            url: '<?php echo $CFG->wwwroot.'/siteadmin/manage/enrol_basic_group_popup.php'; ?>',
            method: 'POST',
            data: { 
                    search        : search,
                    searchtext    : searchtext,
                    mgroupid      : mgroupid,  
                    course        : courseid,  
                    tab        : tab  
            },
            success: function(data) {
                $("#popup_content").parent().html(data);
            },
            error: function(jqXHR, textStatus, errorThrown ) {
                console.log(jqXHR.responseText);
            }
        });
    }
    
    function basic_group_popup_close(){
        $('#basic_group_popup').dialog('destroy').remove();
    }
    
    function basic_group_popup_execute(shortname){
        $('#frm_enrol_basic_popup').append('<input type="hidden" name="shortname" value="'+shortname+'" >');
        $('#right_sel option').each(function(i, selected){
            $('#frm_enrol_basic_popup').append('<input type="hidden" name="new_list[]" value="'+selected.value+'" >');
        });
        
        $('#frm_enrol_basic_popup').submit();
    }
    
    function popup_tab_change(tab) {
        var mgroupid = $( "#popup_enrol_basic_search input[name=mgroupid]" ).val();
        var courseid = $( "#popup_enrol_basic_search input[name=course]" ).val();
        $.ajax({
            url: '<?php echo $CFG->wwwroot.'/siteadmin/manage/enrol_basic_group_popup.php'; ?>',
            method: 'POST',
            data: { 
                tab : tab,
                mgroupid      : mgroupid,  
                course        : courseid  
            },
            success: function(data) {
                $("#popup_content").parent().html(data);
            },
            error: function(jqXHR, textStatus, errorThrown ) {
                console.log(jqXHR.responseText);
            }
        });
    }
    
</script>
