<?php

define('TEMPLATE_IDNUMBER', 'oklass_template');
define('SAMPLE_IDNUMBER', 'oklass_sample');
/*2014.04.23 최현수
 * 
 * @param array $course_list
 * @param array $param
 * @param int   $page
 * @param int   $perpage
 * 
 * @return  array      
 */


function jino_get_template_course($course_list, $param, $page = 1, $perpage = 10) {
    global $DB;
    if ($page != 0) {
        $start = jino_offset($page, $perpage);
        $end = jino_offset($page+1, $perpage);
    } else {
        $start = 0;
        $end = $perpage;
    }
    if (!empty($course_list)) {
        $sql_in = 'IN( ';

        for ($start; $start < $end; $start++) {
            $courseid = $course_list[$start];
            if (!empty($courseid)) {
                $sql_in .= $courseid . ',';
            }
        }

        $sql_in .= ')';

        $sql_in = str_replace(",)", ")", $sql_in);
    } else {
        return null;
    }
    
    $sql_like = '';
    if(!empty($param['searchval'])){
        $sql_param['fullname'] = '%'.$param['searchval'].'%';
        $sql_like = ' AND '.$DB->sql_like('fullname', ':fullname', false);
    }
    if ($page == 0) {
        $data = $DB->count_records_sql("SELECT COUNT(*) FROM {course} where id " . $sql_in.$sql_like, $sql_param);
    } else {
        $data = $DB->get_records_sql("SELECT * FROM {course} where id " . $sql_in.$sql_like, $sql_param);
    }

    return $data;
}
function jino_offset($page, $limit) {
    return $limit * ($page-1);
}
