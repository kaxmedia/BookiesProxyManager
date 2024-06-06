<?php

declare(strict_types=1);

namespace BookiesProxyManagerTest\Signature\Exception;

use PHPUnit\Framework\TestCase;
use BookiesProxyManager\Signature\Exception\MissingSignatureException;
use ReflectionClass;

/**
 * Tests for {@see \BookiesProxyManager\Signature\Exception\MissingSignatureException}
 *
 * @covers \BookiesProxyManager\Signature\Exception\MissingSignatureException
 * @group Coverage
 */
final class MissingSignatureExceptionTest extends TestCase
{
    public function testFromMissingSignature(): void
    {
        $exception = MissingSignatureException::fromMissingSignature(
            new ReflectionClass(self::class),
            ['foo' => 'bar', 'baz' => 'tab'],
            'expected-signature'
        );

        self::assertSame(
            'No signature found for class "'
            . self::class
            . '", expected signature "expected-signature" for 2 parameters',
            $exception->getMessage()
        );
    }
}
