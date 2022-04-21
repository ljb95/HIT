<?php

require(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once 'config.php';


$context = context_system::instance();
require_login();
$PAGE->set_context($context);

?>
<div><span><?php echo get_string('folder','local_repository')?></span><input type="text" size="30" id="forder_add" name="con_name"></div>