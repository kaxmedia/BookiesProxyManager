<?php

declare(strict_types=1);

namespace BookiesProxyManagerTest\ProxyGenerator;

use BookiesProxyManager\Proxy\AccessInterceptorInterface;
use BookiesProxyManager\Proxy\AccessInterceptorValueHolderInterface;
use BookiesProxyManager\Proxy\ValueHolderInterface;
use BookiesProxyManager\ProxyGenerator\AccessInterceptorValueHolderGenerator;
use BookiesProxyManager\ProxyGenerator\ProxyGeneratorInterface;

/**
 * Tests for {@see \BookiesProxyManager\ProxyGenerator\AccessInterceptorValueHolderGenerator}
 *
 * @covers \BookiesProxyManager\ProxyGenerator\AccessInterceptorValueHolderGenerator
 * @group Coverage
 */
final class AccessInterceptorValueHolderTest extends AbstractProxyGeneratorTest
{
    protected function getProxyGenerator(): ProxyGeneratorInterface
    {
        return new AccessInterceptorValueHolderGenerator();
    }

    /**
     * {@inheritDoc}
     */
    protected function getExpectedImplementedInterfaces(): array
    {
        return [
            AccessInterceptorValueHolderInterface::class,
            AccessInterceptorInterface::class,
            ValueHolderInterface::class,
        ];
    }
}
