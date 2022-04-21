<?php

$attendance = new offline_attendance($id);

$submit = optional_param('submit', 0, PARAM_INT); 

if($submit) {
    
    //grade_items 테이블에 넣을 값
    $itemname = optional_param('itemname', '', PARAM_RAW); 
    $maxscore = optional_param('maxscore', 0, PARAM_INT); 
    $minscore = optional_param('minscore', 0, PARAM_INT); 
    
    $itemobj = $attendance->itemobj;
    
    $changeflag = false;
    
    if($itemobj->itemname != $itemname && !empty($itemname)) {
        $itemobj->itemname = $itemname;
        $changeflag = true;
    }
    if((int)$itemobj->grademax != $maxscore) {
        $itemobj->grademax = $maxscore;
        $changeflag = true;
    }
    if((int)$itemobj->grademin != $minscore) {
        $itemobj->grademin = $minscore;
        $changeflag = true;
    }
    
    if($changeflag) {
        $itemobj->update();
    }
    
    //local_off_attendance 테이블에 넣을 값
    $latesubtract = optional_param('latesubtract', 0, PARAM_INT); 
    $earlysubtract = optional_param('earlysubtract', 0, PARAM_INT); 
    
    $courseoption = new stdClass();
    $courseoption->id = $attendance->id;
    $courseoption->latesubtract = $latesubtract;
    $courseoption->earlysubtract = $earlysubtract;
    $courseoption->timemodified = time();
    $courseoption->userid = $USER->id;
    $DB->update_record('local_off_attendance', $courseoption);
    
    $attendance = new offline_attendance($id);
}

?>

<form method="post" name="form_setup" class="table-search-option" action="">
    <input type="hidden" name="id" value="<?php echo $id; ?>">
    <input type="hidden" name="type" value="<?php echo $type; ?>">
    <input type="hidden" name="submit" value="1">
    <div class="options">
        <div class="title"><?php print_string('setup:name', 'local_offline_attendance'); ?></div>
        <input type="text" name="itemname" value="<?php echo $attendance->get_attendance_book_name(); ?>" class="search-text" placeholder="<?php echo get_string('attendance:book', 'local_offline_attendance'); ?>">
    </div>
    <div class="options">
        <div class="title"><?php print_string('setup:maxscore', 'local_offline_attendance'); ?></div>
        <?php echo local_offline_attendance_drow_selectbox(100, 0, 10, $attendance->maxscore, array('name' => 'maxscore')); ?>
    </div>
    <div class="options">
        <div class="title"><?php print_string('setup:minscore', 'local_offline_attendance'); ?></div>
        <?php echo local_offline_attendance_drow_selectbox(100, 0, 10, $attendance->minscore, array('name' => 'minscore')); ?>
    </div>
    <div class="options">
        <div class="title"><?php print_string('setup:late', 'local_offline_attendance'); ?></div>
        <?php echo local_offline_attendance_drow_selectbox(0, -10, 1, $attendance->late, array('name' => 'latesubtract')); ?>
    </div>
    <div class="options">
        <div class="title"><?php print_string('setup:early', 'local_offline_attendance'); ?></div>
        <?php echo local_offline_attendance_drow_selectbox(0, -10, 1, $attendance->early, array('name' => 'earlysubtract')); ?>
    </div>
    <div class="options right">
        <input type="submit" value="<?php echo get_string('setup:save', 'local_offline_attendance'); ?>" class="board-search"/>
    </div>
</form>