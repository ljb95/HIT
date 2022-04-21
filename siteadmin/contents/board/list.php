<?php
require(dirname(dirname(dirname(__FILE__))) . '/config.php');
                    
// Check for valid admin user - no guest autologin
require_login(0, false);
if (isguestuser()) {
    $SESSION->wantsurl = (string) new moodle_url('/siteadmin/board/list.php');
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
 include_once('../inc/header.php'); ?>
<script>
    function change_status(id, status) {
        $.ajax({
            url: "./ajax/change_status.php",
            type: "post",
            data: {
                id: id, status: status
            },
            async: false,
            success: function (data) {
                $('#status' + id).html(data);
            },
            error: function (e) {
                console.log(e.responseText);
            }
        });
    }
</script>
<div id="contents">
    <?php include_once('../inc/sidebar_board.php'); ?>
    <div id="content">
        <h3 class="page_title"><?php echo get_string('board_list', 'local_lmsdata'); ?></h3>
        <div class="page_navbar"> <a href="./notices.php"><?php echo get_string('siteadmin_boardmanagement', 'local_lmsdata'); ?></a> > <?php echo get_string('board_list', 'local_lmsdata'); ?></div>

        <!-- 검색폼 시작 -->

        <form id="frm_notices_search" class="search_area">
            <input type="hidden" name="type" value="<?php echo $type; ?>">
            <select name="perpage" class="w_160" onchange="this.form.submit();">
                <?php
                $nums = array(10, 20, 30, 50);
                foreach ($nums as $num) {
                    $selected = ($num == $perpage) ? 'selected' : '';

                    echo '<option value="' . $num . '" ' . $selected . '>' . get_string('showperpage', 'local_jinoboard', $num) . '</option>';
                }
                ?>
            </select>
            <input type="text" name="search" value="<?php echo $search; ?>" class="search-text" placeholder="<?php echo get_string('input', 'local_jinoboard'); ?>">
            <input type="submit" class="search_btn" id="search" value="<?php echo get_string('search', 'local_jinoboard'); ?>">
        </form>

        <!-- 검색폼 종료 -->

        <table cellspacing="0" cellpadding="0">
            <tr>
                <th style="width:5%;"><?php echo get_string('number', 'local_lmsdata'); ?></th>
                <th><?php echo get_string('name_koen', 'local_lmsdata'); ?></th>
                <th style="width:10%;"><?php echo get_string('update_date', 'local_lmsdata'); ?></th>
                <th style="width:10%;"><?php echo get_string('update_user', 'local_lmsdata'); ?></th>
                <th style="width:10%;"><?php echo get_string('contents_count', 'local_lmsdata'); ?></th>
                <th style="width:10%;"><?php echo get_string('siteadmin_act', 'local_lmsdata'); ?></th>
                <th style="width:10%;"><?php echo get_string('siteadmin_option', 'local_lmsdata'); ?></th>
                <th style="width:10%;"><?php echo get_string('siteadmin_management', 'local_lmsdata'); ?></th>
            </tr>
            <?php
            $datas = $DB->get_records('jinoboard');
            $cnt = 1;
            foreach ($datas as $data) {
                switch ($data->status) {
                    case 1:$status = '<span class="pointer" onclick="change_status(' . $data->id . ',2);">'. get_string('siteadmin_act', 'local_lmsdata') . '</span>';
                        break;
                    case 2:$status = '<span class="pointer" onclick="change_status(' . $data->id . ',1);">'. get_string('siteadmin_noact', 'local_lmsdata') .'</span>';
                        break;
                    case 3:$status =  get_string('siteadmin_necessary', 'local_lmsdata'); 
                        break;
                }
                ?>
                <tr>
                    <td><?php echo $cnt++; ?></td>
                    <td style="text-align:left;"><a target="_blank" href="<?php echo $CFG->wwwroot; ?>/local/jinoboard/list.php?id=<?php echo $data->id; ?>" alt="바로가기" title="바로가기"><?php echo $data->name; ?>/<?php echo $data->engname; ?></a></td>
                    <td><?php echo date('Y-m-d', $data->timemodified); ?></td>
                    <td><?php echo fullname($DB->get_record('user', array('id' => $data->userid))); ?></td>
                    <td><?php echo $DB->count_records('jinoboard_contents',array('board'=>$data->id)); ?> </td>
                    <td id="status<?php echo $data->id; ?>"><?php echo $status; ?></td>
                    <td><a href="modify.php?id=<?php echo $data->id; ?>"><?php echo get_string('siteadmin_option', 'local_lmsdata'); ?></a></td>
                    <td><?php  if($data->required == '1'){ echo get_string('siteadmin_necessary', 'local_lmsdata'); } else { ?><a href="#" onclick="if(confirm('삭제하시겠습니까?')){ location.href='delete.php?id=<?php echo $data->id; ?>';  }"><?php echo get_string('delete', 'local_lmsdata'); ?></a><?php } ?></td>
                </tr>
                <?php
            }
            if (!$datas) {
                ?>
                <tr>
                    <td colspan="8">등록된 게시판이 없습니다.</td>
                </tr>
                <?php
            }
            ?>
        </table>

        <div class="btn_area">
            <input type="button" value="<?php echo get_string('board_regist', 'local_lmsdata'); ?>" onclick="location.href = 'add.php'" class="blue_btn" style="float:right;"/>
        </div>    
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
                $(".check_notice").each(function () {
                    this.checked = true;
                });
            } else {
                $(".check_notice").each(function () {
                    this.checked = false;
                });
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