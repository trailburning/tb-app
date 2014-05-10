<?php 

use Symfony\Component\DependencyInjection\Definition;

if (class_exists('\\Memcached')) {
    // Use Memcached session handler when tho memcached module is installed
    $memcached = $container->setDefinition('session.memcached', new Definition(
        'Memcached',
        ['%session_prefix%']
    ))->addMethodCall('addServer', [
        '%memcached_host%',
        '%memcached_port%',
    ]);
    
    $memcached-addMethodCall('setOption', [
        \Memcached::OPT_BINARY_PROTOCOL,
        true,
    ]);

    // Only set SASL authenfification when parameters are set
    if ($container->getParameter('memcached_username') != '') {
        $memcached->addMethodCall('setSaslAuthData', [
            '%memcached_username%',
            '%memcached_password%',
        ]);
    }

    $container->setDefinition('session.handler.memcached', new Definition(
        'Symfony\Component\HttpFoundation\Session\Storage\Handler\MemcachedSessionHandler',
        [$memcached, [ 'prefix' => '%session_prefix%', 'expiretime' => '%session_expire%' ]]
    ));
    
    $container->loadFromExtension('framework', [
        'session' => [
            'handler_id' => 'session.handler.memcached'
        ],
    ]);
} else {
    // Otherwise use the default session handler
    $container->loadFromExtension('framework', [
        'session' => null
    ]);
}


