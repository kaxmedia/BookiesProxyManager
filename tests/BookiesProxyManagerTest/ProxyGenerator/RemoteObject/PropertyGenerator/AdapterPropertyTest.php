<?php

declare(strict_types=1);

namespace BookiesProxyManagerTest\ProxyGenerator\RemoteObject\PropertyGenerator;

use Laminas\Code\Generator\PropertyGenerator;
use BookiesProxyManager\ProxyGenerator\RemoteObject\PropertyGenerator\AdapterProperty;
use ProxyManagerTest\ProxyGenerator\PropertyGenerator\AbstractUniquePropertyNameTest;

/**
 * Tests for {@see \BookiesProxyManager\ProxyGenerator\RemoteObject\PropertyGenerator\AdapterProperty}
 *
 * @covers \BookiesProxyManager\ProxyGenerator\RemoteObject\PropertyGenerator\AdapterProperty
 * @group Coverage
 */
final class AdapterPropertyTest extends AbstractUniquePropertyNameTest
{
    protected function createProperty(): PropertyGenerator
    {
        return new AdapterProperty();
    }
}
