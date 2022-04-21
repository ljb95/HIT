<?php

$page = optional_param('page', 1, PARAM_INT);
$perpage = optional_param('perpage', 20, PARAM_INT);
$search = optional_param('search', '', PARAM_RAW);

$roleobjs = $DB->get_records('role', array('archetype' => 'student'));
$roles = array_keys($roleobjs);
list($sql_in, $sql_params) = $DB->get_in_or_equal($roles, SQL_PARAMS_NAMED, 'roleid');

$sql_select = "SELECT  ur.*,r.archetype ";
$sql_from = " FROM {user} ur 
              JOIN (
                SELECT userid , roleid 
                FROM {role_assignments} 
                WHERE contextid = :contextid AND roleid $sql_in
                GROUP BY userid 
                ) ra ON ra.userid = ur.id  "
        . "JOIN {role} r on r.id = ra.roleid  ";

$sql_conditions = array('ur.deleted = :deleted');
$sql_params['contextid'] = $context->id;
$sql_params['deleted'] = 0;

//검색어
if (!empty($search)) {
    $like_fullname = $DB->sql_like('CONCAT(ur.firstname,ur.lastname)', ':fullname');
    $like_name = $DB->sql_like('ur.username', ':username');
    $sql_conditions[] = '(' . $like_fullname . ' or ' . $like_name . ')';
    $sql_params['fullname'] = '%' . $search . '%';
    $sql_params['username'] = '%' . $search . '%';
}

$sql_where = ' WHERE '.implode(' AND ', $sql_conditions);
$sql_orderby = ' ORDER BY firstname, lastname ASC ';

$totalcount = $DB->count_records_sql('SELECT COUNT(*) '.$sql_from.$sql_where, $sql_params);
$users = $DB->get_records_sql($sql_select.$sql_from.$sql_where.$sql_orderby, $sql_params, ($page-1)*$perpage, $perpage);

$userids = array_keys($users);

/* LJB 추가 값이 없을때 오류남 */ 
if(count($userids) == 0){
    $userids = array(0);
}

list($sql, $params) = $DB->get_in_or_equal($userids, SQL_PARAMS_NAMED, 'userid');
$sql_select = ' SELECT * FROM {local_off_attendance_status} ';
$sql_where = ' WHERE userid '. $sql . ' and courseid = :courseid';
$params['courseid'] = $id;
$attendance_books = $DB->get_records_sql($sql_select.$sql_where, $params);

$dates = array();
$books = array();
foreach($attendance_books as $atttendance_book) {
    $userid = $atttendance_book->userid;
    $user_timedate = $atttendance_book->timedate;
    $dates[$user_timedate] = $user_timedate; 
    $books[$userid][$user_timedate] = $atttendance_book->status;
}
krsort($dates); 
?>

<form class="table-search-option stat_form"  name="form_setup" >
    <input type="hidden" name="id" value="<?php echo $id; ?>">
    <input type="hidden" name="type" value="<?php echo $type; ?>">
    <input type="hidden" name="page" value="1">
    <input type="hidden" name="perpage" value="<?php echo $perpage; ?>">
    <div class="stat_search_area">
        <input type="text" name="search" value="<?php echo $search; ?>" class="search" placeholder="<?php echo get_string('searchplaceholder', 'coursereport_statistics'); ?>">
        <input type="submit" value="<?php echo get_string('search', 'local_jinoboard'); ?>" class="board-search"/> 
    </div>
</form>

<div class="manage-status-submit">
    <div class="perpage">
        <select class="select perpage" name="perpage" onchange="change_perpage(this.options[this.selectedIndex].value, 'form_setup');">
            <?php
            $nums = array(10,20,50,100);
            foreach ($nums as $num) {
                $selected = '';
                if($num == $perpage) {
                    $selected = ' selected';
                }
                echo '<option value="'.$num.'"'.$selected.'>'.get_string('showperpage', 'local_courselist', $num).'</option>';
            } ?>
        </select>
    </div>
    <div id="status">
        <input type="button" value="<?php print_string('book:grades', 'local_offline_attendance'); ?>" id="attendance_batch_butoon" onclick="attendance_book_grading();"/>
        <input type="button" value="<?php print_string('book:excel', 'local_offline_attendance'); ?>" id="attendance_batch_butoon" onclick="excel_export();"/>
    </div>
</div>

 <table class="generaltable" id="table_courses">
    <thead>
        <tr>
            <th style="width:5%"><?php print_string('manage:num', 'local_offline_attendance'); ?></th>
            <th style="width:5%"><?php print_string('manage:picture', 'local_offline_attendance'); ?></th>
            <th style="width:10%"><?php print_string('manage:major', 'local_offline_attendance'); ?></th>
            <th style="width:10%"><?php print_string('manage:username', 'local_offline_attendance'); ?></th>
            <th style="width:5%"><?php print_string('manage:role', 'local_offline_attendance'); ?></th>
            <th><?php print_string('manage:name', 'local_offline_attendance'); ?></th>
            <?php 
                foreach($dates as $date) {
                    echo '<th>'.date('Y-m-d', $date).'</th>';
                }
            ?>
        </tr>
    </thead>
    <tbody>
        
        <?php 
            $status = array(
                        1 =>'○',
                        2 => 'X',
                        3 =>'△',
                        4 =>'□'
                    );
            if(!empty($users)) {
                $startnum = $totalcount - (($page - 1) * $perpage);
                foreach($users as $user) {
                    $lmsdata = $DB->get_record('lmsdata_user',array('userid'=>$user->id));
                    echo '<tr>';
                    echo '<td>'.$startnum--.'</td>';
                    echo '<td>'.$OUTPUT->user_picture($user).'</td>';
                    echo '<td>'.$lmsdata->major.'</td>';
                    echo '<td>'.$user->username.'</td>';
                    echo '<td>'.get_string('defaultcourse'.$user->archetype).'</td>';
                    echo '<td>'. fullname($user).'</td>';
                    krsort($books[$user->id]);
                    foreach($books[$user->id] as  $st){
                        echo '<td>'.$status[$st].'</td>';
                    }
                    echo '<tr>';
                }
            }
        ?>
    </tbody>
 </table>
<div class="table-footer-area">
    <div class="btn-area btn-area-right">
    </div>
    <?php
     offattendance_print_paging_navbar_script($totalcount, $page, $perpage, 'javascript:goto_page(:page, \'form_setup\');', 10);
     ?>
</div>

<script type="text/javascript">
    function excel_export(){
        $('input[name=excel]').val(1);
        $('form[name=form_setup]').attr('action', './attendance_book_excel.php');
        var test = $('form[name=form_setup]').attr('action');
        $('form[name=form_setup]').submit();
    }
    function attendance_book_grading(){
        if(confirm('<?php print_string('book:alert1', 'local_offline_attendance'); ?>')) {
            $.ajax({
              url: '<?php echo $CFG->wwwroot.'/local/offline_attendance/attendance_book_grade.php'; ?>',
              method: 'POST',
              dataType: 'json',
              data : {
                id : <?php echo $id ?>
              },
              success: function(data) {
                  if(data.status == 'success') {
                      alert(data.text);
                  }
              } 
            });
        }
    }
</script>