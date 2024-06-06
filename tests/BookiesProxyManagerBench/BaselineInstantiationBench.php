<?php

declare(strict_types=1);

namespace BookiesProxyManagerBench;

use BookiesProxyManagerTestAsset\ClassWithMixedProperties;
use BookiesProxyManagerTestAsset\ClassWithPrivateProperties;
use BookiesProxyManagerTestAsset\ClassWithProtectedProperties;
use BookiesProxyManagerTestAsset\ClassWithPublicProperties;
use BookiesProxyManagerTestAsset\EmptyClass;

/**
 * Benchmark that provides baseline results for simple object instantiation
 */
final class BaselineInstantiationBench
{
    public function benchInstantiationOfEmptyObject(): void
    {
        new EmptyClass();
    }

    public function benchInstantiationOfObjectWithPrivateProperties(): void
    {
        new ClassWithPrivateProperties();
    }

    public function benchInstantiationOfObjectWithProtectedProperties(): void
    {
        new ClassWithProtectedProperties();
    }

    public function benchInstantiationOfObjectWithPublicProperties(): void
    {
        new ClassWithPublicProperties();
    }

    public function benchInstantiationOfObjectWithMixedProperties(): void
    {
        new ClassWithMixedProperties();
    }
}
