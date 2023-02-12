<?php

namespace ShortUrl\Lib;

/**
 * 
 */
class URLShortener {

  private $long_url;
  private $shortUrlCode;

  public function __construct($long_url) {
    $this->long_url = $long_url;
    $this->shortUrlCode = "AAAAAAAA";
  }

  public function shortenUrl() {
    return $this->shortUrlCode;
  }
}
