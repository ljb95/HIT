<?php

function xmldb_local_certi_upgrade($oldversion) {
    global $CFG, $DB, $OUTPUT;

    $dbman = $DB->get_manager();

    if ($oldversion < 2016102100) {
        $table = new xmldb_table('lmsdata_certificate_history');
        //이수번호
        $field = new xmldb_field('certinum', XMLDB_TYPE_CHAR, '100', null, XMLDB_NOTNULL, null, '');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        //이수증 언어
        $field = new xmldb_field('lang', XMLDB_TYPE_CHAR, '10', null, XMLDB_NOTNULL, null, 'ko');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        //발급회수
        $field = new xmldb_field('issuecnt', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
    }
    
    if ($oldversion < 2016102101) {
        $table = new xmldb_table('lmsdata_certificate_history');
        //코스 아이디
        $field = new xmldb_field('courseid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
    }
    
    if ($oldversion < 2016102102) {
        $table = new xmldb_table('lmsdata_certificate');
        //이수증 양식 언어
        $field = new xmldb_field('lang', XMLDB_TYPE_CHAR, '10', null, XMLDB_NOTNULL, null, 'ko');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
    }
    
    return true;
}
