<?php

declare(strict_types=1);

namespace BookiesProxyManager\Proxy;

/**
 * Virtual Proxy - a lazy initializing object wrapping around the proxied subject
 */
interface VirtualProxyInterface extends LazyLoadingInterface, ValueHolderInterface
{
}
