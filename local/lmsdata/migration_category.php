<?php

require('../../config.php');
require_once($CFG->dirroot.'/course/lib.php');
require_once($CFG->libdir.'/coursecatlib.php');
ini_set('memory_limit','-1');

// 1depth 카테고리 목록
$hunivs = $DB->get_records_sql('SELECT orgn_clsf_dcd_nm, orgn_clsf_dcd from {haxa_course_info} group by orgn_clsf_dcd_nm, orgn_clsf_dcd order by orgn_clsf_dcd_nm asc');

$parentid = $DB->get_field('course_categories', 'id', array('idnumber'=>'oklass_regular'));
foreach($hunivs as $ucate) {
    if(!$category = $DB->get_record('course_categories', array('parent'=>$parentid, 'name'=>$ucate->orgn_clsf_dcd_nm))) {
        
        $newcategory = new stdClass();
        $newcategory->name = $ucate->orgn_clsf_dcd_nm;
        $newcategory->description = " ";
        $newcategory->idnumber = $ucate->orgn_clsf_dcd;
        $newcategory->sortorder = time();
        $newcategory->parent = $parentid; 

        $org_cate = coursecat::create($newcategory);
        $ucate->sortnum = $org_cate->sortorder;
        $ucate->cateid = $org_cate->id;
        $ucate->idnumber = $newcategory->idnumber;
    }else {
        $ucate->sortnum = $category->sortorder;
        $ucate->cateid = $category->id;
        $ucate->idnumber = $ucate->orgn_clsf_dcd;
    } 
}

$hmajors_sql = "select 
                  ca4.id, ca4.name, ca4.path, ca4.coursecount, 
                  ca3.name as pname, ca3.path as ppath, ca4.idnumber as pidnumber
                from {course_categories} ca1 
                join {course_categories} ca2 ON ca2.parent = ca1.id
                join {course_categories} ca3 ON ca3.parent = ca2.id
                join {course_categories} ca4 ON ca4.parent = ca3.id
                where ca1.name IN ('2014', '2015', '2016') order by ca3.name, ca4.name asc";

$hmajors = $DB->get_records_sql($hmajors_sql);
$cateid_array = array();
foreach($hmajors as $major) {
    $pname = $major->pname;
    $pidnumber = $DB->get_field_sql('select distinct orgn_clsf_dcd from {haxa_course_info} where orgn_clsf_dcd_nm = :pname', array('pname'=>$pname));
    $name = $major->name;
    $idnumber = $DB->get_field_sql('select distinct asgn_sust_cd from {haxa_course_info} where orgn_clsf_dcd = :pidnumber AND asgn_sust_cd_nm = :name', array('pidnumber'=>$pidnumber, 'name'=>$name));
    if(!$pcateid = $DB->get_field('course_categories', 'id', array('parent'=>$parentid, 'name'=>$pname, 'depth'=>2))){
        $pcateid = $DB->get_field('course_categories', 'id', array('parent'=>$parentid, 'idnumber'=>$pidnumber, 'depth'=>2));
    }
    
    if(empty($pcateid)) {
        print_object($major);
    } else {
        if(!$cateid = $DB->get_field('course_categories', 'id', array('parent'=>$pcateid, 'name'=>$name, 'depth'=>3))){
            $cateid = $DB->get_field('course_categories', 'id', array('parent'=>$pcateid, 'idnumber'=>$pidnumber.'-'.$idnumber, 'depth'=>3));
            if(empty($cateid)) {
                $newcategory = new stdClass();
                $newcategory->name = $name;
                $newcategory->description = " ";
                $newcategory->idnumber = $pidnumber.'-'.$idnumber;
                $newcategory->sortorder = time();
                $newcategory->parent = $pcateid; 
                try{
                    $newcate = coursecat::create($newcategory);
                    $cateid = $newcate->id;
                } catch (moodle_exception $ex) {
                   print_object($newcategory);
                }
            } 
        }
    }
    
    $coursecat = coursecat::get($cateid);
    $coursesids = $DB->get_records_menu('course', array('category'=>$major->id), '', 'id');
        // If this fails we want to catch the exception and report it.
    $redirectback = \core_course\management\helper::move_courses_into_category($coursecat, array_keys($coursesids));
    
}
    
?>