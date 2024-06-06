--TEST--
Verifies that generated lazy loading ghost objects disallow reading non-existing properties via direct read
--FILE--
<?php

require_once __DIR__ . '/init.php';

class Kitchen
{
    private $sweets;

    public function & __get($name)
    {
        return $name;
    }
}

$factory = new \BookiesProxyManager\Factory\LazyLoadingGhostFactory($configuration);

$proxy = $factory->createProxy(Kitchen::class, function () {});

echo $proxy->nonExisting;
?>
--EXPECTF--
nonExisting