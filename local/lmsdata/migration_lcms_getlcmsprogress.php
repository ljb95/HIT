<?php

//define('CLI_SCRIPT', true);
//define('CACHE_DISABLE_ALL', true);

require('../../config.php');
require_once($CFG->dirroot . "/local/lmsdata/migration_lib.php");
require_once($CFG->dirroot . "/course/lib.php");
require_once($CFG->dirroot.'/lib/grade/grade_grade.php');
require_once $CFG->dirroot.'/grade/lib.php';

$old_lcmsprogress = $old_lcmses = $DB->get_records('templcmsprogress');

$lcmsmoduleid = $DB->get_field('modules', 'id', array('name'=>'lcmsprogress'));
$lcmsprogress_arr = array();

foreach($old_lcmsprogress as $old_progress) {
    
    if($DB->record_exists('course', array('id'=>$old_progress->course))){
        //lcmsprogress activity 생성
        $newprogress_object = new stdClass();
        $newprogress_object->name = $old_progress->name;
        $newprogress_object->introeditor = array(
                                'text' => $old_progress->intro,
                                'format' => 1
                            );
        $newprogress_object->visible = 1;      
        $newprogress_object->groupingid = 0;    
        $newprogress_object->grade = 100;    
        $newprogress_object->gradecat = $DB->get_field('grade_categories', 'id', array('courseid'=>$old_progress->course, 'depth'=>1));    
        $newprogress_object->grade_rescalegrades = null;    
        $newprogress_object->gradepass = null;    
        $newprogress_object->course = $old_progress->course;      
        $newprogress_object->section = $old_progress->section;      
        $newprogress_object->module = $lcmsmoduleid;      
        $newprogress_object->modulename = 'lcmsprogress';      

        $newlcms_info = create_module($newprogress_object);

        $totoalprogress = new stdClass();
        $totoalprogress->old_progressid = $old_progress->id;
        $totoalprogress->new_progressid = $newlcms_info->id;
        $lcmsprogress_arr[$old_progress->id] = $totoalprogress;
    }
}

$old_grades = $DB->get_records('templcmsprogress_grades');

foreach($old_grades as $old_grade) {
    $progressid = $lcmsprogress_arr[$old_grade->lcmsprogress]->new_progressid;
    if(!empty($progressid)) {
        $grade = new stdClass();
        $grade->lcmsprogress = $progressid;
        $grade->userid = $old_grade->userid;
        $grade->progress = round($old_grade->progress);
        $grade->grade = $grade->progress;
        $grade->timecreated = $old_grade->timecreated;
        $grade->timemodified = $old_grade->timemodified;
        $DB->insert_record('lcmsprogress_grades', $grade);
        
        
        $itemid = $DB->get_field('grade_items', 'id', array('itemmodule'=>'lcmsprogress', 'iteminstance'=>$progressid));
        $gradegrade = grade_grade::fetch(array('itemid' => $itemid, 'userid' => $old_grade->userid));
        if(!empty($gradegrade)) {
            $gradegrade->finalgrade = $grade->grade;
            $gradegrade->update();
        }
    }
}
