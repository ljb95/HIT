<?php

$weeks_status = local_onattendance_week_status($id, $USER->id);

$wstatus_arr = array();
foreach($weeks_status as $wst) {
    $wstatus_arr[$wst->sec] = $wst->status; 
}
unset($weeks_status);

$attends = local_onattendance_get_status($id, $USER->id);

$sortdata = array();
foreach($attends as $attend) {
    $modtitle = $DB->get_field_sql('SELECT name FROM {'.$attend->modname.'} WHERE id = :id', array('id'=>$attend->instance));
    $attend->title = $modtitle;
    $sortdata[$attend->section][$attend->cmid] = $attend;
} 
unset($attends);
?>
<table class="table table-borderer no-top-border margin-bottom">
     <colgroup>
            <col width="25%" />
            <col width="75%" />
        </colgroup>
     <tbody>
         <tr>
            <th>학번</th>
            <td class="text-left"><?php echo $USER->username;?></td>
         </tr>
         <tr>
             <th>이름</th>
            <td class="text-left"><?php echo fullname($USER);?></td>
         </tr>
     </tbody>
</table> 
<table class="table table-borderer">
    <colgroup>
        <col width="50px" />
        <col width="10%" />
        <col width="/" />
    </colgroup>
    <thead>
        <tr>
            <th>주</th>
            <th>이름</th>
            <th>시작시간</th>
            <th>종료시간</th>
            <th>나의진도율</th>
            <th>요구진도율</th>
            <th>진도율</th>
            <th>출석</th>
        </tr>
    </thead>
    <tbody>
        <?php 
            if(!empty($sortdata)) {
                $status_string = array(
                            0 =>'X',
                            1 =>'○',
                            2 =>'△'
                        );
                foreach($sortdata as $week => $data) {
                    $count = count($data);
                    $start = true;
                    $weekstatus = $wstatus_arr[$week];
                    foreach($data as $status){
                        // 출석기간, 지각기간 인정 진도률로 % 수차 보정
                        if($status->aprogress != 0) {
                            $revision = floor(($status->aprogress / $status->alimit) * 100);
                            $revision = ($revision > 100) ? 100 : $revision;
                        } else {
                            $revision = 0;
                        }
                        echo '<tr>';
                        if($start) {
                            echo '<td rowspan="'.$count.'">'.$status->section.'</td>';
                        } 
                        echo '<td>'.$status->title.'</td>';
                        echo '<td>'.date('Y-m-d H:i',$status->starttime).'</td>';
                        echo '<td>'.date('Y-m-d H:i',$status->attendtime).'</td>';
                        echo '<td>'.$status->aprogress.'%</td>';
                        echo '<td>'.$status->alimit.'%</td>';
                        echo '<td>'.$revision.'%</td>';
                        if($start) {
                            echo '<td rowspan="'.$count.'">'.$status_string[$weekstatus].'</td>';
                        } 
                        echo '</tr>';
                        $start = false;
                    }
                }
           
                
            } else {
                echo '<tr><td colspan="7">강의에 등록된 활동 중 출석 체크 활동이 없습니다.</td></tr>';
            }
        ?>

    </tbody>
</table>

<?php
   echo $OUTPUT->footer();
?>