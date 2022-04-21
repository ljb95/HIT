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

$groupid = optional_param('groupid', 0, PARAM_INT);

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
if (!empty($search)) {
    $like = "and " . $DB->sql_like('title', ':search', false);
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
        <h3 class="page_title"><?php echo get_string('contents_contentslist', 'local_lmsdata'); ?></h3>
        <div class="page_navbar"> <a href="<?php echo $CFG->wwwroot . '/siteadmin/contents/index.php'; ?>"><?php echo get_string('lcms_management', 'local_lmsdata'); ?></a> > <a href="#"><?php echo get_string('contents_contentslist', 'local_lmsdata'); ?></a></div>

        <!-- 검색폼 시작 -->

        <form id="frm_notices_search" class="search_area">
            <input type="hidden" name="type" value="<?php echo $type; ?>">
            <select name="perpage" title="page" class="w_160" onchange="this.form.submit();">
                <?php
                $nums = array(10, 20, 30, 50);
                foreach ($nums as $num) {
                    $selected = ($num == $perpage) ? 'selected' : '';

                    echo '<option value="' . $num . '" ' . $selected . '>' . get_string('showperpage', 'local_jinoboard', $num) . '</option>';
                }
                ?>
            </select>
            <select name="target" title="target" class="w_160">
                <option value=""><?php echo get_string('contents_all', 'local_lmsdata'); ?></option>
                <?php
                $areas = $DB->get_records('lcms_clas_area');

                foreach ($areas as $key => $val) {
                    echo '<option value="' . $key . '">' . $val . '</option>';
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
            <tr>
                <th scope="row" style="width:5%;"><input type="checkbox" title="check" id="allcheck" style="margin: 0 !important;"/></th>
                <th scope="row" style="width:5%;"><?php echo get_string('number', 'local_lmsdata'); ?></th>
                <th scope="row" style="width:5%;"><?php echo get_string('contents_groupname', 'local_lmsdata'); ?></th>
                <th scope="row"><?php echo get_string('title', 'local_lmsdata'); ?></th>
                <th scope="row" style="width:15%;"><?php echo get_string('contents_kinds', 'local_lmsdata'); ?></th>
                <th scope="row" style="width:10%;"><?php echo get_string('contents_references', 'local_lmsdata'); ?></th>
                <th scope="row" style="width:10%;"><?php echo get_string('contents_visibility', 'local_lmsdata'); ?></th>
                <th scope="row" style="width:10%;"><?php echo get_string('update_date', 'local_lmsdata'); ?></th>
            </tr>
            <?php
            $datas = get_contents($userid, $groupid, $search, $page, $perpage);
            foreach ($datas->files as $file) {
                if (!$file->gname) {
                    $file->gname = '-';
                }
                switch ($file->con_type) {
                    case "word" : $type_txt = get_string('document', 'local_repository');
                        break;
                    case "html" :
                    case "html2" : $type_txt = get_string('html', 'local_repository');
                        break;
                    case "video" : $type_txt = get_string('video', 'local_repository');
                        break;
                    case "embed" : $type_txt = get_string('embed', 'local_repository');
                        break;
                    default : $type_txt = "-"; break;
                }
                ?>
                <tr>
                    <td style="width:5%;"><input type="checkbox" title="check" name="check[<?php echo $file->conid; ?>]" class="check_delete" style="margin: 0 !important;"/></td>
                    <td style="width:5%;"><?php echo $datas->num--; ?></td>
                    <td style="width:5%;"><?php echo $file->gname; ?></td>
                    <td>
                        <a href="detail.php?id=<?php echo $file->id ?>&userid=<?php echo $userid; ?>">
    <?php echo $file->con_name; ?>
                        </a>
                    </td>
                    <td style="width:15%;"><?php  echo $type_txt; ?></td>
                    <td style="width:10%;"><?php echo $file->referencecnt; ?></td>
                    <td style="width:10%;">
                    <?php
                           $public = (!$file->share_yn || $file->share_yn=='N') ? get_string('n', 'local_repository') : get_string('y', 'local_repository'); 
                           echo $public; 
                    ?>
                    </td>
                    <td style="width:10%;"><?php echo date('Y.m.d', $file->update_dt); ?></td>
                </tr>   
                <?php
            }
            if(empty($datas->total_count)){
                ?>
                <tr>
                    <td colspan="8"><?php echo get_string('nocontents', 'local_repository'); ?></td>
                </tr>
                <?php
            }
            ?>
        </table>
            
        <div class="btn_area">
            <input type="button" value="<?php echo get_string('writepost', 'local_jinoboard') ?>" onclick="location.href = 'add.php?groupid=<?php echo $groupid; ?>'" class="blue_btn" style="float:right;"/>
            <input type="button" id="delete_notice" class="normal_btn" value="<?php echo get_string('delete', 'local_lmsdata'); ?>" style="float: left" />
        </div>    
        </form>
        <?php
        $page_params = array();
        $page_params['type'] = $groupid;
        $page_params['perpage'] = $perpage;
        $page_params['search'] = $search;
        print_paging_navbar_notice("index.php", $page_params, $datas->total_pages, $page);
        ?>
        <!-- Breadcrumbs End -->
    </div> <!-- Table Footer Area End -->
</div>
</div>

<script>
    $(function () {
        $("#accordion").accordion({
            collapsible: true,
            heightStyle: "content",
            header: "h3",
            active: false
        });
        $("#accordion").accordion("option", "icons", null);
    });
//	$('#accordion input[type="checkbox"]').click(function(e) {
//		e.stopPropagation();
//	});

    $(document).ready(function () {
        $('#allcheck').click(function () {
            if ($('#allcheck').is(":checked")) {
                $(".check_delete").each(function () {
                    this.checked = true;
                });
            } else {
                $(".check_delete").each(function () {
                    this.checked = false;
                });
            }
        });
        $('#delete_notice').click(function () {
            if (confirm("삭제 하시겠습니까? 선택된 콘텐츠와 연관된 모든 파일과 학습활동도 삭제됩니다.")) {
                $('#delete_form').submit();
            }
        });

        $('#search').click(function () {
            var searchfield = $('#searchfield').val();
            var searchval = $('#searchval').val();
            var timestart = $('#timestart').val();
            var timeend = $('#timeend').val();

            location.href = "./notice.php?searchfield=" + searchfield + "&searchvalue=" + searchval + "&timestart=" + timestart + "&timeend=" + timeend;
        });
    });
</script>
<?php include_once('../inc/footer.php'); ?>
