<?php

// set config params from enviroment variables if the exist (heroku)

if (isset($_SERVER['DB_DATABASE'])) {
	$container->setParameter('database_name', $_SERVER['DB_DATABASE']);
}

if (isset($_SERVER['DB_USER'])) {
	$container->setParameter('database_user', $_SERVER['DB_USER']);
}

if (isset($_SERVER['DB_HOST'])) {
	$container->setParameter('database_host', $_SERVER['DB_HOST']);
}

if (isset($_SERVER['DB_PORT'])) {
	$container->setParameter('database_port', $_SERVER['DB_PORT']);
}

if (isset($_SERVER['DB_PASSWORD'])) {
	$container->setParameter('database_password', $_SERVER['DB_PASSWORD']);
}

if (isset($_SERVER['API_HOST'])) {
	$container->setParameter('api_host', $_SERVER['API_HOST']);
}


