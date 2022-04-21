<?php
require_once(dirname(__FILE__) . '/../config.php');
require_once($CFG->dirroot.'/course/lib.php');
require_once($CFG->dirroot.'/theme/remui/classes/my_render.php');
?>
<div>
    <?php echo get_string('apply_reason', 'local_lmsdata'); ?>
    <textarea name="objective" title="신청사유" class="w_100" rows="5" ></textarea><br>
    <?php echo get_string('apply_reason', 'local_lmsdata'); ?>
    <textarea name="objective" title="신청사유" class="w_100" rows="5" ></textarea>
</div>
<div>
    <input type="button" class="btn_st01" value="승인"> 
    <input type="button" class="btn_st01" value="미승인">
    <input type="button" class="btn_st01" value="닫기">
</div>



