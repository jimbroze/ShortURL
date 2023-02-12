<?php

use PHPUnit\Framework\TestCase;

use ShortUrl\Util\PHPUnitUtil;
use ShortUrl\Database\DbConnector;
use ShortUrl\Controller\URLController;
use ShortUrl\Model\URLModel;

/**
 * @coversDefaultClass ShortUrl\Controller\URLController
 */
final class URLControllerTest extends TestCase {
  private static $dbConnection;

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
    $sql = "TRUNCATE TABLE urls";
    self::$dbConnection->query($sql);
  }

  /**
   * @after
   */
  protected function tearDown(): void {
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
   * @covers ::processLongURL
   */
  public function testProcessLongURL() {
    $longUrl = "http://www.google.com?param=test";
    $goodQueryString = "url=" . $longUrl;
    $badQueryString = "ul=" . $longUrl;

    $urlController = new URLController("", "GET");

    // Correct query param
    $response = PHPUnitUtil::callMethod(
      $urlController,
      'processLongURL',
      array($goodQueryString)
    );
    // Check response for correct query is 200 OK
    $this->assertEquals(
      $response['status_code_header'],
      "HTTP/1.1 200 OK",
      "Response header is not correct"
    );
    // Check short URL matches
    $this->assertIsString(
      json_decode($response['body']),
      "Returned response body is not a string"
    );

    // Incorrect query param
    $response = PHPUnitUtil::callMethod(
      $urlController,
      'processLongURL',
      array($badQueryString)
    );
    // Check response for incorrect query is 404 Not Found
    $this->assertEquals(
      $response['status_code_header'],
      "HTTP/1.1 404 Not Found",
      "Response header is not correct"
    );
  }

  /**
   * @covers ::processShortURL
   */
  public function testProcessShortURL() {
    $shortUrlCode = "tH5I3k62";
    $longUrl = "http://www.google.com?param=testing";
    $incorrectShortUrl = "Ob7m3Be3";
    $invalidShortUrl = "Ak3";

    $urlController = new URLController("", "GET");

    $urlModel = new URLModel($shortUrlCode, $longUrl);
    // Add url to database before getting.
    PHPUnitUtil::callMethod(
      $urlModel,
      'addURL',
      array($shortUrlCode, $longUrl)
    );

    // Correct query param
    $response = PHPUnitUtil::callMethod(
      $urlController,
      'processShortURL',
      array($shortUrlCode)
    );
    // Check response for correct query is 302 Found
    $this->assertEquals(
      $response['status_code_header'],
      "HTTP/1.1 302 Found"
    );
    // Check long URL matches
    $this->assertEquals(
      json_decode($response['body']),
      $longUrl
    );

    // Incorrect short URL Code.
    $response = PHPUnitUtil::callMethod(
      $urlController,
      'processShortURL',
      array($incorrectShortUrl)
    );
    // Check response for incorrect URL code is 404 Not Found
    $this->assertEquals(
      $response['status_code_header'],
      "HTTP/1.1 404 Not Found"
    );

    // Invalid short URL Code.
    $response = PHPUnitUtil::callMethod(
      $urlController,
      'processShortURL',
      array($invalidShortUrl)
    );
    // Check response for incorrect URL code is 422 Unprocessable Entity
    $this->assertEquals(
      $response['status_code_header'],
      "HTTP/1.1 422 Unprocessable Entity"
    );
  }
}
