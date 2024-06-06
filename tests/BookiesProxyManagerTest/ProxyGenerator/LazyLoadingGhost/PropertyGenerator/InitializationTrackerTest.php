<?php

declare(strict_types=1);

namespace BookiesProxyManagerTest\ProxyGenerator\LazyLoadingGhost\PropertyGenerator;

use Laminas\Code\Generator\PropertyGenerator;
use BookiesProxyManager\ProxyGenerator\LazyLoadingGhost\PropertyGenerator\InitializationTracker;
use ProxyManagerTest\ProxyGenerator\PropertyGenerator\AbstractUniquePropertyNameTest;

/**
 * Tests for {@see \BookiesProxyManager\ProxyGenerator\LazyLoadingGhost\PropertyGenerator\InitializationTracker}
 *
 * @covers \BookiesProxyManager\ProxyGenerator\LazyLoadingGhost\PropertyGenerator\InitializationTracker
 * @group Coverage
 */
final class InitializationTrackerTest extends AbstractUniquePropertyNameTest
{
    protected function createProperty(): PropertyGenerator
    {
        return new InitializationTracker();
    }

    public function testInitializationFlagIsFalseByDefault(): void
    {
        $defaultValue = $this->createProperty()
            ->getDefaultValue();

        self::assertNotNull($defaultValue);
        self::assertFalse($defaultValue->getValue());
    }
}
