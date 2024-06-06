<?php

declare(strict_types=1);

namespace BookiesProxyManagerTest\ProxyGenerator\AccessInterceptor\PropertyGenerator;

use Laminas\Code\Generator\PropertyGenerator;
use BookiesProxyManager\ProxyGenerator\AccessInterceptor\PropertyGenerator\MethodSuffixInterceptors;
use ProxyManagerTest\ProxyGenerator\PropertyGenerator\AbstractUniquePropertyNameTest;

/**
 * Tests for {@see \BookiesProxyManager\ProxyGenerator\AccessInterceptor\PropertyGenerator\MethodSuffixInterceptors}
 *
 * @covers \BookiesProxyManager\ProxyGenerator\AccessInterceptor\PropertyGenerator\MethodSuffixInterceptors
 * @group Coverage
 */
final class MethodSuffixInterceptorsTest extends AbstractUniquePropertyNameTest
{
    protected function createProperty(): PropertyGenerator
    {
        return new MethodSuffixInterceptors();
    }
}
