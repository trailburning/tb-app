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

if (isset($_SERVER['AWS_ACCESSKEY'])) {
    $container->setParameter('aws_accesskey', $_SERVER['AWS_ACCESSKEY']);
}

if (isset($_SERVER['AWS_SECRETKEY'])) {
    $container->setParameter('aws_secretkey', $_SERVER['AWS_SECRETKEY']);
}

if (isset($_SERVER['AWS_SECRETKEY'])) {
    $container->setParameter('aws_secretkey', $_SERVER['AWS_SECRETKEY']);
}

if (isset($_SERVER['MAILER_HOST'])) {
    $container->setParameter('mailer_host', $_SERVER['MAILER_HOST']);
}

if (isset($_SERVER['MAILER_USER'])) {
    $container->setParameter('mailer_user', $_SERVER['MAILER_USER']);
}

if (isset($_SERVER['MAILER_PASSWORD'])) {
    $container->setParameter('mailer_password', $_SERVER['MAILER_PASSWORD']);
}
if (isset($_SERVER['RABBIT_MQ_HOST'])) {
    $container->setParameter('rabbit_mq_host', $_SERVER['RABBIT_MQ_HOST']);
}
if (isset($_SERVER['RABBIT_MQ_USER'])) {
    $container->setParameter('rabbit_mq_user', $_SERVER['RABBIT_MQ_USER']);
}
if (isset($_SERVER['RABBIT_MQ_PASSWORD'])) {
    $container->setParameter('rabbit_mq_password', $_SERVER['RABBIT_MQ_PASSWORD']);
}
if (isset($_SERVER['RABBIT_MQ_VHOST'])) {
    $container->setParameter('rabbit_mq_vhost', $_SERVER['RABBIT_MQ_VHOST']);
}
if (isset($_SERVER['MEMCACHED_HOST'])) {
    $container->setParameter('memcached_host', $_SERVER['MEMCACHED_HOST']);
}
if (isset($_SERVER['MEMCACHED_PORT'])) {
    $container->setParameter('memcached_port', $_SERVER['MEMCACHED_PORT']);
}
if (isset($_SERVER['MEMCACHED_USERNAME'])) {
    $container->setParameter('memcached_username', $_SERVER['MEMCACHED_USERNAME']);
}
if (isset($_SERVER['MEMCACHED_PASSWORD'])) {
    $container->setParameter('memcached_password', $_SERVER['MEMCACHED_PASSWORD']);
}