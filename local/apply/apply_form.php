<?php
require_once(dirname(__FILE__) . '/../../config.php');

$id  = required_param('id',PARAM_INT);

$apply = $DB->get_record('approval_reason',array('id'=>$id));

?>
<form method="post" id="apply_reason" name="apply_reason" action="apply_submit.php">
    <input type="hidden" name="id" value="<?php echo $id; ?>">
<table class="generaltable">
    <tr>
        <th>신청사유</th>
        <td><?php echo nl2br($apply->apply_reason); ?></td>
    </tr>
</table>
</form>

