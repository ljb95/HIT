<?php

require(dirname(dirname(dirname(__FILE__))) . '/config.php');

$id     = optional_param('id', 0, PARAM_INT);

$id = $DB->delete_records('lmsdata_certificate',array('id'=>$id));

redirect('certi.php');