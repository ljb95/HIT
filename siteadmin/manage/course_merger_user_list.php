<?php
require (dirname(dirname(dirname(__FILE__))) . '/config.php');
$merger_id    = optional_param('merger_course_id', 0, PARAM_INT);

$sql_select2 = "SELECT mu.*, lu.psosok, mr.name ";
$sql_from2 = "FROM {context} ct
            JOIN {role_assignments} ra on ra.contextid = ct.id AND (ra.roleid = :roleid OR ra.roleid = :roleid2)
            JOIN {user} mu on mu.id = ra.userid
            JOIN {lmsdata_user} lu on lu.userid = ra.userid 
            JOIN {role} mr on mr.id = ra.roleid ";
$sql_where2 = "WHERE ct.contextlevel = :contextlevel AND ct.instanceid = :instanceid";
$users = $DB->get_records_sql($sql_select2.$sql_from2.$sql_where2, array('contextlevel'=>CONTEXT_COURSE, 'instanceid'=>$merger_id, 'roleid'=>5, 'roleid2'=>9));
$count_users2 = $DB->count_records_sql("SELECT COUNT(*) ".$sql_from2.$sql_where2, array('contextlevel'=>CONTEXT_COURSE, 'instanceid'=>$merger_id, 'roleid'=>5, 'roleid2'=>9));

?>
<table>
    <caption class="hidden-caption">학생목록</caption>
    <thead>
    <tr>
        <th scope="row" width="10%"><?php echo get_string('number', 'local_lmsdata'); ?></th>
        <th scope="row" width="10%"><?php echo get_string('photo', 'local_lmsdata'); ?></th>
        <th scope="row"><?php echo get_string('major', 'local_lmsdata'); ?></th>
        <th scope="row" width="15%"><?php echo get_string('student_number', 'local_lmsdata'); ?></th>
        <th scope="row" width="10%"><?php echo get_string('user_role', 'local_lmsdata'); ?></th>
        <th scope="row" width="15%"><?php echo get_string('name', 'local_lmsdata'); ?></th>
    </tr>
    </thead>
    <?php
        if($count_users2 === 0) { ?>
        <tr>
            <td colspan="6"><?php echo get_string('empty_student','local_lmsdata'); ?></td>
        </tr>
        <?php } else {
            $startnum = $count_users2;
            foreach($users as $user) {
                //$userpic = new user_picture($user);

                echo "<tr>";
                echo "<td>".$startnum--."</td>";
                echo '<td></td>';
                echo '<td>'.$user->psosok.'</td>';
                echo '<td>'.$user->username.'</td>';
                echo "<td>".$startnum--."</td>";
                echo '<td>'.fullname($user).'</td>';
                echo "</tr>";
            }
        }
        ?>    
</table><!--Table End-->