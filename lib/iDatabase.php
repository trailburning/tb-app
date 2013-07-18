<?php 

namespace TB;

interface iDatabase {

	public function __construct($dsn, $username="", $password="", $driver_options=array());
	public function importRoute($route);
	public function exportRoute($routeid);
}

?>
