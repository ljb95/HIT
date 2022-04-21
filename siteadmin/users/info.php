<?php
require_once dirname(dirname(dirname(__FILE__))) . '/config.php';
require_once dirname(dirname(__FILE__)) . '/lib/paging.php';
require_once dirname(dirname(__FILE__)) . '/lib.php';
require_once("$CFG->libdir/excellib.class.php");

// Check for valid admin user - no guest autologin
require_login(0, false);
if (isguestuser()) {
    $SESSION->wantsurl = (string) new moodle_url('/siteadmin/users/info.php');
    redirect(get_login_url());
}
$context = context_system::instance();

require_capability('moodle/site:config', $context);
 
$currpage = optional_param('page', 1, PARAM_INT);
$searchtext = optional_param('searchtext', '', PARAM_TEXT);
$hyear = optional_param('hyear', '', PARAM_RAW);
$dept = optional_param('dept', '', PARAM_RAW);
$juya = optional_param('juya', '', PARAM_RAW);
$perpage = optional_param('perpage', 20, PARAM_INT);
$excell = optional_param('excell', 0, PARAM_INT);

//데이터 가져오기

$dept_sql = "select distinct dept_cd, dept from {lmsdata_user} where dept like '%과%' ";
$dept_lists = $DB->get_records_sql($dept_sql,array());

$bunban_sql = "select distinct bunban from {lmsdata_class} ";
$bunban_lists = $DB->get_records_sql($bunban_sql,array());

$luon = '';
$where = '';

if (!empty($searchtext)) {
    $where = ' and  u.firstname like :searchtxt1 or u.lastname like :searchtxt2 or concat(u.firstname,u.lastname) like :searchtxt3 ';
}

    $where = $where.' and lu.dept like "%'.$dept.'%" and lu.day_tm_cd like "%'.$juya.'%"  ';
    if (!empty($hyear)) {
        if ($hyear == 'p1' || $hyear == 'p2') {
            $hyear = substr($hyear, 1);
            $luon = "and lu.hyear = :hyear and univ = '1'";
        } else {
            $luon = "and lu.hyear = :hyear ";
        }
    }

$sql = "select u.*, lu.dept, lu.userid, lu.eng_name, lu.b_mobile"
        . ", lu.b_email, lu.hyear, lu.day_tm_cd, lu.status, lu.status_nm  "     
        . " from {user} u "
        . "join {lmsdata_user} lu on lu.userid = u.id and lu.usergroup = 'rs' " . $luon
        . "where u.suspended != 1 AND u.deleted != 1 " . $where
        . "group by u.id order by u.username asc";
$params = array('hyear' => $hyear, 'searchtxt1' => '%' . $searchtext . '%', 'searchtxt2' => '%' . $searchtext . '%', 'searchtxt3' => '%' . $searchtext . '%');

