<?php

namespace ShortUrl\Model;

use ShortUrl\Database\DbConnector;
use ShortUrl\Lib\URLShortener;


/**
 * A model representing a URL
 */
class URLModel {
  private $db;
  private $shortUrlCode = null;
  private $longUrl = null;
  private $createdDate = null;

  public function __construct($shortUrlCode = null, $longUrl = null) {
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

  public function createShortUrl($longUrl = null) {
    if ($this->setValidateLongURL($longUrl) == false) {
      throw new \Exception("Long URL is not valid." . $longUrl);
    }

    // Create and validate short URL code.
    // check if in database and recreate if already exists.
    do {
      $this->shortUrlCode = (new URLShortener($this->longUrl))->shortenUrl();
      if (!$this->setValidateShortURL($this->shortUrlCode))
        throw new \Exception("Short URL is not valid: " . $this->shortUrlCode);
    } while ($this->findURL($this->shortUrlCode) !== false);

    // Add url to database.
    $this->addURL($this->shortUrlCode, $this->longUrl);

    // Get and add params to instance
    $this->getLongURL($this->shortUrlCode);
    if ($this->longUrl == false) {
      throw new \Exception("URL was not correctly added to database.");
    }

    return $this->shortUrlCode;
  }

  public function getLongURL($shortUrlCode = null) {
    if ($this->setValidateShortURL($shortUrlCode) == false) {
      throw new \Exception("Short URL is not valid: " . $shortUrlCode);
    }

    // Get long URL from database.
    // Returns false (without updating instance variables) if not found.
    $urlData = $this->findURL($this->shortUrlCode);
    if ($urlData == false) {
      return false;
    }
    $this->longUrl = $urlData["long_url"];
    $this->createdDate = $urlData["created_date"];

    return $this->longUrl;
  }

  public function setValidateShortURL($shortUrlCode = null) {
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

  public function setValidateLongURL($longUrl = null) {
    if (!is_null($longUrl)) {
      $this->longUrl = $longUrl;
    } elseif (is_null($this->longUrl)) {
      throw new \Exception("No long URL provided.");
    }

    if (strlen($this->longUrl) > 2048) {
      return false;
    }
    return true;
  }

  /**
   * Return the number of URLs in the database
   * @access private
   * @return integer
   */
  private function countURLs() {
    $sql = "SELECT count(1) FROM urls";

    return $this->db->query($sql)->fetchColumn();
  }

  /**
   * Return all URLs in the database
   * @access private
   * @return array<integer, Array>
   */
  private function findAllURLs() {
    // Would likely want some limits if required.
    $sql = "SELECT * FROM urls";

    return $this->db->query($sql)->fetchAll(\PDO::FETCH_ASSOC);
  }

  /**
   * find a URL in the database and return all data
   * @access private
   * @return array<string, mixed>
   */
  private function findURL($shortUrlCode) {
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
  private function addURL($shortUrlCode, $longUrl) {
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
  private function updateURL($shortUrlCode, $longUrl) {
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
  private function deleteURL($shortUrlCode) {
    $sql = "DELETE FROM urls WHERE short_url_code = ?";

    $this->db->prepare($sql)->execute([$shortUrlCode]);
  }
}
