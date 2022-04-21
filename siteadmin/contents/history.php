<?php
require(dirname(dirname(dirname(__FILE__))) . '/config.php');

// Check for valid admin user - no guest autologin
require_login(0, false);
if (isguestuser()) {
    $SESSION->wantsurl = (string) new moodle_url('/siteadmin/manage/course_list.php');
    redirect(get_login_url());
}
$context = context_system::instance();
require_capability('moodle/site:config', $context);

require_once dirname(dirname(__FILE__)) . '/lib/paging.php';
require_once dirname(dirname(__FILE__)) . '/lib/contents_lib.php';


$userid = optional_param('userid', 0, PARAM_INT);
$ctype = optional_param('ctype', "all", PARAM_RAW);
$public = optional_param('public', "all", PARAM_RAW);

$search = optional_param('search', '', PARAM_RAW);
$page = optional_param('page', 1, PARAM_INT);
$perpage = optional_param('perpage', 10, PARAM_INT);

$context = context_system::instance();

require_login();

$PAGE->set_context($context);
$PAGE->set_url('/siteadmin/contents/index.php');

$like = '';
$param =array();
$where = '';
if (!empty($search)) {
    $like = " and " . $DB->sql_like('con_name', ':search', false);
    $param['search'] = '%'.$search.'%';
    $where = " where lc.id is not null ";
}

/*
  $sql = "select count(id) from {jinoboard_contents} where board = :board " . $like . " and isnotice = 0 order by ref DESC, step ASC";
  $totalcount = $DB->count_records_sql($sql, array('board' => $board->id, 'search' => '%' . $search . '%'));
  $total_pages = jinoboard_get_total_pages($totalcount, $perpage);
 */
?>
<?php include_once('../inc/header.php'); ?>
<div id="contents">
    <?php include_once('../inc/sidebar_contents.php'); ?>
    <div id="content">
        <h3 class="page_title"><?php echo get_string('contents_contenthistory', 'local_lmsdata'); ?></h3>
        <div class="page_navbar"><a href="<?php echo $CFG->wwwroot . '/siteadmin/contents/index.php'; ?>"><?php echo get_string('lcms_management', 'local_lmsdata'); ?></a> > <a href = '#'><?php echo get_string('contents_contenthistory', 'local_lmsdata'); ?></a></div>

        <!-- 검색폼 시작 -->

        <form id="frm_notices_search" class="search_area">
            <input type="hidden" title="type" name="type" value="<?php echo $type; ?>">
            <select name="perpage" title="page" class="w_160" onchange="this.form.submit();">
                <?php
                $nums = array(10, 20, 30, 50);
                foreach ($nums as $num) {
                    $selected = ($num == $perpage) ? 'selected' : '';

                    echo '<option value="' . $num . '" ' . $selected . '>' . get_string('showperpage', 'local_jinoboard', $num) . '</option>';
                }
                ?>
            </select>
            <input type="text" title="search" name="search" value="<?php echo $search; ?>" class="search-text" placeholder="<?php echo get_string('input', 'local_jinoboard'); ?>">
            <input type="submit" class="search_btn" id="search" value="<?php echo get_string('search', 'local_jinoboard'); ?>">
        </form>

        <!-- 검색폼 종료 -->
        <form id="delete_form" action="deletes.php" method="POST">
        <table cellspacing="0" cellpadding="0">
            <caption class="hidden-caption">콘텐츠 리스트</caption>
            <thead>
            <tr>
                <th scope="row" style="width:5%;"><?php echo get_string('number', 'local_lmsdata'); ?></th>
                <th scope="row" style="width:30%;"><?php echo get_string('contents_event', 'local_lmsdata'); ?></th>
                <th scope="row"><?php echo get_string('contents_contentname', 'local_lmsdata'); ?></th>
                <th scope="row" style="width:15%;"><?php echo get_string('contents_generator', 'local_lmsdata'); ?></th>
                <th scope="row" style="width:10%;"><?php echo get_string('contents_generationtime', 'local_lmsdata'); ?></th>
            </tr>
            </thead>
            <?php
            $sql = 'select h.id,h.event,h.userid,h.contentid,h.timecreated,h.type,lc.con_name from {lcms_history} h '
                    . 'left join {lcms_contents} lc on lc.id = h.contentid '.$like . $where 
                    .' order by timecreated desc';
            $offset = ($page - 1) * $perpage;
            $datas = $DB->get_records_sql($sql,$param,$offset,$perpage);
            $csql = 'select count(h.id) from {lcms_history} h '
                    . 'left join {lcms_contents} lc on lc.id = h.contentid '.$like . $where;
            $total_count = $DB->count_records_sql($csql,$param);
            $num = $total_count - $offset;
            foreach ($datas as $data) {
                $user = $DB->get_record('user',array('id'=>$data->userid));
                ?>
                <tr>
                    <td style="width:5%;"><?php echo $num--; ?></td>
                     <td style="width:10%;"><?php echo $data->event; ?></td>
                     <td style="text-align: left;"><?php echo (!empty($data->con_name))?$data->con_name:'삭제된 콘텐츠입니다.'; ?></td>
                       <td style="width:10%;"><?php echo fullname($user); ?></td>
                        <td style="width:10%;"><?php echo date('Y-m-d h:i:s',$data->timecreated); ?></td>
                </tr>   
     <?php
            }
            if(empty($total_count)){
                ?>
                <tr>
                    <td colspan="5"><?php echo get_string('nocontents', 'local_repository'); ?></td>
                </tr>
                <?php
            }
            ?>
        </table>
            
        </form>
        <?php
        $page_params = array();
        $page_params['perpage'] = $perpage;
        $page_params['search'] = $search;
        $total_pages = ceil($total_count / $perpage);
        print_paging_navbar_notice("history.php", $page_params, $total_pages, $page);
        ?>
        <!-- Breadcrumbs End -->
    </div> <!-- Table Footer Area End -->
</div>
</div>


<?php include_once('../inc/footer.php'); ?>
