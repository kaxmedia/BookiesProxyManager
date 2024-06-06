<?php

declare(strict_types=1);

namespace BookiesProxyManagerTest\ProxyGenerator;

use Laminas\Code\Generator\ClassGenerator;
use BookiesProxyManager\Exception\InvalidProxiedClassException;
use BookiesProxyManager\Exception\UnsupportedProxiedClassException;
use BookiesProxyManager\Proxy\AccessInterceptorInterface;
use BookiesProxyManager\ProxyGenerator\AccessInterceptorScopeLocalizerGenerator;
use BookiesProxyManager\ProxyGenerator\ProxyGeneratorInterface;
use BookiesProxyManagerTestAsset\BaseInterface;
use BookiesProxyManagerTestAsset\ClassWithMixedTypedProperties;
use ReflectionClass;

/**
 * Tests for {@see \BookiesProxyManager\ProxyGenerator\AccessInterceptorScopeLocalizerGenerator}
 *
 * @covers \BookiesProxyManager\ProxyGenerator\AccessInterceptorScopeLocalizerGenerator
 * @group Coverage
 */
final class AccessInterceptorScopeLocalizerTest extends AbstractProxyGeneratorTest
{
    /**
     * @dataProvider getTestedImplementations
     *
     * {@inheritDoc}
     */
    public function testGeneratesValidCode(string $className): void
    {
        $reflectionClass = new ReflectionClass($className);

        if ($reflectionClass->isInterface()) {
            // @todo interfaces *may* be proxied by deferring property localization to the constructor (no hardcoding)
            $this->expectException(InvalidProxiedClassException::class);
        }

        if ($reflectionClass->getName() === ClassWithMixedTypedProperties::class) {
            $this->expectException(UnsupportedProxiedClassException::class);
        }

        parent::testGeneratesValidCode($className);
    }

    public function testWillRejectInterfaces(): void
    {
        $this->expectException(InvalidProxiedClassException::class);

        $this
            ->getProxyGenerator()
            ->generate(new ReflectionClass(BaseInterface::class), new ClassGenerator());
    }

    protected function getProxyGenerator(): ProxyGeneratorInterface
    {
        return new AccessInterceptorScopeLocalizerGenerator();
    }

    /**
     * {@inheritDoc}
     */
    protected function getExpectedImplementedInterfaces(): array
    {
        return [AccessInterceptorInterface::class];
    }
}
