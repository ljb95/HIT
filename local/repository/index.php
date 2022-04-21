<?php
require_once("../../config.php");
require_once("./lib.php");
require_once($CFG->dirroot . "/lib/coursecatlib.php");

$PAGE->set_url('/local/repository/index.php');
$PAGE->set_pagelayout('standard');

$page = optional_param('page', 1, PARAM_INT);
$perpage = optional_param('perpage', 20, PARAM_INT);
$search = optional_param('search', '', PARAM_RAW);
$groupid = optional_param('groupid', 0, PARAM_INT);
$userid = optional_param('userid', 0, PARAM_INT);
$shared = optional_param('shared', '', PARAM_RAW);

$context = context_system::instance();
$PAGE->set_context($context);

$agent = get_browser(null, true);

    if($agent['device_type']!='Desktop'){
        echo '<script>alert("해당 메뉴는 PC에서만 사용 가능합니다");location.href="/my/index.php";</script>';
    }

$strplural = get_string("course_management", "local_repository");
$PAGE->navbar->add(get_string("mypage", "local_courselist"), new moodle_url($CFG->wwwroot.'/local/courselist/course_manage.php'));
$PAGE->navbar->add($strplural);
$PAGE->set_title($strplural);
$PAGE->set_heading($strplural);

require_login();

$role = $DB->get_field('lmsdata_user','usergroup', array('userid' => $USER->id));
if (is_siteadmin()) {
    $role = 'ma';
}

if($role == 'rs'){
    redirect($CFG->wwwroot, 'Permission Denied');
}

// Print the header
$PAGE->requires->jquery(); 
$PAGE->requires->jquery_plugin('ui');
$PAGE->requires->jquery_plugin('ui-css');

echo $OUTPUT->header();
?>
<h3 class="tab_title"></h3>
<form class="table-search-option" method="get">
    <input type="hidden" name="page" value="1">
    <input type="hidden" name="perpage" value="<?php echo $perpage; ?>">
    <label for="search" class="hidden-label">search</label>
    <input type="text" name="search" id="search" value="<?php echo $search; ?>"  placeholder="<?php echo get_string('search', 'local_repository'); ?>">
    <input class="btn_st01" type="submit" class="board-search" value="<?php echo get_string('search', 'local_repository'); ?>" onclick='' />
</form> <!-- Search Area End -->

<div class="table-header-area">
    <form method="get">
        <input type="hidden" name="page" value="1">
        <input type="hidden" name="search" value="<?php echo $search; ?>">
        <?php if (!is_siteadmin()) { ?>
            <label for="shared" class="hidden-label">shared</label>
            <select name="shared" title="공유" id="shared" onchange="this.form.submit();">
                <option <?php if ($shared == '') echo 'selected'; ?> value="">전체</option>
                <option <?php if ($shared == 'N') echo 'selected'; ?> value="N">나의 파일</option>
                <option <?php if ($shared == 'Y') echo 'selected'; ?> value="Y">공유된 파일</option>
            </select>
        <?php } ?>
        <label for="perpage" class="hidden-label">perpage</label>
        <select name="perpage" id="perpage" onchange="this.form.submit();">
            <?php
            $nums = array(10, 20, 30, 50);
            foreach ($nums as $num) {
                $selected = ($num == $perpage) ? 'selected' : '';

                echo '<option value="' . $num . '" ' . $selected . '>' . get_string('showperpage', 'local_jinoboard', $num) . '</option>';
            }
            ?>
        </select>
        <label for="groupid" class="hidden-label">groupid</label>
        <select name="groupid" id="groupid" onchange="this.form.submit();">
            <option><?php echo get_string('all', 'local_repository'); ?></option>
            <?php
            $groups = $DB->get_records("lcms_repository_groups", array('userid' => $USER->id));
            foreach ($groups as $group) {
                ?>
                <option <?php if ($groupid == $group->id) echo "selected"; ?> value="<?php echo $group->id; ?>"><?php echo $group->name; ?></option>
            <?php } ?>
            ?>
        </select>
    </form>
