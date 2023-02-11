<?php

use PHPUnit\Framework\TestCase;
use ShortUrl\Database\DbConnector;
use ShortUrl\Database\UrlModel;

/**
 * @coversDefaultClass ShortUrl\Database\UrlModel
 */
final class DatabaseTest extends TestCase {
  private static $dbConnection;
  private $shortUrlCode;
  private $long_url;

  /**
   * @beforeClass
   */
  public static function setUpBeforeClass(): void {
    $databaseName = $_ENV['MYSQL_DATABASE_TEST'];
    if (is_null($databaseName))
      exit("Test database name is not set.");

    self::$dbConnection = (new DbConnector($databaseName))->getConnection();

    $sql = "TRUNCATE TABLE urls";
    $query = self::$dbConnection->query($sql);
  }

  /**
   * @before
   */
  protected function setUp(): void {
    $this->shortUrlCode = "AhtUk37v";
    $this->long_url = "http://www.google.com?param=test";

    $sql = "TRUNCATE TABLE urls";
    $query = self::$dbConnection->query($sql);
  }

  /**
   * @after
   */
  protected function tearDown(): void {
    $this->shortUrlCode = null;
    $this->long_url = null;

    $sql = "TRUNCATE TABLE urls";
    $query = self::$dbConnection->query($sql);
  }

  /**
   * @afterClass
   */
  public static function tearDownAfterClass(): void {
    self::$dbConnection = null;
  }

  /**
   * @covers ::count
   */
  public function testCount() {
    $urlModel = new UrlModel(self::$dbConnection);
    $numRows = $urlModel->count();

    // Check that number of rows is a number
    $this->assertIsNumeric($numRows, "Counting failed");
  }

  /**
   * @covers ::add
   */
  public function testAdd() {
    $urlModel = new UrlModel(self::$dbConnection);

    // Get initial number of rows
    $numRows = $urlModel->count();

    $urlModel->add(
      $this->shortUrlCode,
      $this->long_url
    );

    // Get number of rows after the add.
    $newNumRows = $urlModel->count();

    $this->assertEquals($newNumRows, $numRows + 1, "Could not add URL");

    // Checks that duplicate short URLs (primary key) cannot be added.
    // 23000 is integrity constraint violation.
    // Thrown when a duplicate primary key is entered
    $this->expectExceptionCode('23000');
    $urlModel->add(
      $this->shortUrlCode,
      $this->long_url
    );
  }

  /**
   * @covers ::find
   */
  public function testFind() {

    $urlModel = new UrlModel(self::$dbConnection);

    // Add an example URL before searching for it
    $urlModel->add(
      $this->shortUrlCode,
      $this->long_url
    );

    $url = $urlModel->find($this->shortUrlCode);

    // Check that the long URL matches
    $this->assertNotFalse($url, "URL was not found");
    $this->assertEquals(
      $url["long_url"],
      $this->long_url,
      "Incorrect URL was found."
    );
  }

  /**
   * @covers ::update
   */
  public function testUpdate() {
    $long_url = "http://bing.com";

    $urlModel = new UrlModel(self::$dbConnection);

    // Add an example URL before updating it
    $urlModel->add(
      $this->shortUrlCode,
      $this->long_url
    );

    // Get initial number of rows
    $numRows = $urlModel->count();

    $urlModel->update(
      $this->shortUrlCode,
      $long_url
    );

    // Get number of rows after the update.
    $newNumRows = $urlModel->count();

    $this->assertEquals(
      $newNumRows,
      $numRows,
      "Update changed number of rows"
    );

    // Check that the long URL matches the updated value
    $url = $urlModel->find($this->shortUrlCode);
    $this->assertNotFalse($url, "URL was not updated");
    $this->assertEquals(
      $url["long_url"],
      $long_url,
      "URL did not update correctly"
    );
  }

  /**
   * @covers ::delete
   */
  public function testDelete() {
    $urlModel = new UrlModel(self::$dbConnection);

    // Add an example URL before deleting it
    $urlModel->add(
      $this->shortUrlCode,
      $this->long_url
    );

    // Get number of rows before deleting
    $numRows = $urlModel->count();

    $urlModel->delete($this->shortUrlCode);

    // Get number of rows after deleting
    $newNumRows = $urlModel->count();
    $this->assertEquals($newNumRows, $numRows - 1, "Could not delete URL");
  }
}
