<?php

declare(strict_types=1);

namespace BookiesProxyManagerTest;

use PHPUnit\Framework\TestCase;
use BookiesProxyManager\Version;

/**
 * Tests for {@see \BookiesProxyManager\Version}
 *
 * @covers \BookiesProxyManager\Version
 * @group Coverage
 */
final class VersionTest extends TestCase
{
    public static function testGetVersion(): void
    {
        $version = Version::getVersion();

        self::assertStringMatchesFormat('%A@%A', $version);
    }
}
