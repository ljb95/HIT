<?php
require_once dirname(dirname(dirname(__FILE__))) . '/config.php';
require_once dirname(dirname(__FILE__)) . '/lib/paging.php';
require_once dirname(dirname(__FILE__)) . '/lib.php';
require_once("$CFG->libdir/excellib.class.php");

// Check for valid admin user - no guest autologin
require_login(0, false);
if (isguestuser()) {
    $SESSION->wantsurl = (string) new moodle_url('/siteadmin/manage/infpro.php');
    redirect(get_login_url());
}
$context = context_system::instance();
require_capability('moodle/site:config', $context);

$currpage = optional_param('page', 1, PARAM_INT);
$search = optional_param('search', 1, PARAM_INT);
$searchtext = optional_param('searchtext', '', PARAM_TEXT);
$usergroup = optional_param('usergroup', '', PARAM_RAW);
$perpage = optional_param('perpage', 20, PARAM_INT);
$excell = optional_param('excell', 0, PARAM_INT);

//데이터 가져오기
$luon = '';
$where = '';
if (!empty($usergroup)) {
    $luon = " and lu.usergroup = '" . $usergroup . "' ";
} else {
    $luon = " and (lu.usergroup = 'pr' or lu.usergroup = 'ad')";
}
if (!empty($searchtext)) {
    switch ($search) {
        case 1:
            $where = ' where u.firstname like :searchtxt1 
                or u.lastname like :searchtxt2 
                or u. firstname||lastname like :searchtxt3 
                or u.username like :searchtxt4 
                or lu.psosok like :searchtxt5 ';
            break;
        case 2:
            $where = ' where u.firstname like :searchtxt1 
                or u.lastname like :searchtxt2 
                or u. firstname||lastname like :searchtxt3 ';
            break;
        case 3:
            $where = ' where u.username like :searchtxt1 ';
            break;
        case 4:
            $luon .= ' and lu.psosok like :searchtxt1 ';
            break;
    }
}

$sql = "select u.* ,lu.usergroup,lu.psosok,lu.dept from {user} u "
        . "join {lmsdata_user} lu on lu.userid = u.id  " . $luon . " " . $where
        . "order by u.firstname asc";
$params = array('usergroup' => $usergroup,
    'searchtxt1' => '%' . $searchtext . '%',
    'searchtxt2' => '%' . $searchtext . '%',
    'searchtxt3' => '%' . $searchtext . '%',
    'searchtxt4' => '%' . $searchtext . '%',
    'searchtxt5' => '%' . $searchtext . '%');

$offset = ($currpage - 1) * $perpage;

if (!$excell) {

    $users = $DB->get_records_sql($sql, $params, $offset, $perpage);

//        $count_sql = "select count(u.id) from {user} u "
//            . "join {lmsdata_user} lu on lu.userid = u.id and lu.usergroup = 'pr' $luon where u.suspended != 1" . $where;
//
//    $total_count = $DB->count_records_sql($count_sql, $params);
    $total_count = $DB->count_records_sql("select count(u.id) 
        from {user} u 
        join {lmsdata_user} lu on lu.userid = u.id " . $luon . " " . $where, $params);
    $num = $total_count - $offset;


    $js = array(
        $CFG->wwwroot . '/siteadmin/manage/course_list.js'
    );

    include_once (dirname(dirname(__FILE__)) . '/inc/header.php');
    ?>
    <div id="contents">
        <?php include_once (dirname(dirname(__FILE__)) . '/inc/sidebar_users.php'); ?>

        <div id="content">
            <h3 class="page_title">교수관리</h3>
            <div class="page_navbar"><a href="./info.php"><?php echo get_string('user_management', 'local_lmsdata'); ?></a> > <a href="./infpro.php"><?php echo get_string('prof_management', 'local_lmsdata'); ?></a></div>

            <form name="" id="course_search" class="search_area" action="infpro.php" method="get">
                <input type="hidden" name="page" value="1" />
                <select title="category" name="search" class="w_160">
                    <option <?php if ($search == '1') echo 'selected'; ?> value="1">- <?php echo get_string('all','local_lmsdata'); ?> -</option>
                    <option <?php if ($search == '2') echo 'selected'; ?> value="2"><?php echo get_string('name','local_lmsdata'); ?></option>
                    <option <?php if ($search == '3') echo 'selected'; ?> value="3"><?php echo get_string('user_teachernumber', 'local_lmsdata'); ?></option>
                </select> 
<!--                <select name="usergroup" class="w_160">
                    <option value="">- <?php echo get_string('user_role', 'local_lmsdata'); ?> -</option>
                    <option value="pr" <?php //if ($usergroup == 'pr') echo 'selected'; ?>>교수</option>
                    <option value="ad" <?php //if ($usergroup == 'ad') echo 'selected'; ?>>조교</option>
                </select> -->
                <input type="text" title="serch" name="searchtext" value="<?php echo $searchtext; ?>" placeholder="<?php echo get_string('search_placeholder','local_lmsdata'); ?>"  class="search-text"/>
                <input type="submit" class="search_btn" value="<?php echo get_string('search','local_lmsdata'); ?>"/>          
                <input type="button" class="blue_btn" value="<?php echo get_string('excell_down','local_lmsdata'); ?>" onclick="location.href='<?php echo $CFG->wwwroot; ?>/siteadmin/users/infpro.php?excell=1&search=<?php echo $search;?>&searchtext=<?php echo $searchtext; ?>&usergroup=<?php echo $usergroup; ?>'">
                       
            </form><!--Search Area2 End-->
            
            <table>
                <caption class="hidden-caption">교수관리</caption>
                <thead>
                <tr>
                    <th scope="row" width="5%"><?php echo get_string('number', 'local_lmsdata'); ?></th>
                    <th scope="row" width="10%"><?php echo get_string('user_teachernumber', 'local_lmsdata'); ?></th>
                    <th scope="row" width="10%">학과/소속</th>
                    <th scope="row" width="10%"><?php echo get_string('name','local_lmsdata'); ?></th>
                    <th scope="row"><?php echo get_string('email', 'local_lmsdata'); ?></th>
                    <th scope="row"><?php echo get_string('contact', 'local_lmsdata'); ?></th>
                </tr>
                </thead>
                <tbody>
                <?php
                foreach ($users as $user) {
                    if ($user->usergroup == 'pr') {
                        $group = 'pr';
                    } else {
                        $group = 'ad';
                    }
                    ?>
                    <tr>
                        <td width="5%"><?php echo $num--; ?></td>
                        <td width="10%"><?php echo $user->username; ?></td>             
                        <td width="10%"><?php echo $user->dept; ?></td>            
                        <td width="10%"><a target="_blank" href="/user/profile.php?id=<?php echo $user->id; ?>"><?php echo fullname($user);?></a></td>
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
                        } ?></td>
                    </tr>
                <?php } if ($total_count <= 0) { ?>
                    <tr>
                        <td colspan="7">등록된 교수가 없습니다.</td>
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
        get_string('user_teachernumber', 'local_lmsdata'),
        get_string('name','local_lmsdata'),
        '학과/소속',
        get_string('email', 'local_lmsdata'),
        get_string('contact', 'local_lmsdata'),
    );

    $filename = '사용자_교수('.date('Ymd').').xls';

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
        $worksheet[0]->write($row, $col++, $user->username);
        $worksheet[0]->write($row, $col++, fullname($user));
        $worksheet[0]->write($row, $col++, $user->dept);
        $worksheet[0]->write($row, $col++, $user->email);
        $worksheet[0]->write($row, $col++, $user->phone2);
        $row++;
    }

    $workbook->close();
    die;
}
