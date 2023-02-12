<?php
require "../src/bootstrap.php";

use ShortUrl\Controller\URLController;

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

$uriPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uriPathParts = explode('/', $uriPath);

// ##### ROUTING

// If only 1 slash
if (count($uriPathParts) == 2) {
  $urlController = new URLController(
    $_SERVER['REQUEST_URI'],
    $_SERVER["REQUEST_METHOD"]
  );
  $urlController->processRequest();
} else {
  header("HTTP/1.1 404 Not Found");
  exit();
}
