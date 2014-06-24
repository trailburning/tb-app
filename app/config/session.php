<?php 

use Symfony\Component\DependencyInjection\Definition;

if (class_exists('\\Memcached')) {
    // Use Memcached session handler when the memcached module is installed
    $memcached = $container->setDefinition('session.memcached', new Definition(
        'Memcached',
        ['%session_prefix%']
    ))->addMethodCall('addServer', [
        '%memcached_host%',
        '%memcached_port%',
    ]);

    // Only set SASL authenfification when parameters are set (local Memcached may not support SASL authenfification) 
    if ($container->getParameter('memcached_username') != '') {        
        $memcached->addMethodCall('setOption', [
            \Memcached::OPT_BINARY_PROTOCOL,
            true,
        ])->addMethodCall('setOption', [
            \Memcached::OPT_AUTO_EJECT_HOSTS,
            true,
        ])->addMethodCall('setOption', [
            \Memcached::OPT_NO_BLOCK,
            true,
        ])->addMethodCall('setSaslAuthData', [
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
    // Otherwise use the default session handler (filesystem)
    $container->loadFromExtension('framework', [
        'session' => null
    ]);
}


