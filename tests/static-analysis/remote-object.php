<?php

namespace StaticAnalysis\RemoteObject;

use BookiesProxyManager\Factory\LazyLoadingValueHolderFactory;
use BookiesProxyManager\Factory\NullObjectFactory;
use BookiesProxyManager\Factory\RemoteObject\AdapterInterface;
use BookiesProxyManager\Factory\RemoteObjectFactory;
use BookiesProxyManager\Proxy\LazyLoadingInterface;

require_once __DIR__ . '/../../vendor/autoload.php';

class MyProxiedClass
{
    public function sayHello() : string
    {
        return 'Hello!';
    }
}

$adapter = new class implements AdapterInterface
{
    public function call(string $wrappedClass, string $method, array $params = [])
    {
        return 'ohai';
    }
};

echo (new RemoteObjectFactory($adapter))
    ->createProxy(new MyProxiedClass())
    ->sayHello();

echo (new RemoteObjectFactory($adapter))
    ->createProxy(MyProxiedClass::class)
    ->sayHello();
