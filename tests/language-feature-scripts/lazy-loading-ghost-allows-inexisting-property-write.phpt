--TEST--
Verifies that generated lazy loading ghost objects disallow reading non-existing properties via direct read
--FILE--
<?php

require_once __DIR__ . '/init.php';

class Kitchen
{
    private $sweets;
}

$factory = new \BookiesProxyManager\Factory\LazyLoadingGhostFactory($configuration);

$proxy = $factory->createProxy(Kitchen::class, function () {});

$proxy->nonExisting = 'I do not exist';
echo $proxy->nonExisting;
?>
--EXPECTF--
I do not exist