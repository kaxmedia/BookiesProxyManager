<?php

declare(strict_types=1);

namespace BookiesProxyManagerTest\ProxyGenerator\LazyLoadingGhost\PropertyGenerator;

use Laminas\Code\Generator\PropertyGenerator;
use BookiesProxyManager\ProxyGenerator\LazyLoadingGhost\PropertyGenerator\InitializerProperty;
use ProxyManagerTest\ProxyGenerator\PropertyGenerator\AbstractUniquePropertyNameTest;

/**
 * Tests for {@see \BookiesProxyManager\ProxyGenerator\LazyLoadingGhost\PropertyGenerator\InitializerProperty}
 *
 * @covers \BookiesProxyManager\ProxyGenerator\LazyLoadingGhost\PropertyGenerator\InitializerProperty
 * @group Coverage
 */
final class InitializerPropertyTest extends AbstractUniquePropertyNameTest
{
    protected function createProperty(): PropertyGenerator
    {
        return new InitializerProperty();
    }
}
