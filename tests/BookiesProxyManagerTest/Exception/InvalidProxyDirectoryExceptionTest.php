<?php

declare(strict_types=1);

namespace BookiesProxyManagerTest\Exception;

use PHPUnit\Framework\TestCase;
use BookiesProxyManager\Exception\InvalidProxyDirectoryException;

/**
 * Tests for {@see \BookiesProxyManager\Exception\InvalidProxyDirectoryException}
 *
 * @covers \BookiesProxyManager\Exception\InvalidProxyDirectoryException
 * @group Coverage
 */
final class InvalidProxyDirectoryExceptionTest extends TestCase
{
    /**
     * @covers \BookiesProxyManager\Exception\InvalidProxyDirectoryException::proxyDirectoryNotFound
     */
    public function testProxyDirectoryNotFound(): void
    {
        $exception = InvalidProxyDirectoryException::proxyDirectoryNotFound('foo/bar');

        self::assertSame('Provided directory "foo/bar" does not exist', $exception->getMessage());
    }
}
