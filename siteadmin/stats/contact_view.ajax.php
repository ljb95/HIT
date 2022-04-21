<?php
require (dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once dirname(dirname(__FILE__)) . '/lib/paging.php';
require_once dirname(dirname(__FILE__)) . '/lib.php';


$time = required_param('time', PARAM_INT);
$timestart = required_param('timestart', PARAM_INT);
$timeend = required_param('timeend', PARAM_INT);
$stat = required_param('stat', PARAM_RAW);
$mobile = optional_param('mobile', '', PARAM_RAW);

//강좌별 수강인원 가져오기
$select = " SELECT lo.id, FROM_UNIXTIME(lo.log_date,'%Y-%m-%d %H:%s:%i') as time, lo.ip, lo.username, lo.action, lo.mobile  ";

$from = "FROM {siteadmin_loginfo} lo
         join {user} u on u.username = lo.username
         JOIN {lmsdata_user} lu ON lu.userid = u.id ";
$conditions = array('lo.action = :isloggedin AND lo.log_date > :timestart AND lo.log_date < :timeend');
$param['isloggedin'] = 'logged';
$param['timestart'] = $timestart;
$param['timeend'] = $timeend;
$param['year'] = date("Y", $time);
$param['month'] = date("m", $time);
$param['day'] = date("d", $time);
$param['hour'] = date("H", $time);

if($stat == 'day'){
    $conditions[] = "FROM_UNIXTIME(lo.log_date,'%H') = :hour";
}else if($stat == 'month'){
    $conditions[] = "FROM_UNIXTIME(lo.log_date,'%d') = :day";
}else if($stat == 'year'){
    $conditions[] = "FROM_UNIXTIME(lo.log_date,'%m') = :month";
}else{
    $conditions[] = "FROM_UNIXTIME(lo.log_date,'%Y') = :year";
    $conditions[] = "FROM_UNIXTIME(lo.log_date,'%m') = :month";
    $conditions[] = "FROM_UNIXTIME(lo.log_date,'%d') = :day";
    
    if($mobile == 'P' || $mobile == 'M') {
        $conditions[] = "lo.mobile = '".$mobile."'";
    }else{
        $conditions[] = "lo.mobile NOT IN ('P', 'M')";
    }
}

if ($conditions)
    $where = ' WHERE ' . implode(' AND ', $conditions);

$sort = " order by FROM_UNIXTIME(lo.log_date,'%Y-%m-%d %H:%s:%i') DESC";

$contacts = $DB->get_records_sql($select . $from . $where . $sort , $param);

//$totalcount = $DB->count_records_sql('SELECT count(*) ' . $from . $where, $params);
?>

<div class="popup_content" id="class_students">
    <h2>로그인 통계</h2>

    <!--<p class="search_result">(<?php echo ceil($totalcount / $perpage); ?>페이지, 총 <?php echo $totalcount; ?>건)</p>-->

    <form id="frm_class_students" name="frm_class_students" onsubmit="return false;">
        <table cellpadding="0" cellspacing="0">
            <tbody>
                <tr>                
                    <th><?php echo get_string('stats_date', 'local_lmsdata') ?></th>
                    <th><?php echo get_string('stats_ip', 'local_lmsdata') ?></th>
                    <th><?php echo get_string('user_id', 'local_lmsdata') ?></th>
                    <th><?php echo get_string('stats_act', 'local_lmsdata') ?></th>
                </tr>
                <?php
                    foreach ($contacts as $contact) {                     
                        
                        echo '<tr>';
                        echo '<td>' . $contact->time . '</td>';
                        echo '<td>' . $contact->ip . '</td>';
                        echo '<td>' . $contact->username . '</td>';
                        echo '<td>' . $contact->action . '</td>';
                        echo '</tr>';
                    }
                ?>
            </tbody>
        </table>
    </form>

    <?php
//    print_paging_navbar_script($totalcount, $currpage, $perpage, 'javascript:class_students_search(:page);');
    ?>
<!--
    <div class="btn_area">
        <input type="button" id="" class="blue_btn" value="엑셀다운로드" onclick="student_list_excel(); return false;" style="float: left">
    </div>-->


    <script type="text/javascript">
        function student_list_excel() {
            var url = "student_grade.excel.php?id=<?php echo $id; ?>";

            document.location.href = url;
        }
    </script>    
