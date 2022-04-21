<?php
require_once dirname(dirname(dirname(__FILE__))) . '/config.php';
require_once dirname(dirname(__FILE__)) . '/lib/paging.php';
require_once dirname(dirname(__FILE__)) . '/lib.php';
require_once("$CFG->libdir/excellib.class.php");

// Check for valid admin user - no guest autologin
require_login(0, false);
if (isguestuser()) {
    $SESSION->wantsurl = (string) new moodle_url('/siteadmin/support/taxdeduction.php');
    redirect(get_login_url());
}
$context = context_system::instance();
require_capability('moodle/site:config', $context);

$currpage = optional_param('page', 1, PARAM_INT);
$searchtext = optional_param('searchtext', '', PARAM_TEXT);
$perpage = optional_param('perpage', 10, PARAM_INT);
$excell = optional_param('excell', 0, PARAM_INT);
$search       = optional_param('search', 0, PARAM_INT);


//데이터 가져오기

$luon = '';
$where = '';

$sql = "select * from {local_supporttex} ";

$sql_where   =  array();
$params = array();
$excel_params = array();

    $offset = ($currpage - 1) * $perpage;
    
if(!empty($searchtext)) {    
    switch($search) {
        case 0: // 이름
            $sql_where[]= '( '.$DB->sql_like('username', ':username').' or '.$DB->sql_like('userid', ':userid'). ')';
            $params['username'] = '%'.$searchtext.'%';
            $params['userid'] = '%'.$searchtext.'%';
            break;
        case 1: // 아이디
            $sql_where[]= $DB->sql_like('username', ':username');
            $params['username'] = '%'.$searchtext.'%';
            break;
        case 2: // 이름
            $sql_where[] = $DB->sql_like('userid', ':userid');
            $params['userid'] = '%'.$searchtext.'%';
            break;
        default:
            break;
    }
}
    if(!empty($sql_where)) {
        $sql_where = 'WHERE '.implode(' and ', $sql_where);
    }else {
        $sql_where = '';
    }

    $users = $DB->get_records_sql($sql.$sql_where, $params, $offset, $perpage);
    $count_sql = "select count(id) from {local_supporttex} ";
    
    $total_count = $DB->count_records_sql($count_sql, $params);
    $num = $total_count - $offset;
    
    $js = array(
        $CFG->wwwroot . '/siteadmin/manage/course_list.js'
    );
    
    if (!$excell) {
        
    include_once('../inc/header.php'); ?>
<div id="contents">
    <?php include_once('../inc/sidebar_support.php'); ?>
    <div id="content">
            <h3 class="page_title">연말정산관리</h3>
            <div class="page_navbar"><a href="./notices.php"><?php echo get_string('user_management', 'local_lmsdata'); ?></a> > <a href="./taxdeduction.php">연말정산관리</a></div>

            <form name="" id="course_search" class="search_area" action="taxdeduction.php" method="get">
                <input type="hidden" name="page" value="1" />

            <select name="search" class="w_160">
                <option value="0" <?php echo (!empty($search) && ($search == 0)) ? 'selected' : ''; ?> ><?php echo get_string('all','local_lmsdata'); ?></option>
                <option value="1" <?php echo (!empty($search) && ($search == 1)) ? 'selected' : ''; ?> >아이디</option>
                <option value="2" <?php echo (!empty($search) && ($search == 2)) ? 'selected' : ''; ?> ><?php echo get_string('name','local_lmsdata'); ?></option>
            </select> 
                <input type="text" name="searchtext" value="<?php echo $searchtext; ?>" placeholder="<?php echo get_string('search_placeholder','local_lmsdata'); ?>"  class="search-text"/>
                <input type="submit" class="blue_btn" value="<?php echo get_string('search','local_lmsdata'); ?>"/>          
<input type="button" class="red_btn" value="<?php echo get_string('excell_down','local_lmsdata'); ?>" onclick="location.href='<?php echo $CFG->wwwroot; ?>/siteadmin/support/taxdeduction.php?excell=1&search=<?php echo $search;?>&searchtext=<?php echo $searchtext; ?>'">
            </form><!--Search Area2 End-->

            <table>
                <tr>
                    <th width="5%"><?php echo get_string('number', 'local_lmsdata'); ?></th>
                    <th width="10%">후원번호</th>
                    <th width="5%">아이디</th>
                    <th width="5%"><?php echo get_string('name','local_lmsdata'); ?></th>
                    <th width="10%">후원금액</th>
                    <th width="10%">결제방식</th>
                    <th width="20%">주소</th>
                    <th width="10%">생년월일</th>
                    <th width="10%">연락처</th>
                    <th width="10%">후원일</th>
                </tr>
                <?php
                foreach ($users as $user) {

                    ?>
                    <tr>
                        <td width="5%"><?php echo $num; ?></td>
                        <td width="10%"><?php echo $user->supportnum; ?></td>
                        <td width="5%"><?php echo $user->userid; ?></td>
                        <td width="5%"><?php echo $user->username; ?></td>
                        <td width="10%"><?php echo $user->amount."원"; ?></td>
                        <td width="10%"><?php echo $user->paytype; ?></td>
                        <td width="20%"><?php echo $user->address; ?></td>
                        <td width="10%"><?php echo $user->birthday; ?></td>
<!--                        <td width="10%"><?php echo "0".substr($user->receiverphone, 0,2)."-".substr($user->receiverphone, 2,4)."-".substr($user->receiverphone, 6,4); ?></td>-->
                        <td width="10%"><?php echo $user->receiverphone; ?></td>
                        <td width="10%"><?php echo substr($user->timecreated, 0,4)."년".substr($user->timecreated, 4,2)."월".substr($user->timecreated, 6,2)."일"; ?></td>
                    </tr>
        <?php $num--;
                } if ($total_count <= 0) {
        ?>
                    <tr>
                        <td colspan="9">등록된 학생이 없습니다.</td>
                    </tr>
            <?php } ?>
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
    $fields = array(
        '번호',
        '후원번호',
        '아이디',
        get_string('name','local_lmsdata'),
        '후원금액',
        '결제방식',
        '주소',
        '생년월일',
        '연락처',
        '후원일',
    );

    $filename = '연말정산신청자 리스트('.date('Ymd').').xls';

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

    foreach ($users as $user) {
        $col = 0;
        $worksheet[0]->write($row, $col++, $num--);
        $worksheet[0]->write($row, $col++, $user->supportnum);
        $worksheet[0]->write($row, $col++, $user->userid);
        $worksheet[0]->write($row, $col++, $user->username);
        $worksheet[0]->write($row, $col++, $user->amount."원");
        $worksheet[0]->write($row, $col++, $user->paytype);
        $worksheet[0]->write($row, $col++, $user->address);
        $worksheet[0]->write($row, $col++, $user->birthday);
        $worksheet[0]->write($row, $col++, "0".substr($user->receiverphone, 0,2)."-".substr($user->receiverphone, 2,4)."-".substr($user->receiverphone, 6,4));
        $worksheet[0]->write($row, $col++, substr($user->timecreated, 0,4)."년".substr($user->timecreated, 4,2)."월".substr($user->timecreated, 6,2)."일");        
        $row++;
    }

    $workbook->close();
    die;
}
?>