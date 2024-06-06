<?php

declare(strict_types=1);

namespace BookiesProxyManagerTest\ProxyGenerator;

use BookiesProxyManager\Proxy\VirtualProxyInterface;
use BookiesProxyManager\ProxyGenerator\LazyLoadingValueHolderGenerator;
use BookiesProxyManager\ProxyGenerator\ProxyGeneratorInterface;

/**
 * Tests for {@see \BookiesProxyManager\ProxyGenerator\LazyLoadingValueHolderGenerator}
 *
 * @covers \BookiesProxyManager\ProxyGenerator\LazyLoadingValueHolderGenerator
 * @group Coverage
 */
final class LazyLoadingValueHolderGeneratorTest extends AbstractProxyGeneratorTest
{
    protected function getProxyGenerator(): ProxyGeneratorInterface
    {
        return new LazyLoadingValueHolderGenerator();
    }

    /**
     * {@inheritDoc}
     */
    protected function getExpectedImplementedInterfaces(): array
    {
        return [VirtualProxyInterface::class];
    }
}
