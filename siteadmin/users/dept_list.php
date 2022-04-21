<?php
require (dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once $CFG->dirroot . '/siteadmin/manage/synclib.php';

$searchstring = optional_param('value', '', PARAM_RAW);

$context = context_system::instance();
$PAGE->set_context($context);

$where = '';
if($searchstring){
    $where = "where DEPT_CD like '%".$searchstring."%' or DEPT_NM like '%".$searchstring."%'";
}
 
$CONN = siteadmin_sync_db_connect();  

$sql_haksa = 'select DEPT_CD , DEPT_NM from DHDB.dbo.COT_DEPT '.$where;
$rs = odbc_prepare($CONN, $sql_haksa);
// article_text가 잘려나오지 않도록 1M로 설정 
odbc_longreadlen($rs, 1048576); 
$success = odbc_execute($rs);

?>
<div class="popup_content" id="search_dept"> 
    <form id="frm_dept_search" class="search_area" onsubmit="search_dept_search(); return false;" method="POST">
        <input type="text" name="value" value="<?php echo $searchstring; ?>" class="w_300" placeholder="부서코드,부서명 검색"/>   
        <input type="submit" class="blue_btn" id="search" value="<?php echo get_string('search', 'local_lmsdata'); ?>"/>
    </form>

    <form id="frm_course_certificate" name="frm_course_certificate" onsubmit="return false;">
        <table cellpadding="0" cellspacing="0">
            <tbody>
                <tr>
                    <th>부서코드</th>
                    <th>부서명</th>
                    <th><?php echo get_string('add', 'local_lmsdata'); ?></th>
                </tr>
                <?php
                 while ($row = odbc_fetch_array($rs)) {
                        echo '<tr>';
                        echo '<td>' . $row['DEPT_CD'] . '</td>';
                        echo '<td>' . $row['DEPT_NM'] . '</td>';
                        echo '<td><input type="button" value="' . get_string('add', 'local_lmsdata') . '" class="orange_btn" onclick="search_dept_select(\'' . $row['DEPT_CD'] . '\', \'' . $row['DEPT_NM'] . '\');"/></td>';
                        echo '</tr>';
                    }
                ?>
            </tbody>
        </table>
    </form>
</div>

<script type="text/javascript">
    function search_dept_search() {
        var searchstring = $("#frm_dept_search input[name=value]").val();
        $.ajax({
            url: '<?php echo $CFG->wwwroot . '/siteadmin/users/dept_list.php'; ?>',
            method: 'POST',
            data: {
                'value': searchstring
            },
            success: function (data) {
                $("#search_dept").parent().html(data);
            },
            error: function (jqXHR, textStatus, errorThrown) {
            }
        });
    }
    function search_dept_select(code, name) {
        $("input[name=dept_code]").attr("value", code);
        $("input[name=dept_name]").attr("value", name);
        $("#dept_popup").dialog("close");
    }
</script> 