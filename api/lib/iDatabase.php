<?php 

namespace TB;

interface iDatabase 
{
	public function __construct($dsn, $username="", $password="", $driver_options=array());
	
	public function writeRoute($route);
	
	public function readRoute($routeid);
	
	public function getTimezone($long, $lat);
}
