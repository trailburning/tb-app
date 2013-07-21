<?php

class RouteTest extends PHPUnit_Framework_TestCase {


  public function testsetTag() {
    $this->setTag("tag", "value");
    $this->assertEquals($tags["tag"], $value);
  }

}




?>
