<?php

declare(strict_types=1);

namespace BookiesProxyManagerTest\ProxyGenerator\LazyLoadingValueHolder\PropertyGenerator;

use Laminas\Code\Generator\PropertyGenerator;
use BookiesProxyManager\ProxyGenerator\LazyLoadingValueHolder\PropertyGenerator\InitializerProperty;
use ProxyManagerTest\ProxyGenerator\PropertyGenerator\AbstractUniquePropertyNameTest;

/**
 * Tests for {@see \BookiesProxyManager\ProxyGenerator\LazyLoadingValueHolder\PropertyGenerator\InitializerProperty}
 *
 * @covers \BookiesProxyManager\ProxyGenerator\LazyLoadingValueHolder\PropertyGenerator\InitializerProperty
 * @group Coverage
 */
final class InitializerPropertyTest extends AbstractUniquePropertyNameTest
{
    protected function createProperty(): PropertyGenerator
    {
        return new InitializerProperty();
    }
}
