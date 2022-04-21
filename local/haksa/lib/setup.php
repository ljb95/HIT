<?php

$HCFG->dirroot = dirname(dirname(__FILE__));
$HCFG->libdir = $HCFG->dirroot .'/lib';



global $conn;

use Doctrine\Common\ClassLoader;

require($HCFG->libdir.'/doctrine/Doctrine/Common/ClassLoader.php');


$classLoader = new ClassLoader('Doctrine', $HCFG->libdir.'/doctrine');
$classLoader->register();


$config = new \Doctrine\DBAL\Configuration();

$connectionParams = array(
    'dbname' => $HCFG->dbname,
    'user' => $HCFG->dbuser,
    'password' => $HCFG->dbpass,
    'host' => $HCFG->dbhost,
    'port' => $HCFG->dbport,
    'driver' => $HCFG->dbtype
);

$conn = \Doctrine\DBAL\DriverManager::getConnection($connectionParams, $config);
