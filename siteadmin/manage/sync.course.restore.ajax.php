<?php

require(dirname(dirname(dirname(__FILE__))) . '/config.php');

if(!is_siteadmin($USER)){
    echo '권한이 없습니다.';
    die;
}

$classids    = optional_param_array('id', array(), PARAM_INT);

$deleted_classes = array();
foreach($classids as $classid) {
    if($class = $DB->get_record('haksa_class', array('id'=>$classid))) {
        $class->deleted = 0;
        $DB->update_record('haksa_class', $class);
        
        $deleted_classes[] = '<li>'.$class->kor_lec_name.' (대학코드:'.$class->domain.', 강의코드:'.$class->hakno.', 분반:'.$class->bb.', 과목코드:'.$class->ohakkwa.')</li>';
    }
}

echo '<h4>'.count($deleted_classes).' 개의 강의를 복구했습니다.</h4><br/>
    <ul>'.implode('<br/>', $deleted_classes).'</ul>';
