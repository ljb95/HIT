<?php
$attendance = new online_attendance($id);
$submittype = optional_param('submittype', '', PARAM_RAW); 

// 성적 설정 submit
if($submittype == 'grade') {
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
    $absentsubtract = optional_param('absentsubtract', 0, PARAM_INT); 
    
    $courseoption = new stdClass();
    $courseoption->id = $attendance->id;
    $courseoption->latesubtract = $latesubtract;
    $courseoption->absentsubtract = $absentsubtract;
    $courseoption->timemodified = time();
    $courseoption->userid = $USER->id;
    $DB->update_record('local_onattend', $courseoption);
    
    $attendance = new online_attendance($id);

// 출석부 인정 활동 submit    
} else if($submittype == 'realize') {
    $realizemods = optional_param_array('realizemod', array(), PARAM_RAW);
    $batchmods = $DB->get_records('local_onattend_cm_batchset', array('courseid'=>$id));
    
    $visiblemod = array();
    // 일괄설정 사용여부 변경
    foreach($batchmods as $mod) {
        if(in_array($mod->modname, $realizemods)) {
            $DB->set_field('local_onattend_cm_batchset', 'visible', 1, array('id'=>$mod->id));
            $visiblemod[$mod->modname] = 1;
        } else {
            $DB->set_field('local_onattend_cm_batchset', 'visible', 0, array('id'=>$mod->id));
            $visiblemod[$mod->modname] = 0;
        }
    }
    
    //해당 activity 출석 사용안함으로 변경
    $acvitiys = $DB->get_records('local_onattend_cm_set', array('courseid'=>$id));
    foreach($acvitiys as $activity) {
        if($activity->approval != $visiblemod[$activity->modname]) {
            $DB->set_field('local_onattend_cm_set', 'approval', $visiblemod[$activity->modname], array('id'=>$activity->id));
        }
    }
    
// 활동별 일괄 설정 submit 
} else if($submittype == 'batch') {
    $startdates = optional_param_array('startdate', array(), PARAM_INT);   //start date
    $starttimes = optional_param_array('starttime', array(), PARAM_RAW);   //start time
    $attenddates = optional_param_array('attenddate', array(), PARAM_INT);   //attendance date
    $attendtimes = optional_param_array('attendtime', array(), PARAM_RAW);   //attendance time
    $aprogress = optional_param_array('aprogress', array(), PARAM_INT);    //attendance progress
    
    $batchmods = $DB->get_records('local_onattend_cm_batchset', array('courseid'=>$id, 'visible'=>1));
    $batch_arr = array();
    foreach($batchmods as $mod) {
        $startdate = $startdates[$mod->id]*60*60*24;
        $starttime = local_onattendance_get_hisecond($starttimes[$mod->id]);
        $attenddate = $attenddates[$mod->id]*60*60*24;
        $attendtime = local_onattendance_get_hisecond($attendtimes[$mod->id]);
        
        $mod->startratio = $startdate+$starttime;
        $mod->attendratio = $attenddate+$attendtime;
        $mod->aprogress = $aprogress[$mod->id];
        
        $DB->update_record('local_onattend_cm_batchset', $mod);
        $batch_arr[$mod->modname] = $mod;
    }
    
    //일괄설정값에 따른 주차별 설정 변경
    $mods = $DB->get_records('local_onattend_cm_set', array('courseid'=>$id));
    foreach($mods as $mod) {
        $mod = local_onattendance_batch_change($batch_arr[$mod->modname], $mod, $course->startdate);
        if($mod->change) {
            $DB->update_record('local_onattend_cm_set', $mod);
        }
    }

// 주차별 설정
} else if($submittype == 'section') {
    $startdates = optional_param_array('startdate', array(), PARAM_RAW);   //start date Y-m-d
    $starttimes = optional_param_array('starttime', array(), PARAM_RAW);   //start time H:i
    $attenddates = optional_param_array('attenddate', array(), PARAM_RAW);   //attendance date Y-m-d
    $attendtimes = optional_param_array('attendtime', array(), PARAM_RAW);   //attendance time H:i
    $aprogress = optional_param_array('aprogress', array(), PARAM_INT);    //attendance progress
    $approval = optional_param_array('approval', array(), PARAM_RAW);    // 출석인정여부
    
    // visible 되어있는 학습활동 유형만 업데이트 함
    $cmset = local_onattendance_get_cmset($id);
    foreach($cmset as $cm) {
        $mod = new Stdclass();
        $mod->starttime = strtotime($startdates[$cm->cmid].' '.$starttimes[$cm->cmid]);
        $mod->attendtime = strtotime($attenddates[$cm->cmid].' '.$attendtimes[$cm->cmid]);
        $mod->aprogress = $aprogress[$cm->cmid];
        $mod->approval = 0;
        if(isset($approval[$cm->cmid])) {
            $mod->approval = 1;
        }
        $cm = local_onattendance_mod_change($mod, $cm);
        if($cm->change) {
            $DB->update_record('local_onattend_cm_set', $cm);
        }
    }
        
}
//출석 점수 설정
?>
<form method="post" name="form_setup" class="table-search-option" action="">
    <input type="hidden" name="id" value="<?php echo $id; ?>">
    <input type="hidden" name="type" value="<?php echo $type; ?>">
    <input type="hidden" name="submittype" value="grade">
    <div class="options">
        <div class="title"><?php print_string('setup:name', 'local_online_attendance'); ?></div>
        <input type="text" name="itemname" value="<?php echo $attendance->get_attendance_book_name(); ?>" class="search-text" placeholder="<?php echo get_string('attendance:book', 'local_online_attendance'); ?>">
    </div>
    <div class="options">
        <div class="title"><?php print_string('setup:maxscore', 'local_online_attendance'); ?></div>
        <?php echo local_online_attendance_drow_selectbox(100, 0, 10, $attendance->maxscore, array('name' => 'maxscore')); ?>
    </div>
    <div class="options">
        <div class="title"><?php print_string('setup:minscore', 'local_online_attendance'); ?></div>
        <?php echo local_online_attendance_drow_selectbox(100, 0, 10, $attendance->minscore, array('name' => 'minscore')); ?>
    </div>
    <div class="options">
        <div class="title"><?php print_string('setup:late', 'local_online_attendance'); ?></div>
        <?php echo local_online_attendance_drow_selectbox(10, 0, 1, $attendance->late, array('name' => 'latesubtract')); ?>
    </div>
    <div class="options">
        <div class="title"><?php print_string('setup:absent', 'local_online_attendance'); ?></div>
        <?php echo local_online_attendance_drow_selectbox(0, -10, 1, $attendance->absent, array('name' => 'absentsubtract')); ?>
    </div>
    <div class="options" style="text-align: right">
        <input type="submit" value="<?php echo get_string('setup:save', 'local_online_attendance'); ?>" class="board-search"/>
    </div>
