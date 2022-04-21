<?php

$dates = local_offline_attendance_dates($id);

// 오늘 날짜 00시00분00초 unixtime
$nowdate = date('Y-m-d', time());
$now = strtotime($nowdate);

$page = optional_param('page', 1, PARAM_INT);
$perpage = optional_param('perpage', 20, PARAM_INT);
$search = optional_param('search', '', PARAM_RAW);
$unixtime = optional_param('unixtime', $now, PARAM_RAW);

if(preg_match('/-/', $unixtime)){
    $unixtime = strtotime($unixtime);
}
$dates[$unixtime] = date('Y-m-d',$unixtime);
asort($dates);
$roleobjs = $DB->get_records('role', array('archetype' => 'student'));
$roles = array_keys($roleobjs);
list($sql_in, $sql_params) = $DB->get_in_or_equal($roles, SQL_PARAMS_NAMED, 'roleid');

$sql_select = "SELECT  ur.*,r.archetype, loa.status  ";
$sql_from = " FROM {user} ur
              JOIN (
                SELECT userid , roleid
                FROM {role_assignments} 
                WHERE contextid = :contextid AND roleid $sql_in
                GROUP BY userid 
                ) ra ON ra.userid = ur.id 
                JOIN {role} r on r.id = ra.roleid 
              LEFT JOIN {local_off_attendance_status} loa ON ur.id = loa.userid and loa.timedate = :timedate and loa.courseid = :courseid ";

$sql_conditions = array('ur.deleted = :deleted');

$sql_params['contextid'] = $context->id;
$sql_params['timedate'] = $unixtime;
$sql_params['courseid'] = $id;
$sql_params['deleted'] = 0;
//검색어
if (!empty($search)) {
    $like_fullname = $DB->sql_like('CONCAT(ur.firstname,ur.lastname)', ':fullname');
    $like_name = $DB->sql_like('ur.username', ':username');
    $sql_conditions[] = '(' . $like_fullname . ' or ' . $like_name . ')';
    $sql_params['fullname'] = '%' . $search . '%';
    $sql_params['username'] = '%' . $search . '%';
}

$sql_where = ' WHERE '.implode(' AND ', $sql_conditions);
$sql_orderby = ' ORDER BY ur.firstname, lastname ASC ';

$totalcount = $DB->count_records_sql('SELECT COUNT(*) '.$sql_from.$sql_where, $sql_params);
$users = $DB->get_records_sql($sql_select.$sql_from.$sql_where.$sql_orderby, $sql_params, ($page-1)*$perpage, $perpage);
$number = $totalcount - (($page-1)*$perpage);
?>

<form class="table-search-option stat_form" name="form_setup">
    <input type="hidden" name="id" value="<?php echo $id; ?>">
    <input type="hidden" name="type" value="<?php echo $type; ?>">
    <input type="hidden" name = "page" value="1">
    <input type="hidden" name = "perpage" value="<?php echo $perpage; ?>">
    <div class="options">
        <div class="title"><?php print_string('manage:attdendate', 'local_offline_attendance'); ?></div>
        <select name="unixtime" id="unixtime" onchange="change_time(this);">
                <?php 
                    foreach($dates as $key => $date) {
                        $selected = '';
                        if($unixtime == $key) {
                            $selected = 'selected';
                        }
                        echo '<option value="'.$key.'" '.$selected.'>'.$date.'</option>';
                    }
                ?>
            <option value="self">직접입력</option>
        </select>
        <div class="self" style="margin-left: calc(30% + 4px); display: none;"><input style="cursor: pointer;" type="text" placeholder="날짜를 선택하세요." readonly="" id="datepicker"><input type="button" onclick="new_date()" value="확인"></div>
    </div>
    <div class="options">
        <div class="title"><?php print_string('manage:search', 'local_offline_attendance'); ?></div>
        <input type="text" name="search" value="<?php echo $search; ?>" class="search-text" placeholder="<?php echo get_string('manage:placeholder', 'local_offline_attendance'); ?>">
        <input type="submit" value="<?php echo get_string('manage:search', 'local_offline_attendance'); ?>" class="board-search"/>
    </div>
    <?php 
       $params = array('courseid'=>$id, 'time1'=>time(), 'time2'=>time());
       $section = $DB->get_record_sql(' SELECT * FROM {local_off_attendance_section} WHERE courseid = :courseid and timestart <= :time1 and timeend >= :time2', $params);
       if(empty($section)){
    ?>
    <div class="options" id="autodiv" data-timeend="0" data-set='0'>
        <div class="title"><?php print_string('manage:auto', 'local_offline_attendance'); ?></div>
        <input type="button" id="autobutton" onclick="auto_attendance_popup()" value="<?php echo get_string('manage:autostart', 'local_offline_attendance'); ?>" class="auto-attendance-button"/>
        <div id='viewtext'></div>
        <div id='viewcode'></div>
        <div id='viewtimer'></div>
    </div>
    <?php 
       }else {
           $time = (int)($section->timeend - time());
           $minute = (int)($time / 60);
           $second = ($time%60);
           if($second < 10 ){
               $second = '0'+$second;
           }
    ?>
        <div class="options" id="autodiv" data-timeend="<?php echo $section->timeend ?>" data-set='1'>
        <div class="title"><?php print_string('manage:auto', 'local_offline_attendance'); ?></div>
        <input type="button" id="autobutton" onclick="auto_attendance_popup()" style="display:none" value="<?php echo get_string('manage:autostart', 'local_offline_attendance'); ?>" class="auto-attendance-button"/>
        <div id='viewtext' style="display: inline-block"><?php print_string('manage:automatic', 'local_offline_attendance'); ?></div>
        <div id='viewcode' style="display: inline-block"><?php echo $section->code; ?></div>
        <div id='viewtimer'style="display: inline-block"><?php echo '0'.$minute.":".$second; ?></div>
    </div>
    <?php }  ?>
