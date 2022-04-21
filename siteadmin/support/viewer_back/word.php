<?php
global $CFG, $con, $scan;

?>

<div style="margin-top:10px;">
    <h2 style="margin-bottom:10px;">강의 정보</h2>
    <p style="margin-bottom:5px;"><strong><?php echo get_string('course_name', 'local_lmsdata'); ?> :</strong> <?php echo $con->con_name; ?>&nbsp;&nbsp;&nbsp;&nbsp;<strong><?php echo get_string('contents_lecturer', 'local_lmsdata'); ?> :</strong> <?php echo $con->teacher; ?></p>
    <p class="ocw_class_description"><?php echo $con->con_des; ?></p>
</div>
<ul>
<?php

if (sizeof($scan) <= 1)echo '등록된 파일이 없습니다.';

$service_path_dir = $scan['path'];
$spath = $scan['spath'];

foreach ($scan['file'] as $file) {
        $filedir = $service_path_dir . '/';
        echo '<li><a href="./lib/download.php?filedir='.urlencode($filedir).'&filename='.$file.'" target="_blank">'.$file.'</a></li>';
}

?>
</ul>


