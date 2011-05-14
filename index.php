<?php 

require "app/config.php";

require APP_PATH . "scanner.php";


$scanner = new SitemapXMLGenerator(array(
  "websites" => "http://www.sykohpath.com/",
  "limit"       => 100
));

// Start process
$scanner->create();

print $scanner->output();