<?php

require_once 'Route.php';
require_once 'RoutePoint.php';

class RouteTest extends PHPUnit_Framework_TestCase {

  protected $route;

  protected function setUp() {
    $this->route = new \TB\Route();
  }

  public function testsetTag() {
    $this->route->setTag("tag", "value");
    $tags = $this->route->getTags();
    $this->assertEquals($tags["tag"], "value");
    echo curl_get('http://www.google.de');
  }

}

?>
