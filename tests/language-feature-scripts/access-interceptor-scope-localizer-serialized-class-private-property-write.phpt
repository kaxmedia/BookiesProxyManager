--TEST--
Verifies that generated access interceptors doesn't throw PHP Warning on Serialized class private property direct write
--FILE--
<?php

require_once __DIR__ . '/init.php';

class Kitchen implements \Serializable
{
    private $sweets = 'candy';

    function serialize()
    {
        return $this->sweets;
    }

    function unserialize($serialized)
    {
        $this->sweets = $serialized;
    }
}

$factory = new \BookiesProxyManager\Factory\AccessInterceptorScopeLocalizerFactory($configuration);

$proxy = $factory->createProxy(new Kitchen());

$proxy->sweets = 'stolen';
?>
--EXPECTF--
%SFatal error:%sCannot %s property%sin %a
