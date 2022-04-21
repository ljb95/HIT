<?php
function xmldb_local_jinoboard_upgrade($oldversion) {
    global $CFG, $DB, $OUTPUT;
    
    $dbman = $DB->get_manager(); 
    
    if($oldversion < 2016022300) {     
        $table = new xmldb_table('jinoboard_contents');  
        $field = new xmldb_field('targets', XMLDB_TYPE_CHAR, '255', null, null, null, '');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field); 
        }
    }
    
    if($oldversion < 2016022301) {     
        $table = new xmldb_table('jinoboard_contents');  
        $field = new xmldb_field('timestart', XMLDB_TYPE_INTEGER, '10', null, null, null, 0);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field); 
        }
    }
    
    if($oldversion < 2016120700) {     
        $table = new xmldb_table('jinoboard');  
        $field = new xmldb_field('access', XMLDB_TYPE_INTEGER, '10', null, null, null, 0);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field); 
        }
    }
    
    
    return true;
}

