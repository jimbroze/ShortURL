<?php

use PHPUnit\Framework\TestCase;

use ShortUrl\Util\PHPUnitUtil;
use ShortUrl\Database\DbConnector;
use ShortUrl\Model\URLModel;

/**
 * @coversDefaultClass ShortUrl\Model\URLModel
 */
final class URLModelTest extends TestCase {
  private static $dbConnection;
  private $shortUrlCode;
  private $longUrl;

  /**
   * @beforeClass
   */
  public static function setUpBeforeClass(): void {
    $databaseName = $_ENV['MYSQL_DATABASE_TEST'];
    if (is_null($databaseName)) {
      exit("Test database environment variable is not set.");
    } elseif ($_ENV["MYSQL_DATABASE"] !== $databaseName) {
      exit("Database not set to test");
    }

    self::$dbConnection = (new DbConnector())->getConnection();
  }

  /**
   * @before
   */
  protected function setUp(): void {
    $this->shortUrlCode = "AhtUk37v";
    $this->longUrl = "http://www.google.com?param=test";

    $sql = "TRUNCATE TABLE urls";
    self::$dbConnection->query($sql);
  }

  /**
   * @after
   */
  protected function tearDown(): void {
    $this->shortUrlCode = null;
    $this->longUrl = null;

    $sql = "TRUNCATE TABLE urls";
    self::$dbConnection->query($sql);
  }

  /**
   * @afterClass
   */
  public static function tearDownAfterClass(): void {
    self::$dbConnection = null;
  }

  /**
   * @covers ::countURLs
   */
  public function testCount() {
    $urlModel = new URLModel();
    $numRows = PHPUnitUtil::callMethod($urlModel, 'countURLs', array());

    // Check that number of rows is a number
    $this->assertIsNumeric($numRows, "Counting failed");
  }

  /**
   * @covers ::addURL
   */
  public function testAdd() {
    $urlModel = new URLModel();

    // Get initial number of rows
    $numRows = PHPUnitUtil::callMethod($urlModel, 'countURLs', array());

    PHPUnitUtil::callMethod(
      $urlModel,
      'addURL',
      array($this->shortUrlCode, $this->longUrl)
    );

    // Get number of rows after the add.
    $newNumRows = PHPUnitUtil::callMethod($urlModel, 'countURLs', array());

    $this->assertEquals($newNumRows, $numRows + 1, "Could not add URL");

    // Checks that duplicate short URLs (primary key) cannot be added.
    // 23000 is integrity constraint violation.
    // Thrown when a duplicate primary key is entered
    $this->expectExceptionCode('23000');
    PHPUnitUtil::callMethod(
      $urlModel,
      'addURL',
      array($this->shortUrlCode, $this->longUrl)
    );
  }

  /**
   * @covers ::findURL
   */
  public function testFind() {

    $urlModel = new URLModel();

    // Add an example URL before searching for it
    PHPUnitUtil::callMethod(
      $urlModel,
      'addURL',
      array($this->shortUrlCode, $this->longUrl)
    );

    $url = PHPUnitUtil::callMethod(
      $urlModel,
      'findURL',
      array($this->shortUrlCode)
    );

    // Check that the long URL matches
    $this->assertNotFalse($url, "URL was not found");
    $this->assertEquals(
      $url["long_url"],
      $this->longUrl,
      "Incorrect URL was found."
    );
  }

  /**
   * @covers ::updateURL
   */
  public function testUpdate() {
    $longUrl = "http://bing.com";

    $urlModel = new URLModel();

    // Add an example URL before updating it
    PHPUnitUtil::callMethod(
      $urlModel,
      'addURL',
      array($this->shortUrlCode, $this->longUrl)
    );

    // Get initial number of rows
    $numRows = PHPUnitUtil::callMethod($urlModel, 'countURLs', array());

    PHPUnitUtil::callMethod(
      $urlModel,
      'updateURL',
      array($this->shortUrlCode, $longUrl)
    );

    // Get number of rows after the update.
    $newNumRows = PHPUnitUtil::callMethod($urlModel, 'countURLs', array());

    $this->assertEquals(
      $newNumRows,
      $numRows,
      "Update changed number of rows"
    );

    // Check that the long URL matches the updated value
    $url = PHPUnitUtil::callMethod(
      $urlModel,
      'findURL',
      array($this->shortUrlCode)
    );
    $this->assertNotFalse($url, "URL was not updated");
    $this->assertEquals(
      $url["long_url"],
      $longUrl,
      "URL did not update correctly"
    );
  }

  /**
   * @covers ::deleteURL
   */
  public function testDelete() {
    $urlModel = new URLModel();

    // Add an example URL before deleting it
    PHPUnitUtil::callMethod(
      $urlModel,
      'addURL',
      array($this->shortUrlCode, $this->longUrl)
    );

    // Get number of rows before deleting
    $numRows = PHPUnitUtil::callMethod($urlModel, 'countURLs', array());

    PHPUnitUtil::callMethod(
      $urlModel,
      'deleteURL',
      array($this->shortUrlCode)
    );


    // Get number of rows after deleting
    $newNumRows = PHPUnitUtil::callMethod($urlModel, 'countURLs', array());
    $this->assertEquals($newNumRows, $numRows - 1, "Could not delete URL");
  }

  /**
   * @covers ::setValidateShortURL
   */
  public function testValidateShortURL() {
    $shortUrlCode = "tH5I3k62";
    $invalidShortUrl = "Ak3";

    $urlModel = new URLModel();

    $valid = $urlModel->setValidateShortURL($shortUrlCode);
    $this->assertTrue($valid, "Short URL code validation failed");

    // $this->expectException(\Exception::class);
    // Invalid short URL.
    $valid = $urlModel->setValidateShortURL($invalidShortUrl);
    $this->assertFalse($valid, "Short URL code validation failed");
  }
}