</form>

<!-- 출석부 인정 활동-->
<h4 class="table-title"><?php print_string('setup:realize', 'local_online_attendance');?></h4>
<form method="post" name="form_realize" class="" action="">
    <input type="hidden" name="id" value="<?php echo $id; ?>">
    <input type="hidden" name="type" value="<?php echo $type; ?>">
    <input type="hidden" name="submittype" value="realize">
<?php 
    //출석부 인정 활동 설정
    // default 값이 생성되어있지 않으면 insert
    if(!$DB->record_exists('local_onattend_cm_batchset', array('courseid'=>$id))) {
        local_onattendance_default_batchset($id);
    }
    
    $batchmods = local_onattendance_get_batchset($id);

    // 활성화된 mod 유형
    $visiblemods = array();
    foreach($batchmods as $batchmod) {
        $visiblemods[$batchmod->modname] = $batchmod->visible;
    }
    
    // 출석부에 사용가능한 modules
    $realizes = local_onattendance_realize_modules();
    foreach($realizes as $realize){
        $icon = $OUTPUT->pix_icon('icon', $realize->modname, $realize->modname, array('class' => 'mod-icon', 'title' => $realize->modname, 'alt' => $realize->modname));
        $checked = '';
        if($visiblemods[$realize->modname]) {
            $checked = 'checked';
        }
?>
    <span class="realize options">
        <input type="checkbox" name="realizemod[]" <?php echo $checked; ?> value="<?php echo $realize->modname;?>" placeholder="<?php echo $realize->modname; ?>">
        <?php echo $icon;?>
    </span>
<?php } ?>
    <div class="options right text-right w100p">
        <input type="submit" value="<?php echo get_string('setup:save', 'local_online_attendance'); ?>" class="board-search"/>
    </div>
