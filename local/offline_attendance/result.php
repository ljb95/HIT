<?php

$sql_select = ' SELECT * FROM {local_off_attendance_status} ';
$sql_where = ' WHERE userid = :userid and courseid = :courseid';
$sql_order_by = ' ORDER BY timedate ASC ';

$params['courseid'] = $id;
$params['userid'] = $USER->id;

$attendance_books = $DB->get_records_sql($sql_select.$sql_where.$sql_order_by, $params);

?>
<table class="generaltable margin_bottom_40" id="table_courses">
    <tbody>
        <tr>
            <th><?php print_string('book:empno', 'local_offline_attendance'); ?></th>
            <td><?php echo $USER->username;?></td>
        </tr>
        <tr>
            <th><?php print_string('manage:name', 'local_offline_attendance'); ?></th>
            <td><?php echo fullname($USER);?></td>
        </tr>
    </tbody>
 </table>
 <table class="generaltable" id="table_courses">
    <thead>
        <tr>
            <th><?php print_string('result:date', 'local_offline_attendance'); ?></th>
            <th><?php print_string('result:time', 'local_offline_attendance'); ?></th>
            <th><?php print_string('manage:status1', 'local_offline_attendance'); ?></th>
            <th><?php print_string('manage:status2', 'local_offline_attendance'); ?></th>
            <th><?php print_string('manage:status3', 'local_offline_attendance'); ?></th>
            <th><?php print_string('manage:status4', 'local_offline_attendance'); ?></th>
        </tr>
    </thead>
    <tbody>
        
        <?php 
            $status = array(
                        1 =>'○',
                        2 =>'X',
                        3 =>'△',
                        4 =>'□'
                    );
            $count = array(
                        1 => 0,
                        2 => 0,
                        3 => 0,
                        4 => 0,
                    );
            if(!empty($attendance_books)) {
                foreach($attendance_books as $book) {
                    if($book->timemodified == $book->timecreated ) {
                        $timemodified = '-';
                    } else {
                        $timemodified = date('H:i', $book->timemodified);
                    }
                    
                    echo '<tr>';
                    echo '<td>'.date('Y-m-d', $book->timedate).'</td>';
                    echo '<td>'.$timemodified.'</td>';
                    
                    if($book->status == 1) {
                        echo '<td>O</td>';
                        $count[1]++;
                    } else {
                        echo '<td></td>';
                    }
                    
                    if($book->status == 2) {
                        echo '<td>O</td>';
                        $count[2]++;
                    } else {
                        echo '<td></td>';
                    }
                    
                    if($book->status == 3) {
                        echo '<td>O</td>';
                        $count[3]++;
                    } else {
                        echo '<td></td>';
                    }
                    
                    if($book->status == 4) {
                        echo '<td>O</td>';
                        $count[4]++;
                    } else {
                        echo '<td></td>';
                    }
                    
                    echo '<tr>';
                }
            } else {
               echo '<tr><td colspan="6">'.get_string('result:empty', 'local_offline_attendance').'</td></tr>';
            }
        ?>
    </tbody>
 </table>
<div class="table-footer-area">
    <?php
        $stringarr = array();
        foreach($count as $key => $co) {
            $string = '';
            $string .= get_string('manage:status'.$key, 'local_offline_attendance');
            $string .= ' - '.$co.'';
            $stringarr[] = $string;
        }
        echo implode(',', $stringarr);
    ?>
</div>