<?php
function xmldb_local_evaluation_upgrade($oldversion) {
    global $CFG, $DB, $OUTPUT;
    
    $dbman = $DB->get_manager(); 
    
    if($oldversion < 2015092100) {     
        $table = new xmldb_table('lmsdata_evaluation');  
        $field = new xmldb_field('professors', XMLDB_TYPE_CHAR, '100', null, null, null, '0');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
    }
    
    if($oldversion < 2015102001) {
        $table = new xmldb_table('lmsdata_evaluation_history');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('evaluation', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0);
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0);

        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }
    }
    
    if($oldversion < 2015102700) {     
        $table = new xmldb_table('lmsdata_evaluation_submits');  
        $field = new xmldb_field('prof_userid', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        $table = new xmldb_table('lmsdata_evaluation_history');  
        $field = new xmldb_field('prof_userid', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        
    }
    
    if($oldversion < 2017053000) {     
        $table = new xmldb_table('lmsdata_evaluation');  
        $field = new xmldb_field('compulsion', XMLDB_TYPE_INTEGER, '1', null, null, null, '0');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
    }
    
    return true;
}

