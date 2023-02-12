<?php

namespace ShortUrl\Controller;

use ShortUrl\Model\URLModel;

/**
 * "/" or "/shorten"
 */
class URLController {
  private $requestMethod;
  private $fullUri;

  public function __construct($fullUri, $requestMethod) {
    $this->fullUri = $fullUri;
    $this->requestMethod = $requestMethod;
  }

  public function processRequest() {
    switch ($this->requestMethod) {
      case 'GET':
        $uriPath = parse_url($this->fullUri, PHP_URL_PATH);
        if ($uriPath == "/shorten") {
          $response = $this->processLongURL(
            parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY)
          );
        } else {
          $response = $this->processShortURL(trim($uriPath, "/"));
        };
        break;
      default:
        $response = $this->notFoundResponse();
        break;
    }
    header($response['status_code_header']);

    if (array_key_exists("redirect", $response)) {
      header("Location: " . $response['redirect']);
    }
    if (array_key_exists("content_type", $response)) {
      header($response['content_type']);
    }
    if ($response['body']) {
      echo $response['body'];
    }
  }

  private function processLongURL($queryString) {
    // Check for empty query
    if (is_null($queryString))
      return $this->notFoundResponse();

    // Get long URL from URL query parameter
    parse_str($queryString, $query);
    if (!array_key_exists("url", $query))
      return $this->notFoundResponse();

    $longUrl = $query["url"];

    $urlModel = new URLModel();
    if (!$urlModel->setValidateLongURL($longUrl)) {
      return $this->unprocessableEntityResponse();
    }
    $shortUrlCode = $urlModel->createShortUrl();

    // Return short url to user
    $response['status_code_header'] = 'HTTP/1.1 200 OK';
    $response['content_type'] = 'Content-Type:text/plain; charset=UTF-8';
    $response['body'] = json_encode($shortUrlCode);
    return $response;
  }

  private function processShortURL($shortUrlCode) {
    // Check if URL matches requirements
    $urlModel = new URLModel();
    if (!$urlModel->setValidateShortURL($shortUrlCode)) {
      return $this->unprocessableEntityResponse();
    }

    // Get long URL
    $longUrl = $urlModel->getLongURL();

    if (!$longUrl) {
      return $this->notFoundResponse();
    }

    // Return long URL to user
    $response['status_code_header'] = 'HTTP/1.1 302 Found';
    $response['body'] = json_encode($longUrl);
    $response['redirect'] = $longUrl;
    return $response;
  }

  private function unprocessableEntityResponse() {
    $response['status_code_header'] = 'HTTP/1.1 422 Unprocessable Entity';
    $response['body'] = json_encode([
      'error' => 'Invalid input'
    ]);
    return $response;
  }

  private function notFoundResponse() {
    $response['status_code_header'] = 'HTTP/1.1 404 Not Found';
    $response['body'] = null;
    return $response;
  }
}