</div>
<div class="table-filter-area">
<!--        <?php if (is_siteadmin()) { ?> 
    <button class="btn_st01" type="button" onclick="location.href = 'cdms.php'">CDMS</button>
    <?php } ?>
-->
    <button class="btn_st01" onclick="location.href = 'write.php'" style="cursor:pointer;"><?php echo get_string('contentsregister', 'local_repository'); ?></button>
</div>
<form id="repository_form" method="post">
    <table cellpadding="0" cellspacing="0" class="generaltable">
        <caption class="hidden-caption">resourses</caption>
        <thead>
            <tr>
                <th scope="row" width="4%" class="mobile"><label for="all_check" class="hidden-label">check</label><input type="checkbox" id="all_check"></th>
                <th scope="row" width="6%" class="mobile"><?php echo get_string('list:no', 'local_repository'); ?></th>
                <th scope="row" width="15%" class="mobile"><?php echo get_string('list:groupname', 'local_repository'); ?></th>
                <th scope="row"><?php echo get_string('list:title', 'local_repository'); ?></th>
                <th scope="row" width="15%"><?php echo get_string('list:type', 'local_repository'); ?></th>
                <th scope="row" width="10%" class="mobile"><?php echo get_string('list:reference', 'local_repository'); ?></th>
                <th scope="row" width="10%" class="mobile">좋아요</th>
                <th scope="row" width="10%" class="mobile"><?php echo get_string('list:isopen', 'local_repository'); ?></th>
                <th scope="row" width="10%" class="mobile"><?php echo get_string('list:timecreated', 'local_repository'); ?></th>
                <th scope="row" width="8%" class="mobile"><?php echo get_string('update_user', 'local_lmsdata'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php
            $like = '';
            if (!empty($search)) {
                $like .= " and " . $DB->sql_like('con.con_name', ':search', false);
            }
            $group_like = "";
            if ($groupid) {
                $group_like .= " and " . $DB->sql_like('groups.id', ':groupid', false);
            }
            if (is_siteadmin()) {
                $userwhere = "";
                if ($userid != 0) {
                    $userwhere = "and rep.userid = :userid";
                }
                $sql = "select "
                        . "rep.id , rep.referencecnt, "
                        . "groups.name as gname, "
                        . "con.id as conid , con.share_yn,con.con_type, con.con_name, con.update_dt, con.teacher "
                        . "from {lcms_repository} rep "
                        . "left join {lcms_repository_groups} groups on groups.id = rep.groupid "
                        . "join {lcms_contents} con on con.id = rep.lcmsid " . $like
                        . " where con.con_type!=:type " . $userwhere . " " . $group_like . " order by con.update_dt desc";
                $count_sql = "select "
                        . "count(rep.id) "
                        . "from {lcms_repository} rep "
                        . "left join {lcms_repository_groups} groups on groups.id = rep.groupid "
                        . "join {lcms_contents} con on con.id = rep.lcmsid " . $like
                        . " where con.con_type!=:type " . $userwhere . " " . $group_like;
            } else {
                $luwhere = "";
                if ($shared == 'N') {
                    $luwhere = " and con.course_cd = :luid and con.share_yn = 'N'";
                } else if ($shared == 'Y') {
                    $luwhere = " and con.share_yn = 'Y'";
                } else {
                    $luwhere = " and (con.course_cd = :luid or con.share_yn = 'Y')";
                }

                $sql = "select "
                        . "rep.id , rep.referencecnt, "
                        . "groups.name as gname, "
                        . "con.id as conid , con.share_yn,con.con_type, con.con_name, con.update_dt, con.teacher "
                        . "from {lcms_repository} rep "
                        . "left join {lcms_repository_groups} groups on groups.id = rep.groupid "
                        . "join {lcms_contents} con on con.id = rep.lcmsid " . $luwhere . $like
                        . " where con.con_type!=:type and con.user_no=:teacher" . $group_like . " order by con.update_dt desc";

                $count_sql = "select "
                        . "count(rep.id) "
                        . "from {lcms_repository} rep "
                        . "left join {lcms_repository_groups} groups on groups.id = rep.groupid "
                        . "join {lcms_contents} con on con.id = rep.lcmsid " . $luwhere . $like
                        . " where con.con_type!=:type and con.teacher=:teacher" . $group_like . " order by con.update_dt desc";
            }
            $totalcount = $DB->count_records_sql($count_sql, array('luid' => $USER->id, 'search' => '%' . $search . '%', 'type' => 'ref', 'groupid' => $groupid, 'userid' => $userid, 'teacher' => $USER->id));
            $total_pages = repository_get_total_pages($totalcount, $perpage);

            $offset = 0;
            if ($page != 0) {
                $offset = ($page - 1) * $perpage;
            }

            $files = $DB->get_records_sql($sql, array('luid' => $USER->id, 'search' => '%' . $search . '%', 'type' => 'ref', 'groupid' => $groupid, 'userid' => $userid, 'teacher' => $USER->id), $offset, $perpage);
            $num = $totalcount - (($page - 1) * $perpage);
            foreach ($files as $file) {
                if (!$file->gname)
                    $file->gname = '-';
                if ($file->con_type == 'word')
                    $type_txt = get_string('document', 'local_repository');
                elseif ($file->con_type == 'html')
                    $type_txt = get_string('html', 'local_repository');
                elseif ($file->con_type == 'html2')
                    $type_txt = get_string('html', 'local_repository');
                elseif ($file->con_type == 'video')
                    $type_txt = get_string('video', 'local_repository');
                elseif ($file->con_type == 'embed')
                    $type_txt = get_string('embed', 'local_repository');
                elseif ($file->con_type == 'media')
                    $type_txt = get_string('mid', 'local_repository');
                ?>
                <tr>
                    <td scope="col" class="mobile"><input type="checkbox" class="template_checked" value="<?php echo $file->id; ?>" name="check<?php echo $file->id; ?>" title="check<?php echo $file->id; ?>"></td>
                    <td scope="col" class="mobile"><?php echo $num--; ?></td>
                    <td scope="col" class="mobile"><?php echo $file->gname; ?></td>
                    <td scope="col" class="title"><a href="detail.php?id=<?php echo $file->id ?>&userid=<?php echo $userid; ?>"><?php echo $file->con_name; ?></a></td>
                    <td scope="col"><?php echo $type_txt; ?></td>
                    <td scope="col" class="mobile"><?php echo $file->referencecnt; ?></td>
                     <td scope="col" class="mobile"><?php echo $file->referencecnt; ?></td>
                    <?php
                    $public = ($file->share_yn == 'N') ? get_string('n', 'local_repository') : get_string('y', 'local_repository');
                    ?> 
                    <td scope="col" class="mobile"><?php echo $public; ?></td>
                    <td scope="col" class="mobile number"><?php echo date('Y.m.d', $file->update_dt) ?></td>
                    <td scope="col" class="mobile"><?php echo $file->teacher; ?></td>
                </tr>
                <?php
            }

            if (!$files) {
                echo '<tr><td colspan="9">' . get_string('nocontents', 'local_repository') . '</td></tr>';
            }
            ?>
        </tbody>
    </table>
    
    <div class="mobile table-footer-area" style="margin:5px 0;">
        <div class="btn-area btn-area-right" style="width:100%">
        <label for="group_manage" class="hidden-label">group manage</label>
        <input class="btn_st01" type="button" id="group_manage" style="cursor:pointer;" value="<?php echo get_string('groupmanage', 'local_repository'); ?>">
        <label for="change_group_select" class="hidden-label">change group select</label>
        <select name="chang_groups" id="change_group_select">
            <?php
            if(!is_siteadmin()){
                $groups = $DB->get_records("lcms_repository_groups", array('userid' => $USER->id));
            } else {
                $groups = $DB->get_records("lcms_repository_groups");
            }
            if(!$groups){
                echo '<option value="">등록된 그룹이 없습니다.</option>';
            }
            foreach ($groups as $group) {
                ?>
                <option value="<?php echo $group->id; ?>"><?php echo $group->name; ?></option>
            <?php } ?>
        </select>
        <button class="btn_st01" id="group_change_submit" style="cursor:pointer;"><?php echo get_string('selectgroupjump', 'local_repository'); ?></button>
        </div>
        <?php
        $page_params = array();
        $page_params['perpage'] = $perpage;
        $page_params['search'] = $search;
        $page_params['groupid'] = $groupid;
        $page_params['userid'] = $userid;
        repository_get_paging_bar($CFG->wwwroot . "/local/repository/index.php", $page_params, $total_pages, $page);
        ?>
        <!-- Breadcrumbs End -->
    </div> <!-- Table Footer Area End -->
</form>

<div class="group_control_popup" id="group_control_popup" title="<?php echo get_string('groupmanage', 'local_repository'); ?>">
    <input type="text" title="group name" id="group_name" class="w-50" placeholder="<?php echo get_string('list:groupname', 'local_repository'); ?>" />
        <input type="button" id="create_group" value="<?php echo get_string('groupregister', 'local_repository'); ?>" />
        <div id="groups_table"></div>
</div>
  <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
<script type="text/javascript">
    /// 그룹 관리창 dialog
   
        $("#group_manage").click(function () {
            $("#group_control_popup").dialog({
            autoOpen: false,
            modal: true,
            width: '700px',
            height: 'auto',
            close: function () {
                location.reload();
            }
            }).dialog("open");
            $.ajax({
                url: "ajax/get_groups.php",
                cache: false
            })
                    .done(function (html) {
                        $("#groups_table").html("");
                        $("#groups_table").append(html);
                    });
        });
    // 그룹 생성 버튼 작동
    $("#create_group").click(function () {
        var groupname = $("#group_name").val();
        if (groupname) {
            $.ajax({
                url: "ajax/insert_group.php",
                type: "POST",
                data: {gname: groupname},
            })
                    .done(function (html) {
                        $("#groups_table .generaltable tbody").append(html);
                        $("#group_name").val("");
                    });
        } else {
            $("#group_name").focus();
            alert("<?php echo get_string('insert:groupname', 'local_repository') ?>");
        }
    })
    // 명칭변경 버튼 작동
    group_modify = function (id) {
        var value = $('.group_' + id).html();
        $('td.group_' + id).html("<input type='text' id='groupname_input" + id + "' value='" + value + "'><button onclick='modify_complate(" + id + ")'>수정</button>");
        $('#modifybtn_' + id).attr('disabled', 'disabled');
    }
    // 삭제 버튼 작동
    group_delete = function (id) {
        var conf = confirm('<?php echo get_string('delete_group', 'local_repository'); ?>');
        if (conf) {
            $.ajax({
                url: "ajax/delete_group.php",
                type: "POST",
                data: {id: id}
            })
                    .done(function () {
                        $('#table_row' + id).hide();
                    });
        }
    }
    //명칭변경 변경 완료
    modify_complate = function (id) {
        var groupname = $("#groupname_input" + id).val();
        if (groupname) {
            $.ajax({
                url: "ajax/modify_group.php",
                type: "POST",
                data: {gname: groupname, id: id},
            })
                    .done(function () {
                        $('.group_' + id).html(groupname);
                        $('#modifybtn_' + id).removeAttr('disabled');
                    });
        } else {
            $("#groupname_input" + id).focus();
            alert("<?php echo get_string('insert:groupname', 'local_repository') ?>");
        }
    }
    $('#all_check').click(function () {
        $('.template_checked').prop('checked', this.checked);
    });
    $('#group_change_submit').click(function () {
        var gid = $('#change_group_select').val();
        var chk = $('.template_checked:checked').length;
        if (gid == 0) {
            alert('<?php echo get_string('select_group', 'local_repository'); ?>');
        } else if (chk == 0) {
            alert('<?php echo get_string('select_content', 'local_repository'); ?>');
        } else {
            var chk = confirm('<?php echo get_string('change_group', 'local_repository'); ?>');
            if (chk) {
                $('#repository_form').attr('action', 'change_groups.php?gid=' + gid);
                $('#repository_form').submit();
            }
        }
    });
    $('#group_delete_submit').click(function () {
        $('#repository_form').attr('action', 'delete_contents.php');
        $('#repository_form').submit();
    });
</script>
<style>
    table {
        float: left;
    }
</style>
<?php
echo $OUTPUT->footer();
?>







