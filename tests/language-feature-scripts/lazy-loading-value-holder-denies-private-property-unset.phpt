--TEST--
Verifies that generated lazy loading value holders disallow private property direct unset
--FILE--
<?php

require_once __DIR__ . '/init.php';

class Kitchen
{
    private $sweets;
}

$factory = new \BookiesProxyManager\Factory\LazyLoadingValueHolderFactory($configuration);

$proxy = $factory->createProxy(Kitchen::class, function (& $wrapped, $proxy, $method, array $parameters, & $initializer) {
    $initializer = null;
    $wrapped     = new Kitchen();
});

unset($proxy->sweets);
?>
--EXPECTF--
%SFatal error:%sCannot %s property %a