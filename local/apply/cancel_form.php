<?php
require_once(dirname(__FILE__) . '/../../config.php');

$id  = required_param('id',PARAM_INT);

$apply = $DB->get_record('approval_reason',array('id'=>$id));

?>
<form method="post" id="apply_reason" name="apply_reason" action="cancel_submit.php">
    <input type="hidden" name="id" value="<?php echo $id; ?>">
<table class="generaltable">
    <tr>
        <th>승인취소사유</th>
        <td><textarea name="unapprove_reason"><?php echo nl2br($apply->unapprove_reason); ?></textarea></td>
    </tr>
</table>
</form>

