<?php

$sql_select = ' SELECT se.*, st.status FROM {local_off_attendance_section} se '
             . 'LEFT JOIN {local_off_attendance_status} st ON st.courseid = se.courseid and st.lastcode = se.code ';

$sql_where = ' WHERE se.courseid = :courseid and se.timestart <= :time1 and se.timeend >= :time2 ';
$sql_order_by = ' ORDER BY se.timeend DESC ';

$params['courseid'] = $id;
$params['time1'] = time();
$params['time2'] = time();


$section = $DB->get_record_sql($sql_select.$sql_where.$sql_order_by, $params);

$date = '-';
$timestring = '-';
$disabled = true;
$datastring = 'data-timeend="0" data-set="0"';
if(!empty($section) && is_null($section->status)) {
    $disabled = false;
    $date = date('Y-m-d', $section->timedate);
    $time = $section->timeend - time();
    $minute = (int)($time / 60);
    $second = (int)($time%60);
    if($second < 10 ){
        $second = '0'+$second;
    }
    $timestring =  '0'.$minute.":".$second;
    
    $datastring = 'data-timeend="'.$section->timeend.'" data-set="1"';
}

?>
<table class="generaltable margin_bottom_40" id="table_check_info">
    <tbody>
        <tr>
            <th><?php print_string('book:empno', 'local_offline_attendance'); ?></th>
            <td><?php echo $USER->username;?></td>
        </tr>
        <tr>
            <th><?php print_string('manage:name', 'local_offline_attendance'); ?></th>
            <td><?php echo fullname($USER);?></td>
        </tr>
        <tr>
            <th><?php print_string('result:date', 'local_offline_attendance'); ?></th>
            <td><?php echo $date;?></td>
        </tr>
        <tr>
            <th><?php print_string('result:time', 'local_offline_attendance'); ?></th>
            <td id="autodiv" <?php echo $datastring; ?>><?php echo $timestring;?></td>
        </tr>
        <tr>
            <th><?php print_string('check:check', 'local_offline_attendance'); ?></th>
            <td><input type="text" name="code"  <?php echo $disabled ? 'disabled' : '' ?> placeholder="<?php echo get_string('check:code', 'local_offline_attendance'); ?>"></td>
        </tr>
    </tbody>
</table>
<?php
    if($disabled) {
        if(is_null($section->status)) {
            $text_string = get_string('check:expired', 'local_offline_attendance');
        } else {
            $text_string = get_string('check:complete', 'local_offline_attendance');
        }
        $text = '<div class="text" style="display:block">'.$text_string.'</div>';
        $button = '<div class="button" style="display:none"><input type="button" value="'.get_string('check:check', 'local_offline_attendance').'" id="attendance_check_butoon" onclick="attendance_check();"/></div>';
    } else {
        $text = '<div class="text" style="display:none">'.get_string('check:expired', 'local_offline_attendance').'</div>';
        $button = '<div class="button" style="display:block"><input type="button" value="'.get_string('check:check', 'local_offline_attendance').'" id="attendance_check_butoon" onclick="attendance_check();"/></div>';
    }
?>
<form method="post" name="form_check" class="table-search-option" action="">
    <?php
        echo $text;
        echo $button;
    ?>
</form>

<script type="text/javascript">
    function attendance_check() {
        var code = $('input[name=code]').val();
        if(code.trim() == 0) {
            alert('<?php print_string('check:alert3', 'local_offline_attendance'); ?>');
        }
        $.ajax({
          url: '<?php echo $CFG->wwwroot.'/local/offline_attendance/attendance_check.php'; ?>',
          method: 'POST',
          dataType: 'json',
          data : {
            id : <?php echo $id; ?>,
            code : code 
          },
          success: function(data) {
              if(data.status == 'success') {
                alert(data.text);
                document.location.href = './index.php?id=<?php echo $id;?>';
              } else if(data.status == 'fail'){
                alert(data.text);
              }
          } 
        });
    }
    setInterval(function(){
       var flag =  $('#autodiv').attr('data-set');
       if(flag == 1) {
           var timeend = $('#autodiv').attr('data-timeend');
           var timenow = new Date();
           var unixnow = parseInt(timenow.getTime() / 1000);
           var timegap = timeend - unixnow;
           var second = parseInt(timegap%60);
           if(second < 10) {
               second = '0'+second;
           }
           var viewtimer = '0'+parseInt(timegap / 60)+':'+second;
           $('#autodiv').text(viewtimer);
           if(timegap <= 0) {
             $('#autodiv').attr('data-set', 0);
             $('#autodiv').text('-');
             $('form[name=form_check] .text').show();
             $('form[name=form_check] .button').hide();
             $('input[name=itemname]').prop('disabled', true);
           }
       }
    }, 1000);
</script>    