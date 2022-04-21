<?php 
require (dirname(dirname(dirname(__FILE__))) . '/config.php');

$searchstring = optional_param('value', '', PARAM_RAW);

$context = context_system::instance();
$PAGE->set_context($context);

$usercount = 0;
if(!empty($searchstring)) {
    $conditions = array(
        'u.deleted = 0',
        'u.username != \'guest\''
    );
    $conditionname = array();

    $conditionname[] = $DB->sql_like('u.firstname', ':firstname', false);
    $conditionname[] = $DB->sql_like('u.lastname', ':lastname', false);
    $conditionname[] = $DB->sql_like($DB->sql_fullname('u.firstname', 'u.lastname'), ':fullname', false);
    $conditionname[] = $DB->sql_like($DB->sql_fullname('u.lastname', 'u.firstname'), ':fullname1', false);
    $conditionname[] = $DB->sql_like($DB->sql_concat('u.firstname', 'u.lastname'), ':fullname2', false);
    $conditionname[] = $DB->sql_like($DB->sql_concat('u.lastname', 'u.firstname'), ':fullname3', false);
    $conditionname[] = $DB->sql_like('u.username', ':username', false);

    $conditions[] = '('.implode(' OR ', $conditionname).')';

    $param['firstname'] = '%'.$searchstring.'%';
    $param['lastname'] = '%'.$searchstring.'%';
    $param['fullname'] = '%'.$searchstring.'%';
    $param['fullname1'] = '%'.$searchstring.'%';
    $param['fullname2'] = '%'.$searchstring.'%';
    $param['fullname3'] = '%'.$searchstring.'%';
    $param['username'] = '%'.$searchstring.'%';
       
    
    $sql_select = "SELECT u.*, lu.psosok ";
    
    $sql_from = " FROM {user} u JOIN {lmsdata_user} lu ON lu.userid = u.id ";
    $sql_where = " WHERE ".implode(' AND ', $conditions);
    $sql_order = " ORDER BY u.firstname";
    $usercount = $DB->count_records_sql('SELECT COUNT(*) '.$sql_from.$sql_where, $param);
    $users = $DB->get_records_sql($sql_select.$sql_from.$sql_where.$sql_order, $param);
    
}
?>

<div class="popup_content" id="search_user">
    <form id="frm_search_user" class="search_area" onsubmit="user_search(); return false;" method="POST">
        <input type="hidden" name="search" value="name" />
        <input type="text" name="value" value="<?php echo $searchstring; ?>" class="w_300" placeholder="<?php echo get_string('placeholder3','local_lmsdata'); ?>"/>   
        <input type="submit" class="blue_btn" id="search" value="<?php echo get_string('search','local_lmsdata'); ?>"/>
    </form>
   
    <form id="frm_course_certificate" name="frm_course_certificate" onsubmit="return false;">
        <table cellpadding="0" cellspacing="0">
            <tbody>
            <tr>
                <th><?php echo get_string('number', 'local_lmsdata'); ?></th>
                <th><?php echo get_string('photo','local_lmsdata'); ?></th>
                <th><?php echo get_string('name','local_lmsdata'); ?></th>
                <th><?php echo get_string('enrol:professor','local_lmsdata'); ?></th>
                <th><?php echo get_string('major','local_lmsdata'); ?></th>
                <th><?php echo get_string('add','local_lmsdata'); ?></th>
            </tr>
            <?php
            if($usercount > 0) {
                $count = 0;
                foreach($users as $user) {
                    echo '<tr>';
                    echo '<td>'.($usercount - $count).'</td>';
                    echo '<td>'.$OUTPUT->user_picture($user).'</td>';
                    echo '<td>'.fullname($user).'</td>';
                    echo '<td>'.$user->username.'</td>';
                    echo '<td>'.$user->psosok.'</td>';
                    echo '<td><input type="button" value="'.get_string('add','local_lmsdata').'" class="orange_btn" onclick="search_user_select(\''.$user->username.'\');"/></td>';
                    echo '</tr>';
                   
                    $count++;
                }
            } else {
                echo '<tr><td colspan="6">'.get_string('searchusers','local_lmsdata').'</td></tr>';
            }
            ?>
            </tbody>
        </table>
    </form>
</div>

<script type="text/javascript">
    function user_search() {
        var searchstring = $( "#frm_search_user input[name=value]" ).val();
        $.ajax({
            url: '<?php echo $CFG->wwwroot.'/siteadmin/stats/search_user.php'; ?>',
            method: 'POST',
            data: {
                'value': searchstring
            },
            success: function(data) {
                $("#search_user").parent().html(data);
            },
            error: function(jqXHR, textStatus, errorThrown ) {
            }
        });
    }
    function search_user_select(username) {
        $( "input[name=username]" ).attr("value",username);
        $("#search_user_popup").dialog( "close" );
    }
</script> 