</form>

<!-- 활동별 일괄 설정-->
<h4 class="table-title"><?php print_string('setup:batch', 'local_online_attendance');?></h4>
<form method="post" name="form_realize" class="" action="">
    <input type="hidden" name="id" value="<?php echo $id; ?>">
    <input type="hidden" name="type" value="<?php echo $type; ?>">
    <input type="hidden" name="submittype" value="batch">
    <table class="generaltable">
            <thead>
                <tr>
                    <th>활동</th>
                    <th>시작일</th>
                    <th>시작시간</th>
                    <th>출석인정일</th>
                    <th>출석인정시간</th>
                    <th>출석인정범위</th>
                </tr>
            </thead>
            <tbody>
    <?php
        $batchs = local_onattendance_get_batchset($id);
        if(!empty($batchs)){
            foreach($batchs as $batch) {
                $icon = $OUTPUT->pix_icon('icon', $batch->modname, $batch->modname, array('class' => 'mod-icon', 'title' => $batch->modname, 'alt' => $batch->modname));
                $sdatetime = local_onattendance_get_datetime($batch->startratio);
                $adatetime = local_onattendance_get_datetime($batch->attendratio);
    ?>
                <tr>
                    <td><?php echo $icon;?></td>
                    <td><input type="text" class="batchset-date" name="<?php echo 'startdate['.$batch->id.']';?>" value="<?php echo $sdatetime->date;?>"></td>
                    <td><input type="text" class="clockpicker" name="<?php echo 'starttime['.$batch->id.']';?>" value="<?php echo $sdatetime->hour.':'.$sdatetime->minute;?>"></td>
                    <td><input type="text" class="batchset-date" name="<?php echo 'attenddate['.$batch->id.']';?>" value="<?php echo $adatetime->date;?>"></td>
                    <td><input type="text" class="clockpicker" name="<?php echo 'attendtime['.$batch->id.']';?>" value="<?php echo $adatetime->hour.':'.$adatetime->minute;?>"></td>
                    <td><input type="text" class="batchset-progress" name="<?php echo 'aprogress['.$batch->id.']';?>" value="<?php echo $batch->aprogress;?>"></td>
                </tr>
    <?php    
           } 
        } else {
             echo '<tr><td colspan="6">설정된 출석부 인정 활동이 없습니다.</td></tr>';
        }
    ?>
            </tbody>
    </table>
    <div class="options" style="text-align: right">
        <input type="submit" value="<?php echo get_string('setup:save', 'local_online_attendance'); ?>" class="board-search"/>
    </div>
</form>

<!-- 주차별 설정-->

