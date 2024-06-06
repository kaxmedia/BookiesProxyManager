<?php

declare(strict_types=1);

namespace BookiesProxyManagerTest\FileLocator;

use PHPUnit\Framework\TestCase;
use BookiesProxyManager\Exception\InvalidProxyDirectoryException;
use BookiesProxyManager\FileLocator\FileLocator;

use const DIRECTORY_SEPARATOR;

/**
 * Tests for {@see \BookiesProxyManager\FileLocator\FileLocator}
 *
 * @group Coverage
 */
final class FileLocatorTest extends TestCase
{
    /**
     * @covers \BookiesProxyManager\FileLocator\FileLocator::__construct
     * @covers \BookiesProxyManager\FileLocator\FileLocator::getProxyFileName
     */
    public function testGetProxyFileName(): void
    {
        $locator = new FileLocator(__DIR__);

        self::assertSame(__DIR__ . DIRECTORY_SEPARATOR . 'FooBarBaz.php', $locator->getProxyFileName('Foo\\Bar\\Baz'));
        self::assertSame(__DIR__ . DIRECTORY_SEPARATOR . 'Foo_Bar_Baz.php', $locator->getProxyFileName('Foo_Bar_Baz'));
    }

    /**
     * @covers \BookiesProxyManager\FileLocator\FileLocator::__construct
     */
    public function testRejectsNonExistingDirectory(): void
    {
        $this->expectException(InvalidProxyDirectoryException::class);
        new FileLocator(__DIR__ . '/non-existing');
    }
}
