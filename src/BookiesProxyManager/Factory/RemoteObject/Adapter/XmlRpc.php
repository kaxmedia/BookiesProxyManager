<?php

declare(strict_types=1);

namespace BookiesProxyManager\Factory\RemoteObject\Adapter;

/**
 * Remote Object XML RPC adapter
 */
class XmlRpc extends BaseAdapter
{
    protected function getServiceName(string $wrappedClass, string $method): string
    {
        return $wrappedClass . '.' . $method;
    }
}
