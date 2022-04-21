<?php
require(dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php');

$id = required_param('id', PARAM_INT);

$sql = "select l.*,cm.id as cmid ,c.fullname,u.firstname,u.lastname,c.id as courseid from {lcms} l "
        . "join {course_modules} cm on cm.instance = l.id "
        . "join {course} c on c.id = cm.course "
        ." join {lmsdata_class} lc on lc.course = c.id "
        . "join {user} u on u.id = lc.prof_userid " 
        . "join {modules} m on m.name = 'lcms' and m.id = cm.module "
        . "where l.contents = :id";
$datas = $DB->get_records_sql($sql,array('id'=>$id));

?>
<table>
    <tr>
        <th>#</th>
        <th>강의명</th>
        <th>교수명</th>
        <th>바로가기</th>
    </tr>
    <?php 
    $num = 1;
    foreach($datas as $data){
    ?>
    <tr>
        <td><?php echo $num++; ?></td>
        <td><?php echo $data->fullname; ?></td>
        <td><?php if(current_language() == 'ko'){ echo $data->firstname; } else { echo $data->lastname; } ?></td>
        <td><a href="<?php $CFG->wwwroot; ?>/mod/lcms.php?id=<?php echo $data->cmid; ?>"></a></td>
    </tr>
    <?php } ?>
</table>