<?php
require_once dirname(dirname (__FILE__)).'/lib/paging.php';

$page         = optional_param('page', 1, PARAM_INT);
$perpage      = optional_param('perpage', 10, PARAM_INT);

$history_count = $DB->count_records('haksa_user_history');
?>
<h4 class="page_sub_title"><?php echo get_string('user_sync','local_lmsdata'); ?></h4>

<table>
    <thead>
        <tr>
            <th><?php echo get_string('number', 'local_lmsdata'); ?></th>
            <th><?php echo get_string('sync_time','local_lmsdata'); ?></th>
            <th><?php echo get_string('change_user_cnt','local_lmsdata'); ?></th>
        </tr>
    </thead>
    <tbody>
    <?php
    if($history_count > 0) {
        $histories = $DB->get_records('haksa_user_history', null, 'id DESC', '*', ($page-1)*$perpage, $perpage);
        
        $startnum = $history_count - (($page - 1) * $perpage);
        foreach($histories as $history) {
    ?>
        <tr>
            <td><?php echo $startnum--; ?></td>
            <td><?php echo date("Y-m-d H:i:s", $history->timestart); ?></td>
            <td><?php echo number_format($history->usercount); ?></td>
        </tr>
    <?php
        }
    } else {
    ?>
        <tr><td colspan="3"><?php echo get_string('nodata','local_lmsdata'); ?></td>
    <?php
    }
    ?>
    </tbody>
</table>
    
<div id="btn_area">
    <input type="button" class="blue_btn" style="float:left; margin-right: 10px;" value="<?php echo get_string('user_sync','local_lmsdata'); ?>" onclick="location.href='sync.user.import.php'"/> 
</div>

<?php
print_paging_navbar($history_count, $page, $perpage, 'sync.php', array('tab'=>$tab));