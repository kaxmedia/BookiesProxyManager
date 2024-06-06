<?php

declare(strict_types=1);

namespace BookiesProxyManagerTest\Factory\RemoteObject\Adapter;

use Laminas\Server\Client;
use PHPUnit\Framework\TestCase;
use BookiesProxyManager\Factory\RemoteObject\Adapter\Soap;

/**
 * Tests for {@see \BookiesProxyManager\Factory\RemoteObject\Adapter\Soap}
 *
 * @group Coverage
 */
final class SoapTest extends TestCase
{
    /**
     * {@inheritDoc}
     *
     * @covers \BookiesProxyManager\Factory\RemoteObject\Adapter\Soap::__construct
     * @covers \BookiesProxyManager\Factory\RemoteObject\Adapter\Soap::getServiceName
     */
    public function testCanBuildAdapterWithSoapRpcClient(): void
    {
        $client = $this->getMockBuilder(Client::class)->setMethods(['call'])->getMock();

        $adapter = new Soap($client);

        $client
            ->expects(self::once())
            ->method('call')
            ->with('bar', ['tab' => 'taz'])
            ->willReturn('baz');

        self::assertSame('baz', $adapter->call('foo', 'bar', ['tab' => 'taz']));
    }
}
