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
$perpage = optional_param('perpage', 20, PARAM_INT);
//데이터 가져오기



$where = '';
$params = array();
if (!empty($searchtext)) {
    $where = ' where  ip like :searchtxt1 or description like :searchtxt2 ';
    $params = array('searchtxt1' => '%' . $searchtext . '%', 'searchtxt2' => '%' . $searchtext . '%');
}

$sql = "select * from {ipblock} ";



$offset = ($currpage - 1) * $perpage;
$ips = $DB->get_records_sql($sql . $where, $params, $offset, $perpage);

$count_sql = "select count(id) from {ipblock} ";

$total_count = $DB->count_records_sql($count_sql . $where, $params);
$num = $total_count - $offset;


$js = array(
    $CFG->wwwroot . '/siteadmin/manage/course_list.js'
);

include_once (dirname(dirname(__FILE__)) . '/inc/header.php');
?>
<div id="contents">
    <?php include_once (dirname(dirname(__FILE__)) . '/inc/sidebar_users.php'); ?>

    <div id="content">
        <h3 class="page_title">IP 제한</h3>
        <div class="page_navbar"><a href="./info.php"><?php echo get_string('user_management', 'local_lmsdata'); ?></a> > <a href="./ipblock.php">IP 제한</a></div>

        <form name="" id="course_search" class="search_area" action="ipblock.php" method="get">
            <input type="hidden" title="page" name="page" value="1" />
            <input type="text" title="search" name="searchtext" value="<?php echo $searchtext; ?>" placeholder="<?php echo get_string('search_placeholder', 'local_lmsdata'); ?>"  class="search-text"/>
            <input type="submit" class="search_btn" value="<?php echo get_string('search', 'local_lmsdata'); ?>"/>          
        </form><!--Search Area2 End-->

        <table>
            <caption class="hidden-caption">IP차단</caption>
            <thead>
                <tr>
                    <th scope="row" width="5%"><?php echo get_string('number', 'local_lmsdata'); ?></th>
                    <th scope="row" width="30%">IP</th>
                    <th scope="row" width="">설명</th>
                </tr>
            </thead>
            <tbody>
                <?php
                foreach ($ips as $ip) {
                    ?>
                    <tr>
                        <td width="5%"><?php echo $num; ?></td>
                        <td width="30%"><strong style="cursor: pointer;" onclick="location.href = 'ipblock_add.php?id=<?php echo $ip->id; ?>'"><?php echo $ip->ip; ?></strong></td>
                        <td style="text-align:left;"><?php echo mb_strimwidth($ip->description, 0, 50); ?></td>
                    </tr>
                    <?php
                    $num--;
                } if ($total_count <= 0) {
                    ?>
                    <tr>
                        <td colspan="3">등록된 IP가 없습니다.</td>
                    </tr>
<?php } ?>
            </tbody>
        </table><!--Table End-->
        <div style="clear:both; float:right;"><input type="button" class="red_btn" onclick="location.href = 'ipblock_add.php'" value="IP추가"></div>
            <?php
            print_paging_navbar_script($total_count, $currpage, $perpage, 'ipblock.php?page=:page');
            ?>


    </div><!--Content End-->

</div> <!--Contents End-->

<?php
include_once ('../inc/footer.php');
?>
