<?php

require_once 'Route.php';

class RouteTest extends PHPUnit_Framework_TestCase {
  protected $route;

  protected function setUp() {
    $this->route= new Route();
  }

  public function testsetTag() {
    $this->route->setTag("tag", "value");
    $tags = $this->route->getTags();
    $this->assertEquals($tags["tag"], "value");
  }

}




?>
