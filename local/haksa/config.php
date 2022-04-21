<?php

unset($HCFG);
global $HCFG;
$HCFG = new stdClass();

/* 실서버 */
$HCFG->dbtype    = 'ibm_db2';
$HCFG->dbhost    = 'localhost';
$HCFG->dbname    = 'testdb';
$HCFG->dbuser    = 'db2user';
$HCFG->dbpass    = 'jinotech';
$HCFG->dbport    = '50000';

require_once(dirname(__FILE__) . '/lib/setup.php');
