<?php

declare(strict_types=1);

namespace BookiesProxyManagerTest\Exception;

use PHPUnit\Framework\TestCase;
use BookiesProxyManager\Exception\DisabledMethodException;

/**
 * Tests for {@see \BookiesProxyManager\Exception\DisabledMethodException}
 *
 * @covers \BookiesProxyManager\Exception\DisabledMethodException
 * @group Coverage
 */
final class DisabledMethodExceptionTest extends TestCase
{
    /**
     * @covers \BookiesProxyManager\Exception\DisabledMethodException::disabledMethod
     */
    public function testProxyDirectoryNotFound(): void
    {
        $exception = DisabledMethodException::disabledMethod('foo::bar');

        self::assertSame('Method "foo::bar" is forcefully disabled', $exception->getMessage());
    }
}