</form>
<div class="guide" style="color:red;"><?php print_string('manage:guide', 'local_offline_attendance'); ?></div> 
<div class="manage-status">
    <div class="manage-status-submit">
        <div class="perpage">
            <select class="select perpage" name="perpage" onchange="change_perpage(this.options[this.selectedIndex].value, 'form_setup');">
                <?php
                $nums = array(10,20,50,100);
                foreach ($nums as $num) {
                    $selected = '';
                    if($num == $perpage) {
                        $selected = ' selected';
                    }
                    echo '<option value="'.$num.'"'.$selected.'>'.get_string('showperpage', 'local_courselist', $num).'</option>';
                } ?>
            </select>
        </div>
        <div id="status">
            <input type="radio" name="batch" value="0" /><?php print_string('manage:status0', 'local_offline_attendance'); ?>
            <input type="radio" name="batch" value="1" /><?php print_string('manage:status1', 'local_offline_attendance'); ?>
            <input type="radio" name="batch" value="2" /><?php print_string('manage:status2', 'local_offline_attendance'); ?>
            <input type="radio" name="batch" value="3" /><?php print_string('manage:status3', 'local_offline_attendance'); ?>
            <input type="radio" name="batch" value="4" /><?php print_string('manage:status4', 'local_offline_attendance'); ?>
            <input type="button" value="<?php print_string('manage:batch', 'local_offline_attendance'); ?>" id="attendance_batch_butoon" onclick="batch_attendance()"/>
        </div>
    </div>
</div>
 <table class="generaltable" id="table_courses">
    <thead>
        <tr>
            <th style="width:5%"><?php print_string('manage:num', 'local_offline_attendance'); ?></th>
            <th style="width:5%"><?php print_string('manage:picture', 'local_offline_attendance'); ?></th>
            <th style="width:10%"><?php print_string('manage:major', 'local_offline_attendance'); ?></th>
            <th style="width:10%"><?php print_string('manage:username', 'local_offline_attendance'); ?></th>
            <th style="width:5%"><?php print_string('manage:role', 'local_offline_attendance'); ?></th>
            <th><?php print_string('manage:name', 'local_offline_attendance'); ?></th>
            <th colspan="4"><?php print_string('manage:state', 'local_offline_attendance'); ?></th>
        </tr>
    </thead>
    <tbody>
        
        <?php 
            $status = array(
                        1 => get_string('manage:status1', 'local_offline_attendance'),
                        2 => get_string('manage:status2', 'local_offline_attendance'),
                        3 => get_string('manage:status3', 'local_offline_attendance'),
                        4 => get_string('manage:status4', 'local_offline_attendance')
                    );
            if(!empty($users)) {
                foreach($users as $user) {
                    $lmsdata = $DB->get_record('lmsdata_user',array('userid'=>$user->id));
                    echo '<tr>';
                    echo '<td>'.$number--.'</td>';
                    echo '<td>'.$OUTPUT->user_picture($user).'</td>';
                    echo '<td>'.$lmsdata->major.'</td>';
                    echo '<td>'.$user->username.'</td>';
                    echo '<td>'.get_string('defaultcourse'.$user->archetype).'</td>';
                    echo '<td>'. fullname($user).'</td>';
                    foreach($status as $key => $string){
                        $checked = '';
                        if(!empty($user->status) && $key == $user->status ) {
                            $checked = 'checked';
                        }
                        echo '<td><input type="radio" class="userstatus" name="'.$user->id.'" value='.$key.' '.$checked.'/>'.$string.'</td>';
                    }
                    echo '<tr>';
                }
            } else {
                echo '<tr><td colspan="8">'.get_string('manage:empty1', 'local_offline_attendance').'</td></tr>';
            }
        ?>
    </tbody>
 </table>
<div class="table-footer-area">
    <div class="btn-area btn-area-right">
        <?php if($unixtime){ ?>
        <input type="button" value="삭제" onclick="if(confirm('삭제하시겠습니까? 당일 출석에 대한 모든 데이터가 삭제됩니다.')){ location.href='delete_fromdate.php?unixtime=<?php echo $unixtime ?>&id=<?php echo  $id; ?>' }"/>
                <?php } ?>
        <input type="button" value="저장" onclick="Individual_attendance()"/>
    </div>
    <?php
     offattendance_print_paging_navbar_script($totalcount, $page, $perpage, 'javascript:goto_page(:page, \'form_setup\');', 10);
     ?>
