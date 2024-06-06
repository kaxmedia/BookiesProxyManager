<?php

declare(strict_types=1);

namespace BookiesProxyManagerTest\ProxyGenerator\LazyLoadingValueHolder\PropertyGenerator;

use Laminas\Code\Generator\PropertyGenerator;
use BookiesProxyManager\ProxyGenerator\LazyLoadingValueHolder\PropertyGenerator\ValueHolderProperty;
use ProxyManagerTest\ProxyGenerator\PropertyGenerator\AbstractUniquePropertyNameTest;
use ReflectionClass;

/**
 * Tests for {@see \BookiesProxyManager\ProxyGenerator\LazyLoadingValueHolder\PropertyGenerator\ValueHolderProperty}
 *
 * @covers \BookiesProxyManager\ProxyGenerator\LazyLoadingValueHolder\PropertyGenerator\ValueHolderProperty
 * @group Coverage
 */
final class ValueHolderPropertyTest extends AbstractUniquePropertyNameTest
{
    protected function createProperty(): PropertyGenerator
    {
        return new ValueHolderProperty(new ReflectionClass(self::class));
    }

    /** @group #400 */
    public function testWillDocumentPropertyType(): void
    {
        $docBlock = (new ValueHolderProperty(new ReflectionClass(self::class)))->getDocBlock();

        self::assertNotNull($docBlock);
        self::assertEquals(
            <<<'PHPDOC'
/**
 * @var \ProxyManagerTest\ProxyGenerator\LazyLoadingValueHolder\PropertyGenerator\ValueHolderPropertyTest|null wrapped object, if the proxy is initialized
 */

PHPDOC
            ,
            $docBlock->generate()
        );
    }
}
