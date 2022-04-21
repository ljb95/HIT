<?php

function xmldb_local_haksa_upgrade($oldversion) {
    global $CFG, $DB, $OUTPUT;
    require_once($CFG->libdir . '/eventslib.php');

    $dbman = $DB->get_manager();

//    if($oldversion < 2015022300) {
//        $table = new xmldb_table('haksa_class_student');
//        $field = new xmldb_field('name', XMLDB_TYPE_CHAR, '100', null, null, null, null);
//        if (!$dbman->field_exists($table, $field)) {
//            $dbman->add_field($table, $field);
//        }
//    }

    if ($oldversion < 2015031502) {
        $table = new xmldb_table('haksa_class');
        $index = new xmldb_index('deleted', XMLDB_INDEX_NOTUNIQUE, array('deleted'));
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }

        $table = new xmldb_table('haksa_class');
        $index = new xmldb_index('year-ohakkwa', XMLDB_INDEX_NOTUNIQUE, array('year', 'term', 'hakno', 'bb', 'sbb', 'ohakkwa'));
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }

        $table = new xmldb_table('haksa_class_student');
        $index = new xmldb_index('year-ohakkwa', XMLDB_INDEX_NOTUNIQUE, array('year', 'term', 'hakno', 'bb', 'sbb', 'ohakkwa'));
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }

        $table = new xmldb_table('haksa_class_professor');
        $index = new xmldb_index('year-bb', XMLDB_INDEX_NOTUNIQUE, array('year', 'term', 'hakno', 'bb'));
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }
    }

    if ($oldversion < 2015061200) {
        $table = new xmldb_table('haksa_class');

        $field = new xmldb_field('cata1_eng', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('cata2_eng', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('cata3_eng', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $classes = $DB->get_records('haksa_class');
        foreach ($classes as $class) {
            $class->cata1_eng = $class->cata1;
            $class->cata2_eng = $class->cata2;
            $class->cata3_eng = $class->cata3;
            $DB->update_record('haksa_class', $class);
        }
    }

    if ($oldversion < 2015062901) {
        $table = new xmldb_table('haksa_class_student');
        $field = new xmldb_field('deleted', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $table = new xmldb_table('haksa_class_professor');
        $field = new xmldb_field('deleted', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
    }

    if ($oldversion < 2016021700) {
        $table = new xmldb_table('haksa_class');
        $field = new xmldb_field('shortname', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
    }

    if ($oldversion < 2016021701) {
        $table = new xmldb_table('haksa_class_student');
        $field = new xmldb_field('lec_cd', XMLDB_TYPE_CHAR, '20', null, null, null, null);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $table = new xmldb_table('haksa_class_professor');
        $field = new xmldb_field('lec_cd', XMLDB_TYPE_CHAR, '20', null, null, null, null);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
    }

    if ($oldversion < 2017012200) {
        $table = new xmldb_table('haksa_class');

        $field = new xmldb_field('term');
        $field->set_attributes(XMLDB_TYPE_CHAR, '2', null, true, null, null);
        try {
            $dbman->change_field_type($table, $field);
        } catch (moodle_exception $e) {
            
        }
    }

    if ($oldversion < 2018081700) {
        $table = new xmldb_table('haksa_class');

        $field = new xmldb_field('day_tm_cd', XMLDB_TYPE_CHAR, '10', null, null, null, null);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        
        $field = new xmldb_field('hyear', XMLDB_TYPE_CHAR, '10', null, null, null, null);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
    }

    return true;
}
