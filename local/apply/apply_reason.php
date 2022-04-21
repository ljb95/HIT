<?php
require_once(dirname(__FILE__) . '/../../config.php');

$id  = required_param('id',PARAM_INT);

$apply = $DB->get_record('approval_reason',array('courseid'=>$id,'userid'=>$USER->id));

?>
<form method="post" id="apply_reason" name="apply_reason" action="apply_reason_post.php">
    <input type="hidden" name="id" value="<?php echo $id; ?>">
    <input type="hidden" name="applyid" value="<?php echo $apply->id; ?>">
<table class="generaltable">
    <tr>
        <th>신청사유</th>
        <td><textarea name="apply_reason" title="신청사유" class="w_100" rows="5" ><?php echo $apply->apply_reason; ?></textarea></td>
    </tr>
</table>
</form>

