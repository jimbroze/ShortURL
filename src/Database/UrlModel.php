<?php
namespace ShortUrl\Database;

/**
* A database gateway to access urls 
*/
class UrlModel
{
  private $db = null;

  public function __construct($dbConnection) {
    $this->db = $dbConnection;
  }

  /**
   * Return the number of URLs in the database
   * @access public
   * @return integer
   */
  public function count() {
    $sql = "SELECT count(1) FROM urls";

    return $this->db->query($sql)->fetchColumn();
  }

  /**
   * Return all URLs in the database
   * @access public
   * @return array<integer, Array>
   */
  public function findAll() {
    // Would likely want some limits if required.
    $sql = "SELECT * FROM urls";

    return $this->db->query($sql)->fetchAll(\PDO::FETCH_ASSOC);
  }

  /**
   * find a URL in the database and return all data
   * @access public
   * @return array<string, mixed>
   */
  public function find($shortUrlCode) {
    $sql = "SELECT * FROM urls WHERE short_url_code=?";

    $statementHandle = $this->db->prepare($sql);
    $statementHandle->execute([$shortUrlCode]);
    return $statementHandle->fetch(\PDO::FETCH_ASSOC);
  }

  /**
   * Add a new URL to the database
   * @access public
   * @param string $shortUrlCode 8 digit short URL
   * @param string $long_url full length URL (up to 2048 chars)
   * @return void
   */
  public function add($shortUrlCode, $long_url) {
    $sql = <<<SQL
      INSERT INTO urls (long_url, short_url_code)
      VALUES (:long_url, :short_url_code)
    SQL;

    $this->db->prepare($sql)->execute([
      'short_url_code' => $shortUrlCode,
      'long_url' => $long_url
    ]);
  }

  /**
   * Update a URL to the database
   * @access public
   * @param string $shortUrlCode 8 digit short URL
   * @param string $long_url full length URL (up to 2048 chars)
   * @return void
   */
  public function update($shortUrlCode, $long_url) {
    $sql = <<<SQL
      UPDATE urls
      SET long_url=:long_url
      WHERE short_url_code=:short_url_code
    SQL;

    $this->db->prepare($sql)->execute([
      'short_url_code' => $shortUrlCode,
      'long_url' => $long_url
    ]);
  }

  /**
   * Delete a URL from the database
   * @access public
   * @param string $shortUrlCode 8 digit short URL
   * @return void
   */
  public function delete($shortUrlCode) {
    $sql = "DELETE FROM urls WHERE short_url_code = ?";

    $query = $this->db->prepare($sql)->execute([$shortUrlCode]);
  }

}