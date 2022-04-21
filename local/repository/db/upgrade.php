<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * This file keeps track of upgrades to
 * the jinoforum module
 *
 * Sometimes, changes between versions involve
 * alterations to database structures and other
 * major things that may break installations.
 *
 * The upgrade function in this file will attempt
 * to perform all the necessary actions to upgrade
 * your older installation to the current version.
 *
 * If there's something it cannot do itself, it
 * will tell you what you need to do.
 *
 * The commands in here will all be database-neutral,
 * using the methods of database_manager class
 *
 * Please do not forget to use upgrade_set_timeout()
 * before any action that may take longer time to finish.
 *
 * @package   mod_jinoforum
 * @copyright 2003 onwards Eloy Lafuente (stronk7) {@link http://stronk7.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
function xmldb_local_repository_upgrade($oldversion) {
    global $CFG, $DB, $OUTPUT;

    $dbman = $DB->get_manager(); // Loads ddl manager and xmldb classes.
    // Moodle v2.5.0 release upgrade line.
    // Put any upgrade step following this.
    if ($oldversion < 2015022800) {
        // Define table jinoforum_digests to be created.
        $table = new xmldb_table('lcms_groups');

        if ($dbman->table_exists($table)) {
            $dbman->drop_table($table);
        }

        $table = new xmldb_table('lcms_repository_groups');

        // Adding fields to table jinoforum_digests.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('name', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('filecnt', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);

        // Adding keys to table jinoforum_digests.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));


        // Conditionally launch create table for jinoforum_digests.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }
    }

    if ($oldversion < 2015022801) {
        // Define table jinoforum_digests to be created.

        $table = new xmldb_table('lcms_contents');

        // Adding fields to table jinoforum_digests.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('area_cd', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('major_cd', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('course_cd', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('teacher', XMLDB_TYPE_CHAR, '50', null, null, null, null);
        $table->add_field('share_yn', XMLDB_TYPE_CHAR, '20', null, null, null, null);
        $table->add_field('con_name', XMLDB_TYPE_CHAR, '100', null, XMLDB_NOTNULL, null, null);
        $table->add_field('con_type', XMLDB_TYPE_CHAR, '20', null, XMLDB_NOTNULL, null, null);
        $table->add_field('con_des', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('con_tag', XMLDB_TYPE_CHAR, '300', null, null, null, null);
        $table->add_field('con_total_time', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0);
        $table->add_field('author', XMLDB_TYPE_CHAR, '50', null, null, null, null);
        $table->add_field('cc_type', XMLDB_TYPE_CHAR, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('cc_mark', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $table->add_field('embed_type', XMLDB_TYPE_CHAR, '10', null, null, null, null);
        $table->add_field('embed_code', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $table->add_field('data_dir', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $table->add_field('user_no', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('con_hit', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0);
        $table->add_field('reg_dt', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('update_dt', XMLDB_TYPE_INTEGER, '10', null, null, null, null);

        // Adding keys to table jinoforum_digests.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));


        // Conditionally launch create table for jinoforum_digests.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }


        $table = new xmldb_table('lcms_contents_file');

        // Adding fields to table jinoforum_digests.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('con_seq', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('duration', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('filepath', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $table->add_field('filename', XMLDB_TYPE_CHAR, '100', null, null, null, null);
        $table->add_field('fileoname', XMLDB_TYPE_CHAR, '100', null, null, null, null);
        $table->add_field('filesize', XMLDB_TYPE_CHAR, '100', null, null, null, null);
        $table->add_field('con_type', XMLDB_TYPE_CHAR, '20', null, null, null, null);
        $table->add_field('bitrate', XMLDB_TYPE_CHAR, '20', null, null, null, null);


        // Adding keys to table jinoforum_digests.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));


        // Conditionally launch create table for jinoforum_digests.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }
    }


    // 마이그레이션하는 동안 아이디를 저장하기 위해 사용
    // 마이그레이션 끝나고 삭제해야 함.
    if ($oldversion < 2015031800) {
        $table = new xmldb_table('lcms_migration_temp');

        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('oldid', XMLDB_TYPE_CHAR, '20', null, XMLDB_NOTNULL, null, null);
        $table->add_field('newid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('migration', XMLDB_TYPE_CHAR, '20', null, null, null, null);

        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->add_index('newid', XMLDB_INDEX_NOTUNIQUE, array('newid'));
        $table->add_index('oldid', XMLDB_INDEX_NOTUNIQUE, array('oldid'));
        $table->add_index('migration_oldid', XMLDB_INDEX_NOTUNIQUE, array('migration', 'oldid'));

        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }
    }

    if ($oldversion < 2015031801) {
        $table = new xmldb_table('lcms_repository_open');

        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('year', XMLDB_TYPE_CHAR, '10', null, null, null, null);
        $table->add_field('term', XMLDB_TYPE_CHAR, '10', null, null, null, null);
        $table->add_field('course', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('isopen', XMLDB_TYPE_CHAR, '1', null, null, null, null);

        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }
    }

    if ($oldversion < 2015032700) {

        $table = new xmldb_table('lcms_repository_groups');
        $field = new xmldb_field('userid', XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, null, '0');

        // Conditionally launch add field.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $table = new xmldb_table('lcms_contents_file');
        $field = new xmldb_field('user_no', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');

        // Conditionally launch add field.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
    }


    if ($oldversion < 2015070900) {
        $table = new xmldb_table('lcms_user_info');

        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('group_seq', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0);
        $table->add_field('user_pw', XMLDB_TYPE_CHAR, '100', null, XMLDB_NOTNULL, null, null);
        $table->add_field('user_email', XMLDB_TYPE_CHAR, '50', null, null, null, null);
        $table->add_field('user_name', XMLDB_TYPE_CHAR, '50', null, XMLDB_NOTNULL, null, null);
        $table->add_field('user_tel', XMLDB_TYPE_CHAR, '30', null, XMLDB_NOTNULL, null, null);
        $table->add_field('area_cd', XMLDB_TYPE_INTEGER, '2', null, XMLDB_NOTNULL, null, 0);
        $table->add_field('major_cd', XMLDB_TYPE_INTEGER, '2', null, XMLDB_NOTNULL, null, 0);
        $table->add_field('reg_ip', XMLDB_TYPE_CHAR, '30', null, XMLDB_NOTNULL, null, null);
        $table->add_field('reg_dt', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('update_dt', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('last_login', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('use_yn', XMLDB_TYPE_CHAR, '1', null, XMLDB_NOTNULL, null, null);
        $table->add_field('del_yn', XMLDB_TYPE_CHAR, '1', null, null, null, null);
        $table->add_field('user_id', XMLDB_TYPE_CHAR, '30', null, XMLDB_NOTNULL, null, null);
        $table->add_field('user_auth', XMLDB_TYPE_CHAR, '100', null, null, null, null);

        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }
    }
    if ($oldversion < 2015071300) {
        $table = new xmldb_table('lcms_clas_area');

        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('area_name', XMLDB_TYPE_CHAR, '100', null, XMLDB_NOTNULL, null, null);
        $table->add_field('user_no', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('update_dt', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('use_yn', XMLDB_TYPE_CHAR, '1', null, XMLDB_NOTNULL, null, 'Y');

        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->add_key('user_no', XMLDB_KEY_FOREIGN, array('user_no'), 'lcms_user_info', array('id'));

        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        $table = new xmldb_table('lcms_clas_course');

        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('major_cd', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('area_cd', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('course_name', XMLDB_TYPE_CHAR, '100', null, XMLDB_NOTNULL, null, null);
        $table->add_field('user_no', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('update_dt', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('use_yn', XMLDB_TYPE_CHAR, '1', null, XMLDB_NOTNULL, null, 'Y');
        $table->add_field('storage', XMLDB_TYPE_CHAR, '30', null, XMLDB_NOTNULL, null, null);

        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->add_key('user_no', XMLDB_KEY_FOREIGN, array('user_no'), 'lcms_user_info', array('id'));

        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        $table = new xmldb_table('lcms_clas_major');

        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('area_cd', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('major_name', XMLDB_TYPE_CHAR, '100', null, XMLDB_NOTNULL, null, null);
        $table->add_field('user_no', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('update_dt', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('use_yn', XMLDB_TYPE_CHAR, '1', null, XMLDB_NOTNULL, null, 'Y');


        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->add_key('user_no', XMLDB_KEY_FOREIGN, array('user_no'), 'lcms_user_info', array('id'));

        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }
    }
    
    if ($oldversion < 2015072801) {

        $table = new xmldb_table('lcms_contents');
        $field = new xmldb_field('auth_key', XMLDB_TYPE_CHAR, '100', null, null, null, '0');

        // Conditionally launch add field.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
    }
    if ($oldversion < 2016032300) {

        $table = new xmldb_table('lcms_repository');
        $field = new xmldb_field('iscdms', XMLDB_TYPE_INTEGER, '2', null, null, null, '0');

        // Conditionally launch add field.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        $field = new xmldb_field('status', XMLDB_TYPE_INTEGER, '2', null, null, null, '0');

        // Conditionally launch add field.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
    }
    if ($oldversion < 2016033100) {

        $table = new xmldb_table('lcms_repository');
        $field = new xmldb_field('delaymsg', XMLDB_TYPE_CHAR, '255', null, null, null, '0');

        // Conditionally launch add field.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
    }
        if ($oldversion < 2016110400) {
        $table = new xmldb_table('lcms_history');

        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('contentid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('event', XMLDB_TYPE_CHAR, '100', null, XMLDB_NOTNULL, null, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('type', XMLDB_TYPE_CHAR, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }
    }

    return true;
}
