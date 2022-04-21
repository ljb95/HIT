<?php

function xmldb_local_lmsdata_upgrade($oldversion) {
    global $CFG, $DB, $OUTPUT;
    require_once($CFG->libdir . '/eventslib.php');

    $dbman = $DB->get_manager();

    if ($oldversion < 2017021400) {
        $table = new xmldb_table('lmsdata_user');
        $field = new xmldb_field('usergroup_cd', XMLDB_TYPE_CHAR, '10', null, null, null, '0');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        $field = new xmldb_field('univ_cd', XMLDB_TYPE_CHAR, '10', null, null, null, ' ');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        $field = new xmldb_field('major_cd', XMLDB_TYPE_CHAR, '10', null, null, null, ' ');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
    }

    if ($oldversion < 2017021401) {
        $table = new xmldb_table('lmsdata_class');

        $field = new xmldb_field('ohakkwa');
        $field->set_attributes(XMLDB_TYPE_CHAR, '255', null, true, null, null);
        try {
            $dbman->change_field_type($table, $field);
        } catch (moodle_exception $e) {
            
        }

        $field = new xmldb_field('domain');
        $field->set_attributes(XMLDB_TYPE_CHAR, '255', null, true, null, null);
        try {
            $dbman->change_field_type($table, $field);
        } catch (moodle_exception $e) {
            
        }
    }

    if ($oldversion < 2017032300) {

        $table = new xmldb_table('ust_sms');
        if ($dbman->table_exists($table)) {
            $dbman->rename_table($table, 'lmsdata_sms');
        }

        $table = new xmldb_table('ust_sms_data');
        if ($dbman->table_exists($table)) {
            $dbman->rename_table($table, 'lmsdata_sms_data');
        }
    }

    if ($oldversion < 2017033101) {
        $table = new xmldb_table('lmsdata_class');
        $field = new xmldb_field('tag', XMLDB_TYPE_CHAR, '300', null, null, null, null);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
    }

    if ($oldversion < 2017033102) {
        $table = new xmldb_table('lmsdata_class');

        $field = new xmldb_field('bunban');
        $field->set_attributes(XMLDB_TYPE_CHAR, '10', null, true, null, '00');
        try {
            $dbman->change_field_type($table, $field);
        } catch (moodle_exception $e) {
            
        }
    }
    if ($oldversion < 2017041800) {
        // Define table competency_usercompplan to be created.
        $table = new xmldb_table('siteadmin_loginfo');

        // Adding fields to table competency_usercompplan.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('username', XMLDB_TYPE_CHAR, '100', null, null, null, '');
        $table->add_field('ip', XMLDB_TYPE_CHAR, '30', null, null, null, '');
        $table->add_field('mobile', XMLDB_TYPE_CHAR, '100', null, null, null, '');
        $table->add_field('action', XMLDB_TYPE_CHAR, '100', null, null, null, '');
        $table->add_field('log_date', XMLDB_TYPE_INTEGER, '10', null, null, null, 0);

        // Adding keys to table competency_usercompplan.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        // Conditionally launch create table for competency_usercompplan.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }
    }
    if ($oldversion < 2017042400) {
        $table = new xmldb_table('lmsdata_class');

        $field = new xmldb_field('hakjum', XMLDB_TYPE_CHAR, '4', null, null, null, '0');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
    }
    if ($oldversion < 2017051200) {
        $table = new xmldb_table('lmsdata_class');

        $field = new xmldb_field('ohakkwa_cd', XMLDB_TYPE_CHAR, '30', null, null, null, '0');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
    }

    if ($oldversion < 2017053000) {
        $table = new xmldb_table('lmsdata_class');

        $field = new xmldb_field('univ_type', XMLDB_TYPE_CHAR, '2', null, null, null, '0');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
    }

    if ($oldversion < 2017053001) {
        // Define table competency_usercompplan to be created.
        $table = new xmldb_table('excel_user_period');

        // Adding fields to table competency_usercompplan.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, null, null, 0);
        $table->add_field('adminid', XMLDB_TYPE_INTEGER, '10', null, null, null, 0);
        $table->add_field('startdate', XMLDB_TYPE_INTEGER, '10', null, null, null, 0);
        $table->add_field('enddate', XMLDB_TYPE_INTEGER, '10', null, null, null, 0);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, null, null, 0);

        // Adding keys to table competency_usercompplan.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        // Conditionally launch create table for competency_usercompplan.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }
    }

    if ($oldversion < 2017080200) {

        // Define table popup to be created.
        $table = new xmldb_table('popup');

        // Adding fields to table popup.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('isactive', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('title', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('type', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('url', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('timedue', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timeavailable', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('popupx', XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('popupy', XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('popupwidth', XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('popupheight', XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('availablescroll', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('cookieday', XMLDB_TYPE_INTEGER, '2', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('description', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('descriptionformat', XMLDB_TYPE_INTEGER, '4', null, null, null, '0');
        $table->add_field('descriptiontrust', XMLDB_TYPE_INTEGER, '4', null, null, null, '0');
        $table->add_field('user', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');

        // Adding keys to table popup.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        // Conditionally launch create table for popup.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Lmsdata savepoint reached.
        upgrade_plugin_savepoint(true, 2017080200, 'local', 'lmsdata');
    }

    if ($oldversion < 2017080201) {

        // Define field name to be added to menu_auth_name.
        $table = new xmldb_table('menu_auth_name');
        $field = new xmldb_field('name', XMLDB_TYPE_CHAR, '100', null, XMLDB_NOTNULL, null, '0', 'lang');

        // Conditionally launch add field name.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Lmsdata savepoint reached.
        upgrade_plugin_savepoint(true, 2017080201, 'local', 'lmsdata');
    }
    
    if ($oldversion < 2017082400) {
        $table = new xmldb_table('lmsdata_user');
        $field = new xmldb_field('menu_auth', XMLDB_TYPE_INTEGER, '10', null, null, null, 0);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
    }
    
    if ($oldversion < 2018030500) {
        $table = new xmldb_table('lmsdata_user');
        $field = new xmldb_field('chn_name', XMLDB_TYPE_CHAR, '255', null, null, null);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        
        $field = new xmldb_field('dept_cd', XMLDB_TYPE_CHAR, '100', null, null, null);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        
        $field = new xmldb_field('dept', XMLDB_TYPE_CHAR, '100', null, null, null);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        
        $field = new xmldb_field('nation_cd', XMLDB_TYPE_INTEGER, '10', null, null, null);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
    }
    if ($oldversion < 2018032300) {
        $table = new xmldb_table('lmsdata_class');
        $field = new xmldb_field('learningtime', XMLDB_TYPE_INTEGER, '10', null, null, null);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
    }
    if ($oldversion < 2018032301) {
        $table = new xmldb_table('lmsdata_class');
        $field = new xmldb_field('certificateid', XMLDB_TYPE_INTEGER, '10', null, null, null);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
    }
    
    
    if ($oldversion < 2018080300) {
        $table = new xmldb_table('lmsdata_user');
        $field = new xmldb_field('day_tm_cd', XMLDB_TYPE_INTEGER, '2', null, null, null,0);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
    }
    
    if ($oldversion < 2018081700) {
        $table = new xmldb_table('lmsdata_class');

        $field = new xmldb_field('day_tm_cd', XMLDB_TYPE_CHAR, '10', null, null, null, null);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        
        $field = new xmldb_field('hyear', XMLDB_TYPE_CHAR, '10', null, null, null, null);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
    }
    
     if ($oldversion < 2018081701) {
        // Define table forum_like to be created.
        $table = new xmldb_table('forum_like');

        // Adding fields to table forum_like.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('course', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('forum', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('discussion', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('likey', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('post', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null,0);

        // Adding keys to table forum_like.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        // Conditionally launch create table for forum_like.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

    }
    
    if ($oldversion < 2018081701) {
        $table = new xmldb_table('forum_like');
        $field = new xmldb_field('post', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null,0);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
    }
    
     if ($oldversion < 2018082700) {

        // Define table haksa_auto_sync to be created.
        $table = new xmldb_table('haksa_auto_sync');

        // Adding fields to table haksa_auto_sync.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('year', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('term', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('hour', XMLDB_TYPE_INTEGER, '2', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        
        // Adding keys to table haksa_auto_sync.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        // Conditionally launch create table for haksa_auto_sync.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }
     }
    
    if ($oldversion < 2019041701) {
        $table = new xmldb_table('lmsdata_user');

        $field = new xmldb_field('status', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
    }
    
    if ($oldversion < 2019061100) {
        $table = new xmldb_table('lmsdata_user');

        $field = new xmldb_field('status_nm', XMLDB_TYPE_CHAR, '50', null, null, null, null);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
    }
     
    
    if($oldversion < 2019070303) {
        $table = new xmldb_table('lmsdata_sms');

        $field = new xmldb_field('contents', XMLDB_TYPE_TEXT, null, null, null, null, null);
        if ($dbman->field_exists($table, $field)) {
            $dbman->change_field_default($table, $field);
        }
    }
    return true;
}
