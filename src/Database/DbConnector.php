<?php
namespace ShortUrl\Database;

class DbConnector {

  private $dbConnection = null;

  public function __construct($dbName = null) {
    if ($dbName == null) {
      $dbName = $_ENV['MYSQL_DATABASE'];
    }
    $host = $_ENV['MYSQL_HOST'];
    $port = $_ENV['MYSQL_PORT'];
    $username = $_ENV['MYSQL_USER'];
    $password = $_ENV['MYSQL_PASSWORD'];
    $options = [
      \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
    ];

    $this->dbConnection = new \PDO(
      "mysql:host=$host;port=$port;dbname=$dbName",
      $username,
      $password,
      $options
    );
  }

  public function getConnection() {
    return $this->dbConnection;
  }
}
