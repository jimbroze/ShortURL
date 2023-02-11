<?php

require_once 'vendor/autoload.php'

use ShortUrl\Database\DbConnector;

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . "/../");
$dotenv->load();

// Create database connection for the app.
$dbConnection = (new DbConnector())->getConnection();