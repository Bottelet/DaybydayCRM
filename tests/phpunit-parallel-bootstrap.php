<?php

// Require Composer autoloader (this is what "bootstrap=vendor/autoload.php" did)
require __DIR__ . '/../vendor/autoload.php';

// Detect the Paratest worker token
$token = getenv('TEST_TOKEN') ?: getenv('PARATEST');

// Only override if running Paratest, for partitioning
if ($token) {
    $db = "daybyday_test_{$token}";
    putenv("DB_DATABASE={$db}");
    $_ENV['DB_DATABASE']    = $db;
    $_SERVER['DB_DATABASE'] = $db;
}