if (!$excell) {

    $offset = ($currpage - 1) * $perpage;
    $users = $DB->get_records_sql($sql, $params, $offset, $perpage);

    $count_sql = "select count(distinct u.id) from {user} u "
            . "join {lmsdata_user} lu on lu.userid = u.id and lu.usergroup = 'rs' $luon where u.suspended != 1 AND u.deleted != 1 " . $where;

    $total_count = $DB->count_records_sql($count_sql, $params);
    $num = $total_count - $offset;


    $js = array(
        $CFG->wwwroot . '/siteadmin/manage/course_list.js'
    );

    include_once (dirname(dirname(__FILE__)) . '/inc/header.php');
    ?>
    <div id="contents">
        <?php include_once (dirname(dirname(__FILE__)) . '/inc/sidebar_users.php'); ?>

        <div id="content">
            <h3 class="page_title"><?php echo get_string('stu_management', 'local_lmsdata'); ?></h3>
            <div class="page_navbar"><a href="./info.php"><?php echo get_string('user_management', 'local_lmsdata'); ?></a> > <a href="./info.php"><?php echo get_string('stu_management', 'local_lmsdata'); ?></a></div>

            <form name="" id="course_search" class="search_area" action="info.php" method="get">
                <input type="hidden" title="page" name="page" value="1" />
                <b>주야구분 : </b> 
                <select title="주야" name="juya" class="w_160">
                    <option value="">- <?php echo get_string('contents_all', 'local_lmsdata'); ?> -</option>
                    <option value = '10' <?php if($juya == 10) echo 'selected'; ?>>주간</option>
                    <option value = '20' <?php if($juya == 20) echo 'selected'; ?>>야간</option>
                </select> 
                <b>학과 : </b> 
                <select title="학과" name="dept" class="w_160">
                    <option value="">- <?php echo get_string('contents_all', 'local_lmsdata'); ?> -</option>
                    <?php
                        foreach ($dept_lists as $dept_list){
                            $select = '';
                            if($dept == $dept_list->dept){
                                $select = 'selected'; 
                            }
                            if($dept_list->dept_cd == '' || $dept_list->dept_cd == ' ' || $dept_list->dept_cd == null){
                                continue;
                            }else{
                                echo '<option value="'.$dept_list->dept.'" '.$select.'>'.$dept_list->dept.' </option>';
                            }
                        }
                    ?>
                </select> 
                <b>학년 : </b> 
                <select title="학년" name="hyear" class="w_160">
                    <option value="">- <?php echo get_string('contents_all', 'local_lmsdata'); ?> -</option>
                    <option <?php if ($hyear == '1') echo 'selected'; ?> value="1">1<?php echo get_string('class','local_lmsdata'); ?></option>
                    <option <?php if ($hyear == '2') echo 'selected'; ?> value="2">2<?php echo get_string('class','local_lmsdata'); ?></option>
                    <option <?php if ($hyear == '3') echo 'selected'; ?> value="3">3<?php echo get_string('class','local_lmsdata'); ?></option>
                    <option <?php if ($hyear == '4') echo 'selected'; ?> value="4">4<?php echo get_string('class','local_lmsdata'); ?></option>
                </select> 
                <input type="text" title="search" name="searchtext" value="<?php echo $searchtext; ?>" placeholder="<?php echo get_string('name_placeholder','local_lmsdata'); ?>"  class="search-text"/>
                <input type="submit" class="search_btn" value="<?php echo get_string('search','local_lmsdata'); ?>"/>          
                <input type="button" class="blue_btn" value="<?php echo get_string('excell_down','local_lmsdata'); ?>" onclick="location.href='<?php echo $CFG->wwwroot; ?>/siteadmin/users/info.php?excell=1&search=<?php echo $search;?>&searchtext=<?php echo $searchtext; ?>&hyear=<?php echo $hyear; ?>&juya=<?php echo $juya; ?>&dept=<?php echo $dept; ?>'">
            </form><!--Search Area2 End-->

            <table>
                <caption class="hidden-caption">학생관리</caption>
                <thead>
                <tr>
                    <th scope="row" width="5%"><?php echo get_string('number', 'local_lmsdata'); ?></th>
                    <th scope="row" width="10%"><?php echo get_string('student_number', 'local_lmsdata'); ?></th>
                    <th scope="row" width="10%"><?php echo get_string('class','local_lmsdata'); ?></th>
                    <th scope="row" width="10%"><?php echo get_string('name','local_lmsdata'); ?></th>
                    <th scope="row" width="15%"><?php echo get_string('major','local_lmsdata'); ?></th>
                    <th scope="row"><?php echo get_string('email', 'local_lmsdata'); ?></th>
                    <th scope="row"><?php echo get_string('contact', 'local_lmsdata'); ?></th>
                    <th scope="row"><?php echo get_string('dayandnight', 'local_lmsdata'); ?></th>
                    <th scope="row">학적상태</th>
                </tr>
                </thead>
                <tbody>
                <?php
//                $hak_status = array(1 => '재학', 2 => '휴학', 3 => '제적(자퇴등)', 4 => '수료', 5 => '졸업');
                foreach ($users as $user) {
                    ?>
                    <tr>
                        <td width="5%"><?php echo $num; ?></td>
                        <td width="10%"><?php echo $user->username; ?></td>
                        <td width="7%"><?php
                            if($user->hyear == '0' || $user->hyear == null || $user->hyear == ''){ $user->hyear = '-'; }
                            echo $user->hyear;
                            ?></td>
                        <td width="10%"><a target="_blank" href="/user/profile.php?id=<?php echo $user->userid; ?>"><?php echo fullname($user); ?></a></td>
                        <td width="15%"><?php 
                            if($user->dept == '0' || $user->dept == null || $user->dept == ''){ $user->dept = '-'; }
                            echo $user->dept; ?></td>
                        <td><?php echo $user->email; ?></td>
                        <td><?php $str = $user->phone2;
                        if(strlen($str) == 11){
                            $phone[0] = substr($str, 0, 3);
                            $phone[1] = substr($str, 3, 4);
                            $phone[2] = substr($str, 7, 4);
                            echo $phone[0].'-'.$phone[1].'-'.$phone[2];
                        }else if(strlen($str) == 10){
                            $phone[0] = substr($str, 0, 3);
                            $phone[1] = substr($str, 3, 3);
                            $phone[2] = substr($str, 6, 4);
                            echo $phone[0].'-'.$phone[1].'-'.$phone[2];
                        }else{
                            echo $user->phone2;
                        }
                         ?></td>
                        <td><?php 
                            if($user->day_tm_cd == '0' || $user->day_tm_cd == null || $user->day_tm_cd == ''){ $user->day_tm_cd = '-'; 
                            }else if($user->day_tm_cd == '10'){$user->day_tm_cd = '주간';
                            }else if($user->day_tm_cd == '20'){$user->day_tm_cd = '야간';}
                        echo $user->day_tm_cd; ?></td>
                        <td><?php echo !empty($user->status_nm) ? $user->status_nm : '-'; ?></td>
                    </tr>
        <?php $num--;
    } if ($total_count <= 0) {
        ?>
                    <tr>
                        <td colspan="9">검색된 학생이 없습니다.</td>
                    </tr>
            <?php } ?>
                </tbody>
            </table><!--Table End-->

    <?php
    print_paging_navbar_script($total_count, $currpage, $perpage, 'javascript:cata_page(:page);');
    ?>


        </div><!--Content End-->

    </div> <!--Contents End-->

    <?php
    include_once ('../inc/footer.php');
} else {
    $users = $DB->get_records_sql($sql, $params);
    $num = count($users);

    $fields = array(
        get_string('number', 'local_lmsdata'),
        get_string('student_number', 'local_lmsdata'),
        get_string('class','local_lmsdata'),
        get_string('name','local_lmsdata'),
        get_string('major','local_lmsdata'),
        get_string('email', 'local_lmsdata'),
        get_string('contact', 'local_lmsdata'),
        get_string('dayandnight', 'local_lmsdata'),
        '학적상태'
    );

    $filename = '사용자_학생('.date('Ymd').').xls';

    $workbook = new MoodleExcelWorkbook('-');
    $workbook->send($filename);

    $worksheet = array();

    $worksheet[0] = $workbook->add_worksheet('');
    $col = 0;
    foreach ($fields as $fieldname) {
        $worksheet[0]->write(0, $col, $fieldname);
        $col++;
    }

    $row = 1;

    $hak_status = array(1 => '재학', 2 => '휴학', 3 => '제적(자퇴등)', 4 => '수료', 5 => '졸업');
    foreach ($users as $user) {
        $col = 0;
        $worksheet[0]->write($row, $col++, $num--);
        $worksheet[0]->write($row, $col++, $user->username);
        $hyear = '';
        if ($user->univ != 3) {
            if ($user->univ == 1)$hyear .= "예과 ";
            $hyear .= $user->hyear;
        } else {
            $hyear .= '대학원';
        }
        $status = !empty($user->status) ? $hak_status[$user->status] : '-';
        
        $worksheet[0]->write($row, $col++, $user->hyear);
        $worksheet[0]->write($row, $col++, fullname($user));
        $worksheet[0]->write($row, $col++, $user->dept);
        $worksheet[0]->write($row, $col++, $user->email);
        $worksheet[0]->write($row, $col++, $user->phone2);
        if($user->day_tm_cd == '0' || $user->day_tm_cd == null || $user->day_tm_cd == ''){ $user->day_tm_cd = '-'; 
        }else if($user->day_tm_cd == '10'){$user->day_tm_cd = '주간';
        }else if($user->day_tm_cd == '20'){$user->day_tm_cd = '야간';}
        $worksheet[0]->write($row, $col++, $user->day_tm_cd);
        $worksheet[0]->write($row, $col++, $status);
        $row++;
    }
    $workbook->close();
    die;
}
?>
