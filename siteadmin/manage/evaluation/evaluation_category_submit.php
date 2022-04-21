<?php

require_once dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php';
require_once dirname(dirname(dirname(__FILE__))) . '/lib/paging.php';
require_once dirname(dirname(dirname(__FILE__))) . '/lib.php';

$mode = required_param('mode', PARAM_RAW);
$categoryid = optional_param('categoryid', '0', PARAM_INT);
$name = required_param('name', PARAM_RAW);
$formid = required_param('formid', PARAM_INT);
$sortorder = required_param('sortorder', PARAM_INT);



if ($mode == 'add') {
    $data = new stdClass();
    $data->name = $name;
    $data->formid = $formid;
    $data->sortorder = $sortorder;

    $orders = $DB->get_records('lmsdata_evaluation_category', array('formid' => $formid), 'sortorder asc', 'sortorder');
    $found = false;
    foreach ($orders as $key => $element) {
        if ($element->sortorder == $data->sortorder) {
            $found = true;
        }
    }

    if ($found) {
        redirect('./evaluation_category_add.php?formid=' . $formid,get_string('used_order','local_lmsdata'), 2);
    } else {
        $new_form = $DB->insert_record('lmsdata_evaluation_category', $data);
        redirect('./evaluation_categories.php?formid=' . $formid);
    }
} else if($mode == 'modify'){
    $category = $DB->get_record('lmsdata_evaluation_category', array('id' => $categoryid));
    
    $data = new stdClass();
    $data->id = $category->id;
    $data->name = $name;
    $data->sortorder = $sortorder;

    $orders = $DB->get_records('lmsdata_evaluation_category', array('formid' => $formid), 'sortorder asc', 'sortorder');
    $found = false;
    foreach ($orders as $key => $element) {
        if ($element->sortorder == $data->sortorder) {
            $found = true;
        }
    }
    if($category->sortorder == $data->sortorder)$found = false;
    if ($found) {
        redirect('./evaluation_category_modify.php?formid=' . $formid.'&categoryid='.$category->id, get_string('used_order','local_lmsdata'), 2);
    } else {
        $new_form = $DB->update_record('lmsdata_evaluation_category', $data);
        redirect('./evaluation_categories.php?formid=' . $formid);
    }
}