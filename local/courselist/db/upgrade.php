<?php

function xmldb_local_courselist_upgrade($oldversion) {
    global $CFG, $DB, $OUTPUT;
    
    $dbman = $DB->get_manager(); 
    
    if($oldversion < 2015022300) {  
        $newcategory = new stdClass();
        $newcategory->name = "비정규";
        $newcategory->description = " ";
        $newcategory->idnumber = "jinotech_irregular_2015_02_23_categorynumber";
        $newcategory->sortorder = 999;

        $irregular = coursecat::create($newcategory);

        $newcategory2 = new stdClass();
        $newcategory2->name = "GTEC 자체과목";
        $newcategory2->description = " ";
        $newcategory2->idnumber = "jinotech_gtec_self_2015_02_23_categorynumber";
        $newcategory2->parent = $irregular->id; 
        $newcategory2->sortorder = 999;

        coursecat::create($newcategory2);

    }
    
    //비교과 이수증 내역 테이블 추가
    if($oldversion < 2016080900) {
        $table = new xmldb_table('course_irregular_complete');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('courseid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0);
        $table->add_field('complete', XMLDB_TYPE_INTEGER, '1', null, null, null, 0);
        $table->add_field('grade', XMLDB_TYPE_CHAR, '10', NULL, null, null);
        $table->add_field('department', XMLDB_TYPE_CHAR, '2', NULL, null, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0);
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0);
        
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }
    }
    
    //이수증 필드 추가
    if($oldversion < 2016080901) {     
        $table = new xmldb_table('lmsdata_class');  
        $field = new xmldb_field('certificate', XMLDB_TYPE_CHAR, '1', null, null, null, 0);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
    }
    
    if($oldversion < 2016080902) {
        $table = new xmldb_table('lmsdata_class_drive_log');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('standard_id', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('subject_id', XMLDB_TYPE_INTEGER, '10', NULL, NULL, NULL, NULL);
        $table->add_field('user_id', XMLDB_TYPE_INTEGER, '10', NULL, NULL, NULL, NULL);
        $table->add_field('invisible', XMLDB_TYPE_INTEGER, '1', NULL, NULL, NULL, NULL);
        $table->add_field('type', XMLDB_TYPE_INTEGER, '1', NULL, NULL, NULL, NULL);
        $table->add_field('restore_flag', XMLDB_TYPE_INTEGER, '1', null, null, null, 0);
        $table->add_field('restore_user_id', XMLDB_TYPE_INTEGER, '10', NULL, NULL, NULL, 0);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', NULL, NULL, NULL, NULL);
        $table->add_field('timerestore', XMLDB_TYPE_INTEGER, '10', null, null, null, 0);
        
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }
    }
    
    if ($oldversion < 2016081200) {
        $table = new xmldb_table('lmsdata_class_drive_log');
         
        $restore_flag_field = new xmldb_field('restore_flag', XMLDB_TYPE_INTEGER, '2', null, XMLDB_NOTNULL, null, 0);
        $dbman->change_field_type($table,$restore_flag_field);
        
        $type_field = new xmldb_field('type', XMLDB_TYPE_INTEGER, '2', null, XMLDB_NOTNULL, null, 0);
        $dbman->change_field_type($table,$type_field);
    }
    
    return true;
}
