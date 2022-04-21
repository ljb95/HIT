<?php

defined('MOODLE_INTERNAL') || die();

$plugin->version   = 2017082100;
$plugin->requires  = 2012061700;       

$plugin->component = 'local_courseguide'; // Full name of the plugin (used for diagnostics)

$plugin->dependencies = array(
    'local_okirregular' => ANY_VERSION,
    'local_okregular' => ANY_VERSION
);