</div>

<script type="text/javascript">
    function Individual_attendance() {
        
        var olduserid = '';
        var status = [];
        $('.userstatus').each(function(i, ele){
            var userid = $(ele).attr('name');
            if(olduserid != userid) {
                var st = $('input[name="'+userid+'"]:checked').val();
                if(st != null) {
                    var udata = userid+'/'+st;
                    status.push(udata)  
                    olduserid = userid;
                    
                }
            }
        });
        
        if(confirm('<?php print_string('manage:alert4', 'local_offline_attendance'); ?>')) {
            if(status.length == 0) {
                alert('<?php print_string('manage:alert2', 'local_offline_attendance'); ?>');
                return false;
            }
            $.ajax({
              url: '<?php echo $CFG->wwwroot.'/local/offline_attendance/auto_attendance_individual.php'; ?>',
              method: 'POST',
              dataType: 'json',
              data : {
                id : <?php echo $id ?>,  
                unixtime : <?php echo $unixtime ?>,  
                status : status  
              },
              success: function(data) {
                  if(data.status) {
                    alert(data.text);
                    $('form[name=form_setup]').submit();
                  }
              } 
            });
        }
    }
    function batch_attendance() {
        if(confirm('<?php print_string('manage:alert1', 'local_offline_attendance'); ?>')) {
            var value = $("input:radio[name=batch]:checked").val();
            if(value == null) {
                alert('<?php print_string('manage:alert2', 'local_offline_attendance'); ?>');
                return false;
            }
            $.ajax({
              url: '<?php echo $CFG->wwwroot.'/local/offline_attendance/auto_attendance_batch.php'; ?>',
              method: 'POST',
              dataType: 'json',
              data : {
                id : <?php echo $id ?>,  
                unixtime : <?php echo $unixtime ?>,  
                value : value  
              },
              success: function(data) {
                  if(data.status) {
                    alert(data.text);
                    $('form[name=form_setup]').submit();
                  }
              } 
            });
        }
    }
    function auto_attendance_popup() {
        var unixtime = $('#unixtime option:selected').val();
        var tag = $("<div id='auto_attendance_popup'></div>");
        $.ajax({
          url: '<?php echo $CFG->wwwroot.'/local/offline_attendance/auto_attendance_popup.php'; ?>',
          method: 'POST',
          data : {
            id : <?php echo $id ?>,  
            unixtime : unixtime  
          },
          success: function(data) {
            tag.html(data).dialog({
                title: '<?php echo get_string('manage:autoattend','local_offline_attendance');?>',
                modal: true,
                width: 450,
                resizable: false,
                height: 200,
                close: function () {
                    $('#frm_auto_attendance').remove();
                    $( this ).dialog('destroy').remove();
                }
            }).dialog('open');
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
           $('#viewtimer').text(viewtimer);
           if(timegap <= 0) {
             $('#autodiv').attr('data-set', 0);
             $('#viewtext').css('display', 'none');
             $('#viewcode').css('display', 'none');
             $('#viewtimer').css('display', 'none');
             $('#viewtext').text('');
             $('#viewcode').text('');
             $('#viewtimer').text('');
             $('#autobutton').show();
             
             $.ajax({
                  url: '<?php echo $CFG->wwwroot.'/local/offline_attendance/auto_attendance_early.php'; ?>',
                  method: 'POST',
                  dataType: 'json',
                  data : {
                    id : <?php echo $id; ?>,
                    timeend : timeend,
                    unixtime : <?php echo $unixtime ?>
                  },
                  success: function(data) {
                      if(data.status == 'success') {
                          $('#autodiv').attr('data-timeend', 0);
                      }
                  } 
                });
           }
       }
    }, 1000);
    function change_time(obj){
        if(obj.value != 'self'){
            obj.form.submit();
        } else {
            $('.self').show();
            $('.self input[type=text]').focus();
        }
    }
    function new_date(){ 
        if($('.self input[type=text]').val()){
            location.href='?id=<?php echo $id; ?>&unixtime='+$('.self input[type=text]').val();
        } else {
            alert('날짜를 선택해주세요.');
            $('.self input[type=text]').focus();
        }
    }
    <?php if(current_language() =='ko'){ ?>
    $.datepicker.setDefaults({
        dateFormat: 'yy-mm-dd',
        prevText: '이전 달',
        nextText: '다음 달',
        monthNames: ['1월', '2월', '3월', '4월', '5월', '6월', '7월', '8월', '9월', '10월', '11월', '12월'],
        monthNamesShort: ['1월', '2월', '3월', '4월', '5월', '6월', '7월', '8월', '9월', '10월', '11월', '12월'],
        dayNames: ['일', '월', '화', '수', '목', '금', '토'],
        dayNamesShort: ['일', '월', '화', '수', '목', '금', '토'],
        dayNamesMin: ['일', '월', '화', '수', '목', '금', '토'],
        showMonthAfterYear: true,
        yearSuffix: '년'
    });
    <?php } ?>
    $( function() {
        $( "#datepicker" ).datepicker();
    } );
</script>