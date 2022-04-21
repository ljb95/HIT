<?php
require_once '../../lib/config.php';
global $CFG;
require_once $CFG->libdir.'/class/contents.php';
$DB = new CONTENTS;

$seq = $_REQUEST['id'];
$data = $DB->getHistory($seq);

?>

<!--콘텐츠이력-->
<table border="1" class="write_form">
    <caption>콘텐츠이력</caption>
    <tr>
        <th>변경날짜</th>
        <td id="update_dt"><?php echo $data['TABLE_NAME'];?></td>
    </tr>
    <tr>
        <th>테이블명</th>
        <td id="table_name"><?php echo $data['DT'];?></td>
    </tr>
    <tr>
        <th>변경형식</th>
        <td id="update_type"><?php echo $data['UPDATE_TYPE'];?></td>
    </tr>
    <tr>
        <th><?php echo get_string('contents_explanation', 'local_lmsdata'); ?></th>
        <td id="update_des"><?php echo $data['UPDATE_DES'];?></td>
    </tr>
    <tr>
        <th>상세데이터</th>
        <td id="update_data"><?php echo $data['UPDATE_DATA'];?></td>
    </tr>
</table>

