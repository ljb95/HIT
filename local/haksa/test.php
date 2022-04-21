<?php

require('/home/moodle/local/haksa/lib/doctrine/Doctrine/Common/ClassLoader.php');

$classLoader = new Doctrine\Common\ClassLoader('Doctrine', '/home/moodle/local/haksa/lib/doctrine');
$classLoader->register();

$config = new \Doctrine\DBAL\Configuration();

$connectionParams = array(
    'dbname' => 'testdb',
    'user' => 'db2user',
    'password' => 'jinotech',
    'host' => 'localhost',
    'port' => '50000',
    'driver' => 'ibm_db2',
);

$conn = \Doctrine\DBAL\DriverManager::getConnection($connectionParams, $config);
$sql = "SELECT * FROM t1";
$stmt = $conn->query($sql);
while ($row = $stmt->fetch()) {
  print_r($row);
}
$conn->close();