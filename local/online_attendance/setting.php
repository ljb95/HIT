<?php
require(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once $CFG->libdir . '/formslib.php';
require_once $CFG->dirroot . '/local/online_attendance/lib.php';

$id = required_param('id', PARAM_INT); // course id
$type = optional_param('type', 0, PARAM_INT); 
$submit = optional_param('submit', 0, PARAM_INT); 

$course = $DB->get_record('course', array('id'=>$id), '*', MUST_EXIST);

require_course_login($course, true);
$context = context_course::instance($course->id);
$PAGE->set_context($context);

$url = new moodle_url('/local/online_attendance/index.php', array('id' => $id, 'type' => $type));
$PAGE->set_url($url);

$PAGE->set_pagelayout('incourse');
$PAGE->requires->jquery();
$PAGE->requires->jquery_plugin('ui');
$PAGE->requires->jquery_plugin('ui-css');
$PAGE->requires->jquery_plugin('migrate');
$PAGE->requires->css('/local/online_attendance/style.css');
$PAGE->requires->js('/siteadmin/js/lib/jquery.ui.datepicker-ko.js');

$strplural = get_string("pluginnameplural", "local_online_attendance");
$PAGE->navbar->add($strplural);
$PAGE->set_title($strplural);

if(has_capability('local/online_attendance:progress_viewall', $context)) {

    //tab
    $row = array();
    $row[] = new tabobject('0', new moodle_url('/local/online_attendance/index.php', array('id' => $id, 'type' => 0)), get_string('lcms:progress', 'local_online_attendance'));
    $row[] = new tabobject('1', new moodle_url('/local/online_attendance/status.php', array('id' => $id, 'type' => 1)), get_string('attendance:status', 'local_online_attendance'));
    $row[] = new tabobject('2', new moodle_url('/local/online_attendance/setting.php', array('id' => $id, 'type' => 2)), get_string('attendance:setting', 'local_online_attendance'));
    $rows[] = $row;

    $PAGE->navbar->add($rows[0][$type]->text);
    $PAGE->set_heading($rows[0][$type]->text);

    echo $OUTPUT->header();

    print_tabs($rows, $type);
    if(!$DB->record_exists('local_on_attendance', array('courseid'=>$id))) {
        local_online_attendance_created($id);
    }
    $courseoption = $DB->get_record('local_on_attendance', array('courseid' => $id));
    $sectionoptions = $DB->get_records('local_on_attendance_section', array('courseid' => $id));
    
    if($submit) {
        
        //local_on_attendance 테이블에 넣을 값
        $maxscore = optional_param('maxscore', 0, PARAM_INT); 
        $minscore = optional_param('minscore', 0, PARAM_INT); 
        $latesubtract = optional_param('latesubtract', 0, PARAM_INT); 
        $absentsubtract = optional_param('absentsubtract', 0, PARAM_INT); 
        
        $courseoption->maxscore = $maxscore;
        $courseoption->minscore = $minscore;
        $courseoption->latesubtract = $latesubtract;
        $courseoption->absentsubtract = $absentsubtract;
        $courseoption->timemodified = time();
        $courseoption->userid = $USER->id;
        $DB->update_record('local_on_attendance', $courseoption);
        
        //local_on_attendance_section 테이블에 넣을 값
        $timestarts = required_param_array('timestart', PARAM_ALPHANUMEXT);
        $timeends = required_param_array('timeend', PARAM_ALPHANUMEXT);
        $attendscopes = required_param_array('attendscope', PARAM_INT);
        $timelates = required_param_array('timelate', PARAM_ALPHANUMEXT);
        $latescopes = required_param_array('latescope', PARAM_INT);
        $approvals = required_param_array('approval', PARAM_BOOL);
        
        foreach($sectionoptions as $option) {
            $update_flag = false;
            $index = $option->section;
            
            $timestart = strtotime($timestarts[$index]);
            if($option->timestart != $timestart) {
                $option->timestart = $timestart;
                $update_flag = true;
            }
            
            $timeend = strtotime($timeends[$index]);
            if($option->timeend != $timeend) {
                $option->timeend = $timeend;
                $update_flag = true;
            }
            
            $attendscope = $attendscopes[$index];
            if($option->attendscope != $attendscope) {
                $option->attendscope = $attendscope;
                $update_flag = true;
            }
            
            $timelate = strtotime($timelates[$index]);
            if($option->timelate != $timelate) {
                $option->timelate = $timelate;
                $update_flag = true;
            }
            
            $latescope = $latescopes[$index];
            if($option->latescope != $latescope) {
                $option->latescope = $latescope;
                $update_flag = true;
            }
            
            $approval = $approvals[$index];
            if($option->approval != $approval) {
                $option->approval = $approval;
                $update_flag = true;
            }
            
            if($update_flag) {
                $DB->update_record('local_on_attendance_section', $option);
            }
        }
    }
?>

    <form method="post" enctype="multipart/form-data" name="form_setting" action="">
        <table class="generaltable setting-course">
            <thead>
                <tr>
                    <th class="setting-th"><?php echo get_string('grade:total', 'local_online_attendance'); ?></th>
                    <td><?php echo local_online_attendance_drow_selectbox(20, 10, 1, $courseoption->maxscore, array('name' => 'maxscore')); ?></td>
                </tr>  
                <tr>
                    <th class="setting-th"><?php echo get_string('grade:min', 'local_online_attendance'); ?></th>
                    <td><?php echo local_online_attendance_drow_selectbox(10, 0, 1, $courseoption->minscore, array('name' => 'minscore')); ?></td>
                </tr>  
                <tr>
                    <th class="setting-th"><?php echo get_string('grade:late', 'local_online_attendance'); ?></th>
                    <td><?php echo local_online_attendance_drow_selectbox(0, -3, 1, $courseoption->latesubtract, array('name' => 'latesubtract')); ?></td>
                </tr>  
                <tr>
                    <th class="setting-th"><?php echo get_string('grade:absence', 'local_online_attendance'); ?></th>
                    <td><?php echo local_online_attendance_drow_selectbox(0, -3, 1, $courseoption->absentsubtract, array('name' => 'absentsubtract')); ?></td>
                </tr>  
            </thead>
        </table>
        <table class="generaltable setting-section">
            <thead>
                <tr>
                    <th><?php echo get_string('course:weeks', 'local_online_attendance'); ?></th>
                    <th><?php echo get_string('course:startdate', 'local_online_attendance'); ?></th>
                    <th><?php echo get_string('attendance:period', 'local_online_attendance'); ?></th>
                    <th><?php echo get_string('attendance:range', 'local_online_attendance'); ?></th>
                    <th><?php echo get_string('late:period', 'local_online_attendance'); ?></th>
                    <th><?php echo get_string('late:range', 'local_online_attendance'); ?></th>
                    <th><?php echo get_string('attendance:approval', 'local_online_attendance'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php 
                    foreach($sectionoptions as $option) {
                ?>
                    <tr>
                        <td><?php echo get_string('course:week', 'local_online_attendance', $option->section);?></td>
                        <td>
                            <div><input type="text" name="timestart[<?php echo $option->section;?>]" class="section-date timestart" value="<?php echo date('Y-m-d', $option->timestart);?>" placeholder="yyyy-mm-dd"/></div>
                            <div class="section-time">00:00:00</div>
                        </td>
                        <td>
                            <div><input type="text" name="timeend[<?php echo $option->section;?>]" class="section-date timeend" value="<?php echo date('Y-m-d', $option->timeend);?>" placeholder="yyyy-mm-dd"/></div>
                            <div class="section-time">23:59:59</div>
                        </td>
                        <td>
                            <input type="text" name="attendscope[<?php echo $option->section;?>]" class="section-scope attendscope" value="<?php echo $option->attendscope;?>" />
                        </td>
                        <td>
                            <div><input type="text" name="timelate[<?php echo $option->section;?>]" class="section-date timelate" value="<?php echo date('Y-m-d', $option->timelate);?>" placeholder="yyyy-mm-dd"/></div>
                            <div class="section-time">23:59:59</div>
                        </td>
                        <td>
                            <input type="text" name="latescope[<?php echo $option->section;?>]" class="section-scope attendscope" value="<?php echo $option->latescope;?>" />
                        </td>
                        <td><input type="checkbox" name="approval[<?php echo $option->section;?>]" class="approval-check approval" <?php echo $option->approval ? 'checked' : '';?> ></td>
                    </tr>
                <?php        
                    }
                ?>
            </tbody>
        </table>
        <div class="table-filter-area">
            <input type="hidden" value ="1" name="submit" />
            <input type="submit" value="<?php echo get_string('setting:save', 'local_online_attendance');?>" class="board-search" />
        </div>
    </form>
<?php
}
echo $OUTPUT->footer();
?>

<script type="text/javascript">
    
    $(document).ready(function() {
        var timestart = $('.timestart');
        var timeend = $('.timeend');
        var timelate = $('.timelate');
        $.each(timestart, function(index, item){
            $(this).datepicker({
                dateFormat: "yy-mm-dd",
                onClose: function( selectedDate ) {
                    $(timeend[index]).datepicker( "option", "minDate", selectedDate );
                }
            });
        });
        $.each(timeend, function(index, item){
            $(this).datepicker({
                dateFormat: "yy-mm-dd",
                onClose: function( selectedDate ) {
                    $(timelate[index]).datepicker( "option", "minDate", selectedDate );
                }
            });
        });
        $.each(timelate, function(index, item){
            $(this).datepicker({
                dateFormat: "yy-mm-dd",
                onClose: function( selectedDate ) {
                    $(timestart[index]).datepicker( "option", "maxDate", selectedDate );
                }
            });
        });
        
    });
</script>