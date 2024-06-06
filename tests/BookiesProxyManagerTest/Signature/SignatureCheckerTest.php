<?php

declare(strict_types=1);

namespace BookiesProxyManagerTest\Signature;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use BookiesProxyManager\Signature\Exception\InvalidSignatureException;
use BookiesProxyManager\Signature\Exception\MissingSignatureException;
use BookiesProxyManager\Signature\SignatureChecker;
use BookiesProxyManager\Signature\SignatureGeneratorInterface;
use ReflectionClass;

/**
 * Tests for {@see \BookiesProxyManager\Signature\SignatureChecker}
 *
 * @covers \BookiesProxyManager\Signature\SignatureChecker
 * @group Coverage
 */
final class SignatureCheckerTest extends TestCase
{
    public string $signatureExample = 'valid-signature';
    private SignatureChecker $signatureChecker;

    /** @var SignatureGeneratorInterface&MockObject */
    private SignatureGeneratorInterface $signatureGenerator;

    protected function setUp(): void
    {
        $this->signatureGenerator = $this->createMock(SignatureGeneratorInterface::class);
        $this->signatureChecker   = new SignatureChecker($this->signatureGenerator);
    }

    public function testCheckSignatureWithValidKey(): void
    {
        $this
            ->signatureGenerator
            ->expects(self::atLeastOnce())
            ->method('generateSignatureKey')
            ->with(['foo' => 'bar'])
            ->willReturn('Example');
        $this
            ->signatureGenerator
            ->expects(self::atLeastOnce())
            ->method('generateSignature')
            ->with(['foo' => 'bar'])
            ->willReturn('valid-signature');

        $this->signatureChecker->checkSignature(new ReflectionClass($this), ['foo' => 'bar']);
    }

    public function testCheckSignatureWithInvalidKey(): void
    {
        $this
            ->signatureGenerator

            ->method('generateSignatureKey')
            ->with(['foo' => 'bar'])
            ->willReturn('InvalidKey');
        $this
            ->signatureGenerator
            ->method('generateSignature')
            ->with(['foo' => 'bar'])
            ->willReturn('valid-signature');

        $this->expectException(MissingSignatureException::class);

        $this->signatureChecker->checkSignature(new ReflectionClass($this), ['foo' => 'bar']);
    }

    public function testCheckSignatureWithInvalidValue(): void
    {
        $this
            ->signatureGenerator
            ->method('generateSignatureKey')
            ->with(['foo' => 'bar'])
            ->willReturn('Example');
        $this
            ->signatureGenerator
            ->method('generateSignature')
            ->with(['foo' => 'bar'])
            ->willReturn('invalid-signature');

        $this->expectException(InvalidSignatureException::class);

        $this->signatureChecker->checkSignature(new ReflectionClass($this), ['foo' => 'bar']);
    }
}
