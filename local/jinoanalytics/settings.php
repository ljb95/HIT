<?php
defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig) {
	$settings = new admin_settingpage('local_analytics', get_string('pluginname', 'local_jinoanalytics'));
	$settings->add(new admin_setting_configtext(
            'local_analytics/modules', 
            get_string('displaymodules', 'local_jinoanalytics'), '', 
            'assign,forum,quiz,wiki,feedback,url,book,resource'
            ));
	$ADMIN->add('localplugins', $settings);
}