<?php
require_once(dirname(__FILE__) . '/../../config.php');

$id  = required_param('id',PARAM_INT);

$apply = $DB->get_record('approval_reason',array('id'=>$id,'userid'=>$USER->id));

switch($apply->approval_status){
     case 2:$apply_type = '미승인'; break;
     case 3:$apply_type = '승인취소';break;
}
?>
<table class="generaltable">
    <tr>
        <th><?php echo $apply_type; ?> 사유</th>
        <td><?php echo $apply->unapprove_reason; ?></td>
    </tr>
</table>

