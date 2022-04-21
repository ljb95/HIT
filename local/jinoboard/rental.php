<?php

require(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once $CFG->dirroot . '/local/jinoboard/lib.php';


$id = optional_param('id', 0, PARAM_INT);				// content ID
$board = optional_param('board', 0, PARAM_INT);
$status = optional_param('status', 0, PARAM_INT);
$perpage = optional_param('perpage', 10, PARAM_INT);
$search = optional_param('search', '', PARAM_RAW);
$list_num = optional_param('list_num', 1, PARAM_INT);

$content = $DB->get_record('jinoboard_contents',array('id'=>$id));

$data = new stdClass();
$data->id = $id;
$data->status = $status;


	$DB->update_record('jinoboard_contents', $data);

redirect(new moodle_url('/local/jinoboard/detail_uncore.php', array('id' => $id,'search'=>$search,'perpage'=>$perpage,'list_num'=>$list_num,'board'=>$board)));
