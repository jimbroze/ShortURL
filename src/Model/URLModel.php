<?php

namespace ShortUrl\Model;

use ShortUrl\Database\DbConnector;

/**
 * A model representing a URL
 */
class URLModel {
  protected static $shortURLCodeLength = 8;

  private $db;
  private $shortUrlCode = null;
  private $longUrl = null;
  private $createdDate = null;

  public function __construct(
    string $shortUrlCode = null,
    string $longUrl = null
  ) {
    $this->db = (new DbConnector())->getConnection();
    if (!is_null($shortUrlCode)) {
      if ($this->setValidateShortURL($shortUrlCode)) {
        $this->shortUrlCode = $shortUrlCode;
      } else {
        throw new \Exception("Short URL is not valid: " . $shortUrlCode);
      }
    }

    if (!is_null($longUrl)) {
      if ($this->setValidateLongURL($longUrl)) {
        $this->longUrl = $longUrl;
      } else {
        throw new \Exception("Long URL is not valid." . $longUrl);
      }
    }
  }

  /**
   * Save a URL and create a short URL that links to it
   * @access public
   * @param string $longUrl full length URL (up to 2048 chars)
   * @return string
   */
  public function createShortUrl(string $longUrl = null): string {
    if ($this->setValidateLongURL($longUrl) == false) {
      throw new \Exception("Long URL is not valid." . $longUrl);
    }

    // Create and validate short URL code.
    // check if in database and recreate if already exists.
    do {
      $this->shortUrlCode = $this->generateRandomString();
      if (!$this->setValidateShortURL($this->shortUrlCode))
        throw new \Exception("Short URL is not valid: " . $this->shortUrlCode);
    } while ($this->findURLInDb($this->shortUrlCode) !== false);

    // Add url to database.
    $this->addURLInDb($this->shortUrlCode, $this->longUrl);

    // Get and add params to instance
    $this->getLongURL($this->shortUrlCode);
    if ($this->longUrl == false) {
      throw new \Exception("URL was not correctly added to database.");
    }

    return $this->shortUrlCode;
  }

  /**
   * Use a short URL to retrieve a stored (long) URL
   * @access public
   * @param string $shortUrlCode 8 digit short URL
   * @return string
   */
  public function getLongURL(string $shortUrlCode = null): string {
    if ($this->setValidateShortURL($shortUrlCode) == false) {
      throw new \Exception("Short URL is not valid: " . $shortUrlCode);
    }

    // Get long URL from database.
    // Returns false (without updating instance variables) if not found.
    $urlData = $this->findURLInDb($this->shortUrlCode);
    if ($urlData == false) {
      return false;
    }
    $this->longUrl = $urlData["long_url"];
    $this->createdDate = $urlData["created_date"];

    return $this->longUrl;
  }

  /**
   * Set the object's short URL and ensure it is valid.
   * @access public
   * @param string $shortUrlCode 8 digit short URL
   * @return bool
   */
  public function setValidateShortURL(string $shortUrlCode = null): bool {
    if (!is_null($shortUrlCode)) {
      $this->shortUrlCode = $shortUrlCode;
    } elseif (is_null($this->shortUrlCode)) {
      throw new \Exception("No short URL code provided.");
    }

    if (strlen($this->shortUrlCode) != 8) {
      return false;
    }
    return true;
  }

  /**
   * Set the object's long URL and ensure it is valid.
   * @access public
   * @param string $longUrl full length URL (up to 2048 chars)
   * @return bool
   */
  public function setValidateLongURL(string $longUrl = null): bool {
    if (!is_null($longUrl)) {
      $this->longUrl = $longUrl;
    } elseif (is_null($this->longUrl)) {
      throw new \Exception("No long URL provided.");
    }

    if (strlen($this->longUrl) > 2048) {
      return false;
    }

    if (is_null(parse_url($this->longUrl, PHP_URL_SCHEME)))
      $this->longUrl = "http://" . $this->longUrl;

    return true;
  }

  /**
   * Generate a random hex string of specific length.
   * @access private
   * @param int $length length of string to generate
   * @return string
   */
  private function generateRandomString(int $length = null): string {
    if (is_null($length))
      $length = self::$shortURLCodeLength;
    $randomString = md5(uniqid());
    $trimmedRandomString = substr($randomString, -1 * ($length));

    return $trimmedRandomString;
  }

  /**
   * Return the number of URLs in the database
   * @access private
   * @return integer
   */
  private function countURLsInDb(): int {
    $sql = "SELECT count(1) FROM urls";

    return $this->db->query($sql)->fetchColumn();
  }

  /**
   * Return all URLs in the database
   * @access private
   * @return array<integer, Array>
   */
  private function findAllURLsInDb(): array {
    // Would likely want some limits if function is required.
    $sql = "SELECT * FROM urls";

    return $this->db->query($sql)->fetchAll(\PDO::FETCH_ASSOC);
  }

  /**
   * find a URL in the database and return all data
   * @access private
   * @param string $shortUrlCode 8 digit short URL
   * @return array<string, mixed>
   */
  private function findURLInDb(string $shortUrlCode): array|bool {
    $sql = "SELECT * FROM urls WHERE short_url_code=?";

    $statementHandle = $this->db->prepare($sql);
    $statementHandle->execute([$shortUrlCode]);
    return $statementHandle->fetch(\PDO::FETCH_ASSOC);
  }

  /**
   * Add a new URL to the database
   * @access private
   * @param string $shortUrlCode 8 digit short URL
   * @param string $longUrl full length URL (up to 2048 chars)
   * @return void
   */
  private function addURLInDb(string $shortUrlCode, string $longUrl): void {
    $sql = <<<SQL
      INSERT INTO urls (long_url, short_url_code)
      VALUES (:long_url, :short_url_code)
    SQL;

    $this->db->prepare($sql)->execute([
      'short_url_code' => $shortUrlCode,
      'long_url' => $longUrl
    ]);
  }

  /**
   * Update a URL to the database
   * @private public
   * @param string $shortUrlCode 8 digit short URL
   * @param string $longUrl full length URL (up to 2048 chars)
   * @return void
   */
  private function updateURLInDb(string $shortUrlCode, string $longUrl): void {
    $sql = <<<SQL
      UPDATE urls
      SET long_url=:long_url
      WHERE short_url_code=:short_url_code
    SQL;

    $this->db->prepare($sql)->execute([
      'short_url_code' => $shortUrlCode,
      'long_url' => $longUrl
    ]);
  }

  /**
   * Delete a URL from the database
   * @access private
   * @param string $shortUrlCode 8 digit short URL
   * @return void
   */
  private function deleteURLInDb(string $shortUrlCode): void {
    $sql = "DELETE FROM urls WHERE short_url_code = ?";

    $this->db->prepare($sql)->execute([$shortUrlCode]);
  }
}
