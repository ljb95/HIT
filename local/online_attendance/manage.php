<?php

$page = optional_param('page', 1, PARAM_INT);
$perpage = optional_param('perpage', 10, PARAM_INT);
$search = optional_param('search', '', PARAM_RAW);

//등록된 학생 목록
$fullname = $DB->sql_fullname('ur.firstname', 'ur.lastname');
$sql_select = "SELECT  ur.id,
                       ur.username, ".$DB->sql_fullname('ur.firstname', 'ur.lastname')." AS fullname ";
$sql_from = " FROM {context} co 
              JOIN {role_assignments} ra ON ra.contextid = co.id and roleid = :roleid
              JOIN {user} ur ON ur.id = ra.userid ";

$sql_conditions[] = ' co.id = :contextid ';
$sql_conditions[] = ' ur.deleted = :deleted ';

$params['contextid'] = $context->id;
$params['roleid'] = $DB->get_field('role', 'id', array('archetype' => 'student'));
$params['deleted'] = 0;

//검색어
if (!empty($search)) {
    $like_fullname = $DB->sql_like($fullname, ':fullname');
    $like_name = $DB->sql_like('ur.username', ':username');
    $sql_conditions[] = '(' . $like_fullname . ' or ' . $like_name . ')';
    $params['fullname'] = '%' . $search . '%';
    $params['username'] = '%' . $search . '%';
}

$sql_where = ' WHERE '.implode(' AND ', $sql_conditions);
$sql_order_by = ' ORDER BY loc.section, loc.cmid ASC ';

$totalcount = $DB->count_records_sql('SELECT COUNT(*) '.$sql_from.$sql_where, $params);
$users = $DB->get_records_sql($sql_select.$sql_from.$sql_where.$sql_orderby, $params, ($page-1)*$perpage, $perpage);

// user activity 별 출석 데이터
if(!empty($users)){
    $userlist = array();
    foreach($users as $user) {
        $userlist[] = $user->id;
    }
}
$userdata = local_onattendance_get_status($id, $userlist);
foreach($userdata as $data) {
    $attend[$data->userid][$data->cmid] = $data;
}

// 활성화 activity 목록
$mods = local_onattendance_get_cmset($id, $visible);
$totaltr = 3;
foreach($mods as $mod) {
    $modtitle = $DB->get_field_sql('SELECT name FROM {'.$mod->modname.'} WHERE id = :id', array('id'=>$mod->instance));
    if(isset($scount[$mod->section])) {
        $scount[$mod->section]++;
    }else {
        $scount[$mod->section] = 1;
    }
    $totaltr++;    
    $cmdata[$mod->cmid] = $modtitle;
} 

?>
<form id="form1" name="form_setup" class="table-search-option stat_form">
        <input type="hidden" name="id" value="<?php echo $id; ?>">
        <input type="hidden" name="type" value="<?php echo $type; ?>">
        <input type="hidden" name = "page" value="1">
        <input type="hidden" name = "perpage" value="<?php echo $perpage; ?>">
        <input type="text" name="search" placeholder="학번/이름을 입력하세요" title="이름입력" class="search_name" value='<?php echo $search;?>' />
        <input type="submit" value="검색" />
    </form>
<div class="buttons">
    <div class="left">
        <select class="perpage" name="perpage" title="페이지" onchange="change_perpage(this.options[this.selectedIndex].value, 'form_setup');">
            <?php
            $nums = array(5,10,15,20);
            foreach ($nums as $num) {
                $selected = '';
                if($num == $perpage) {
                    $selected = ' selected';
                }
                echo '<option value="'.$num.'"'.$selected.'>'.get_string('showperpage', 'local_courselist', $num).'</option>';
            } ?>
        </select>
    </div>
    <div class="right">
        <input type="button" value="Excel 다운로드" class="btn" onclick="excel_export();"/>
    </div>
</div>
 <table class="generaltable">
        <thead>
            <tr>
                <th colspan="3">&nbsp;</th>
                <?php foreach($scount as $section => $count) {
                    echo '<th colspan="'.$count.'">'.$section.'주차</th>';
                
                }?>
            </tr>
            <tr>
                <th>번호</th>
                <th>이름</th>
                <th>학번</th>
                <?php foreach($cmdata as $cm) {
                    echo '<th>'.$cm.'</th>';
                }?>
            </tr>
        </thead>
        <tbody>
            <?php
                if($totalcount >0) {
                    $startnum = $totalcount - (($page - 1) * $perpage);
                    foreach($users as $user) {
                        echo '<tr>';
                        echo '<td>'.$startnum--.'</td>';
                        echo '<td>'.$user->fullname.'</td>';
                        echo '<td>'.$user->username.'</td>';
                        foreach($cmdata as $cmid => $modname) {
                            $attenddata = $attend[$user->id][$cmid];
                            echo '<td>'.$attenddata->aprogress.'%</td>';
                        }
                        echo '<tr>';
                    }
                }else {
                    echo '<tr><td colspan="'.$totaltr.'">데이터가 없습니다.</td></tr>';
                }
            ?>
        </tbody>
    </table>
<div class="table-footer-area">
    <?php
     onattendance_print_paging_navbar_script($totalcount, $page, $perpage, 'javascript:goto_page(:page, \'form_setup\');', 10);
     ?>
</div>
<?php
    echo $OUTPUT->footer();
?>
<script type="text/javascript">
    function excel_export(){
        $('form[name=form_setup]').attr('action', './manage_excel.php');
        $('form[name=form_setup]').attr('action');
        $('form[name=form_setup]').submit();
    }
    
</script>