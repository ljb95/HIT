<?php 
require (dirname(dirname(dirname(__FILE__))) . '/config.php');

$id       = required_param('id', PARAM_INT);    // course id
$unixtime = required_param('unixtime', PARAM_NUMBER); // unixtime
$save = optional_param('save', 0, PARAM_INT);

$context = context_course::instance($id);
$PAGE->set_context($context);

if($save) {
    $code = required_param('code', PARAM_INT);
    $gap = required_param('gap', PARAM_INT);
    
    $section = new stdClass();
    $section->courseid = $id;
    $section->userid = $USER->id;
    $section->code = $code;
    $section->timestart = time();
    $section->timeend = time() + ($gap * 60);
    $section->timedate = $unixtime;
    $DB->insert_record('local_off_attendance_section', $section);
    
    // status 테이블에 값이 없을 경우(unixtime : 날짜 값이 최초인 경우) status 테이블에 status 필드 0 값으로 초기화 값 넣어줌
    $sql_select = "SELECT  ur.*, loa.status  ";
    $sql_from = " FROM {user} ur
                  JOIN (
                    SELECT userid 
                    FROM {role_assignments} 
                    WHERE contextid = :contextid 
                    GROUP BY userid 
                    ) ra ON ra.userid = ur.id
                  LEFT JOIN {local_off_attendance_status} loa ON ur.id = loa.userid and loa.timedate = :timedate and loa.courseid = :courseid ";

    $sql_conditions = array('ur.deleted = :deleted');
    $sql_params = array(
            'contextid' => $context->id,
            'timedate' => $unixtime,
            'courseid' => $id,
            'deleted' => 0
            );
    $sql_where = ' WHERE '.implode(' AND ', $sql_conditions);
    $sql_orderby = ' ORDER BY ur.firstname, lastname ASC ';

    $users = $DB->get_records_sql($sql_select.$sql_from.$sql_where.$sql_orderby, $sql_params);
    
    foreach($users as $user){
        if(is_null($user->status)) {
            $status = new stdClass();
            $status->courseid = $id;
            $status->userid = $user->id;
            $status->status = 2;
            $status->lastcode = 0;
            $status->timedate = $unixtime;
            $status->timecreated = time();
            $status->timemodified = time();
            
           $DB->insert_record('local_off_attendance_status', $status);
        }
    }
    
    
    $returnvalue->status = 'success';
    $returnvalue->timeend = $section->timeend;
    $returnvalue->code = $section->code;
    
    
    @header('Content-type: application/json; charset=utf-8');
    echo json_encode($returnvalue);
} else {
    
    $timedates = $DB->get_records_sql('SELECT code  FROM {local_off_attendance_section} ', array('courseid' => $id));

    do{
        $autonumber = mt_rand(10000, 99999);    
    }while(!empty($timedates[$autonumber]));

    ?>
        <div class="popup_content" id="course_prof">
        <form id="frm_auto_attendance" class="search_area" method="POST">
            <div class="popup_auto_setting">
                <div class="label-time">
                    <label><?php print_string('manage:allowedtime', 'local_offline_attendance');?></label>
                </div>
                <div class="select-time">
                    <select class="select perpage" id="timegap">
                        <?php
                        $max = 5;
                        for($num = 1 ; $num <= $max ; $num++) {
                            $selected = '';
                            if($num == $max) {
                                $selected = ' selected';
                            }
                            echo '<option value="'.$num.'"'.$selected.'>'.get_string('manage:minute', 'local_offline_attendance', $num).'</option>';
                        } ?>
                    </select>
                </div>
                <div class="select-input">
                    <input type="text" value="<?php echo $autonumber; ?>" id="autonumber" disabled="true"/>
                </div>
            </div>    
            <div class="popup_buttons">
                <input type="button" class="blue_btn" onclick="auto_attendance_save()" value="<?php print_string('setup:save', 'local_offline_attendance');?>"/>
                <input type="button" class="blue_btn" id="cancel" onclick="audo_attendance_cancel()" value="<?php print_string('manage:cancel', 'local_offline_attendance');?>"/>
            </div>
        </form>
    </div>

<script type="text/javascript">
    function auto_attendance_save() {
        var gap =   $('#timegap option:selected').val();
        var code = $( "#autonumber" ).val();
        $.ajax({
            url: '<?php echo $CFG->wwwroot.'/local/offline_attendance/auto_attendance_popup.php'; ?>',
            method: 'POST',
            dataType: 'json',
            data: { 
                id : <?php echo $id ?>,
                unixtime : <?php echo $unixtime ?>,
                save : 1,
                gap  : gap,
                code : code
            },
            success: function(data) {
                if(data.status == 'success') {
                    $('#autodiv').attr('data-timeend', data.timeend);
                    $('#autodiv').attr('data-set', 1);
                    $('#autobutton').hide();
                    var timenow = new Date();
                    var unixnow = timenow.getTime() / 1000
                    var timegap = data.timeend - unixnow;
                    var second = parseInt(timegap%60);
                    if(second < 10) {
                       second = '0'+second;
                    }
                    var viewtimer = '0'+parseInt(timegap / 60)+':'+second;
                    $('#viewtext').append('<?php print_string('manage:automatic', 'local_offline_attendance'); ?>');
                    $('#viewcode').append(data.code);
                    $('#viewtimer').append(viewtimer);
                    $('#viewtext').css('display', 'inline-block');
                    $('#viewcode').css('display', 'inline-block');
                    $('#viewtimer').css('display', 'inline-block');
                }
                $("#auto_attendance_popup").dialog( "close" );
            },
            error: function(jqXHR, textStatus, errorThrown ) {
            }
        });
    }
    function audo_attendance_cancel(){
         $("#auto_attendance_popup").dialog( "close" );
    }
</script> 
<?php } ?>


