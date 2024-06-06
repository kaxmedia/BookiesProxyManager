<?php

declare(strict_types=1);

namespace BookiesProxyManager\Factory\RemoteObject\Adapter;

/**
 * Remote Object SOAP adapter
 */
class Soap extends BaseAdapter
{
    protected function getServiceName(string $wrappedClass, string $method): string
    {
        return $method;
    }
}
