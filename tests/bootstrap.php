<?php
// Bootstrap file for testing only.
// DbConnection created in test file using test database.

require_once 'vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . "/../");
$dotenv->load();