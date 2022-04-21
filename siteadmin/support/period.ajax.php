<?php

require_once dirname(dirname(dirname(__FILE__))) . '/config.php';
require_once dirname(dirname(__FILE__)) . '/lib.php';

$id = optional_param('id', 0, PARAM_RAW);
$startdate = optional_param('period_start', '', PARAM_RAW);
$enddate = optional_param('period_end', '', PARAM_RAW);
$year = required_param('year', PARAM_INT);
$term = required_param('term', PARAM_INT);
$mode = optional_param('mode', "add", PARAM_RAW);

$sql = 'select max(startdate) as startdate,max(section) as section from {lmsdata_trust} where year = :year and term = :term';
$latest_period = $DB->get_record_sql($sql, array('year' => $year, 'term' => $term));
$latest_period_id = $DB->get_field('lmsdata_trust','id',array('section'=>$latest_period->section,'startdate'=>$latest_period->startdate));

if($mode == 'add'){

$new_period = new stdClass();
$new_period->year = $year;
$new_period->term = $term;
$new_period->userid = $USER->id;
$new_period->startdate = strtotime('+7 days', $latest_period->startdate);
$new_period->enddate = strtotime('+20 days', $latest_period->startdate);
$new_period->section = $latest_period->section + 1;
$new_period->timecreated = time();
$new_period->timemodified = time();

$new_id = $DB->insert_record('lmsdata_trust', $new_period);
?>
<tr id="periodid<?php echo $new_id; ?>">
                            <td width="15%"><?php echo $new_period->section; ?> 주차</td>
                            <td class="text-left">
                                <input type="date" name="period_start" value="<?php echo date('Y-m-d',$new_period->startdate); ?>" /> 
                                ~ 
                                <input type="date" name="period_end" value="<?php echo date('Y-m-d',$new_period->enddate); ?>" />
                                <input type="button" id="delete_notice" class="red_btn" value="<?php echo get_string('save','local_lmsdata'); ?>" />
                            </td>
</tr>
<?php 

} else if($mode == 'delete'){ 
    $DB->delete_records('lmsdata_trust',array('id'=>$latest_period_id));
    echo $latest_period_id;
 } else if($mode == 'edit'){
     $trust = $DB->get_record('lmsdata_trust',array('id'=>$id));
     $modifiy_period = new stdClass();
     $modifiy_period->id = $trust->id;
     $modifiy_period->startdate = strtotime($startdate);
     $modifiy_period->enddate   = strtotime($enddate);
     $DB->update_record('lmsdata_trust',$modifiy_period);
     echo "<script>location.href='".$CFG->wwwroot."/siteadmin/support/period.php?year".$year."&term=".$term."&mode=edit'</script>";
 } 
 ?>


