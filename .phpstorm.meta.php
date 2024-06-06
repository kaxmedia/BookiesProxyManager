<?php

namespace PHPSTORM_META {

    override(
        \BookiesProxyManager\Factory\AccessInterceptorScopeLocalizerFactory::createProxy(0),
        map([
            '@&ProxyManager\Proxy\AccessInterceptorInterface',
        ])
    );

    override(
        \BookiesProxyManager\Factory\AccessInterceptorValueHolderFactory::createProxy(0),
        map([
            '@&ProxyManager\Proxy\AccessInterceptorValueHolderInterface',
        ])
    );

    override(
        \BookiesProxyManager\Factory\LazyLoadingGhostFactory::createProxy(0),
        map([
            '@&ProxyManager\Proxy\GhostObjectInterface',
        ])
    );

    override(
        \BookiesProxyManager\Factory\LazyLoadingValueHolderFactory::createProxy(0),
        map([
            '@&ProxyManager\Proxy\VirtualProxyInterface',
        ])
    );

    override(
        \BookiesProxyManager\Factory\NullObjectFactory::createProxy(0),
        map([
            '@&ProxyManager\Proxy\NullObjectInterface',
        ])
    );

    override(
        \BookiesProxyManager\Factory\RemoteObjectFactory::createProxy(0),
        map([
            '@&ProxyManager\Proxy\RemoteObjectInterface',
        ])
    );
}
