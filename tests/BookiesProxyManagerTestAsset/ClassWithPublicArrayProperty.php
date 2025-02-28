<?php

declare(strict_types=1);

namespace BookiesProxyManagerTestAsset;

/**
 * Base test class to verify that proxies actually modify the array keys of
 * public properties that keep an array
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 */
class ClassWithPublicArrayProperty
{
    /** @var mixed[] */
    public $arrayProperty = [];
}