<h4 class="table-title"><?php print_string('setup:section', 'local_online_attendance');?></h4>
<form method="post" name="form_realize" class="" action="">
    <input type="hidden" name="id" value="<?php echo $id; ?>">
    <input type="hidden" name="type" value="<?php echo $type; ?>">
    <input type="hidden" name="submittype" value="section">
    <table class="generaltable">
            <thead>
                <tr>
                    <th>주차</th>
                    <th>활동</th>
                    <th>시작일</th>
                    <th>시작시간</th>
                    <th>출석인정일</th>
                    <th>출석인정시간</th>
                    <th>출석인정범위</th>
                    <th>출석인정여부</th>
                </tr>
            </thead>
            <tbody>
    <?php
        if($DB->record_exists('local_onattend_cm_batchset', array('courseid'=>$id, 'visible'=>1))){
            $cmset = local_onattendance_get_cmset($id, true);
            if(!empty($cmset)) {
                foreach($cmset as $cm) {
                    $icon = $OUTPUT->pix_icon('icon', $cm->modname, $cm->modname, array('class' => 'mod-icon', 'title' => $cm->modname, 'alt' => $cm->modname));
                    $disabled = '';
                    $checked = '';
                    $displaynone = '';
                    if(!empty($cm->approval)) {
                        $checked = 'checked';
                    }
    ?>
                <tr class="">
                    <td><?php echo $cm->section;?></td>
                    <td><?php echo $icon;?></td>
                    <td><input type="text" class="datepicker" id="datepicker-start-<?php echo $cm->cmid;?>" name="<?php echo 'startdate['.$cm->cmid.']';?>" value="<?php echo date('Y-m-d',$cm->starttime);?>"></td>
                    <td><input type="text" class="clockpicker" name="<?php echo 'starttime['.$cm->cmid.']';?>" value="<?php echo date('H:i',$cm->starttime);?>"></td>
                    <td><input type="text" class="datepicker" id="datepicker-attend-<?php echo $cm->cmid;?>" name="<?php echo 'attenddate['.$cm->cmid.']';?>" value="<?php echo date('Y-m-d',$cm->attendtime);?>"></td>
                    <td><input type="text" class="clockpicker" name="<?php echo 'attendtime['.$cm->cmid.']';?>" value="<?php echo date('H:i',$cm->attendtime);?>"></td>
                    <td><input type="text" class="batchset-progress" name="<?php echo 'aprogress['.$cm->cmid.']';?>" value="<?php echo $cm->aprogress;?>" <?php echo $disabled; ?>></td>
                    <td><input type="checkbox" name="<?php echo 'approval['.$cm->cmid.']';?>" value="1" <?php echo $checked;?>></td>
                </tr>
        <?php 
                }
            } else {
                echo '<tr><td colspan="8">강의에 생성된 학습활동이 없습니다.</td></tr>';
            }
        } else {
            echo '<tr><td colspan="8">설정된 출석부 인정 활동이 없습니다.</td></tr>';
        }
    ?>
            </tbody>
    </table>
    <div class="options" style="text-align: right">
        <input type="submit" value="<?php echo get_string('setup:save', 'local_online_attendance'); ?>" class="board-search"/>
    </div>
</form>

<?php
    echo $OUTPUT->footer();

?>

<script type="text/javascript">
    $( function() {
        $(".batchset-date").spinner();
        $(".batchset-progress").spinner({step: 10});
        $('.clockpicker').clockpicker({
            placement: 'top',
            autoclose : true
        });
    } );
    
     $(document).ready(function() {
         <?php foreach($cmset as $cm) { ?>
            var startid = '#datepicker-start-<?php echo $cm->cmid;?>';
            var attendid = '#datepicker-attend-<?php echo $cm->cmid;?>';
            $(startid).datepicker({
                dateFormat: "yy-mm-dd",
                onClose: function( selectedDate ) {
                    $(attendid).datepicker( "option", "minDate", selectedDate );
                }
            });
            var attendmin = $(startid).datepicker( "getDate" );
            $(attendid).datepicker({
                dateFormat: "yy-mm-dd",
                minDate: attendmin,
                onClose: function( selectedDate ) {
                    $(startid).datepicker( "option", "maxDate", selectedDate );
                }
            });
            var startmax = $(attendid).datepicker( "getDate" );
            $(startid).datepicker( "option", "maxDate", startmax );
        <?php } ?>     
     });
</script>