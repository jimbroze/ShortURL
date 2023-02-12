<?php
// Bootstrap file for testing only.

require_once 'vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . "/../");
$dotenv->load();

$_ENV["MYSQL_DATABASE"] = $_ENV["MYSQL_DATABASE_TEST"];