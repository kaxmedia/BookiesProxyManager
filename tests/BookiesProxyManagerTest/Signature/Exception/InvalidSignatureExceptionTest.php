<?php

declare(strict_types=1);

namespace BookiesProxyManagerTest\Signature\Exception;

use PHPUnit\Framework\TestCase;
use BookiesProxyManager\Signature\Exception\InvalidSignatureException;
use ReflectionClass;

/**
 * Tests for {@see \BookiesProxyManager\Signature\Exception\InvalidSignatureException}
 *
 * @covers \BookiesProxyManager\Signature\Exception\InvalidSignatureException
 * @group Coverage
 */
final class InvalidSignatureExceptionTest extends TestCase
{
    public function testFromInvalidSignature(): void
    {
        $exception = InvalidSignatureException::fromInvalidSignature(
            new ReflectionClass(self::class),
            ['foo' => 'bar', 'baz' => 'tab'],
            'blah',
            'expected-signature'
        );

        self::assertSame(
            'Found signature "blah" for class "'
            . self::class
            . '" does not correspond to expected signature "expected-signature" for 2 parameters',
            $exception->getMessage()
        );
    }
}
