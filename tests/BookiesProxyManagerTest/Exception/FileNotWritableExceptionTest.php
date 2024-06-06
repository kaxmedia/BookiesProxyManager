<?php

declare(strict_types=1);

namespace BookiesProxyManagerTest\Exception;

use PHPUnit\Framework\TestCase;
use BookiesProxyManager\Exception\FileNotWritableException;
use Webimpress\SafeWriter\Exception\ExceptionInterface as FileWriterException;

/**
 * Tests for {@see \BookiesProxyManager\Exception\FileNotWritableException}
 *
 * @covers \BookiesProxyManager\Exception\FileNotWritableException
 * @group Coverage
 */
final class FileNotWritableExceptionTest extends TestCase
{
    public function testFromPrevious(): void
    {
        $previousExceptionMock = $this->getMockBuilder(FileWriterException::class);
        $previousExceptionMock->enableOriginalConstructor();
        $previousExceptionMock->setConstructorArgs(['Previous exception message']);
        $previousException = $previousExceptionMock->getMock();

        $exception = FileNotWritableException::fromPrevious($previousException);

        self::assertSame('Previous exception message', $exception->getMessage());
        self::assertSame($previousException, $exception->getPrevious());
    }
}
