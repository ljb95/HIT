<?php
defined('MOODLE_INTERNAL') || die();

/**
 * The upgrade function for local_online_attendance.
 *
 * @param int $oldversion
 * @return boolean
 */
function xmldb_local_online_attendance_upgrade($oldversion) {
    global $DB;
    
    $dbman = $DB->get_manager();
    
    if ($oldversion < 2017030500) {
        $table = new xmldb_table('local_onattend_cm_set');
        $field = new xmldb_field('completeflag', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
    }
    
    if ($oldversion < 2017030501) {
        $table = new xmldb_table('local_onattend_status');
        $field = new xmldb_field('lprogress', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
    }
    
    if ($oldversion < 2017030700) {
        
        // 지각 일정 관련 필드 삭제
        $table = new xmldb_table('local_onattend_cm_batchset');

        $field = new xmldb_field('timetype');
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }
        $field = new xmldb_field('lateratio');
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }
        $field = new xmldb_field('lprogress');
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }
        
        // 지각 일정 관련 필드 삭제
        $table = new xmldb_table('local_onattend_cm_set');

        $field = new xmldb_field('latetime');
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }
        $field = new xmldb_field('lprogress');
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }
        $field = new xmldb_field('completeflag');
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }
        
        // 지각 일정 관련 필드 삭제
        $table = new xmldb_table('local_onattend_status');

        $field = new xmldb_field('lprogress');
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }
        
        // 주차별 출석 내용 저장 테이블 추가
        $table = new xmldb_table('local_onattend_week_status');  
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('courseid', XMLDB_TYPE_INTEGER, '10', NULL, XMLDB_NOTNULL, null, 0);
        $table->add_field('section', XMLDB_TYPE_INTEGER, '10', NULL, XMLDB_NOTNULL, null, 0);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', NULL, XMLDB_NOTNULL, null, 0);
        $table->add_field('status', XMLDB_TYPE_INTEGER, '2', NULL, XMLDB_NOTNULL, null, 0);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', NULL, XMLDB_NOTNULL, null, 0);
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', NULL, XMLDB_NOTNULL, null, 0);
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }
        
        // 로그 테이블 데이터 추가
        $table = new xmldb_table('local_onattend_status');
        $field = new xmldb_field('objectid', XMLDB_TYPE_CHAR, '500', null, false, null, '0');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
    } 
    
    if ($oldversion < 2017030701) {
        
        // 지각 일정 관련 필드 삭제
        $table = new xmldb_table('local_onattend_status');

        $field = new xmldb_field('objectid');
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }
        // 로그 테이블 데이터 추가
        $table = new xmldb_table('local_onattend_log');
        $field = new xmldb_field('objectid', XMLDB_TYPE_CHAR, '500', null, false, null, '');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
    }
    
    if ($oldversion < 2017030702) {
        $table = new xmldb_table('local_onattend_log');
        $field = new xmldb_field('objectdata', XMLDB_TYPE_CHAR, '500', null, false, null, '');
        $dbman->change_field_type($table, $field);
        $field = new xmldb_field('objectid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $dbman->change_field_type($table, $field);
    }
    
    if ($oldversion < 2017030703) {
        $table = new xmldb_table('local_onattend_week_status');
        $field = new xmldb_field('fixstatus', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        
    }
    return true;
}
