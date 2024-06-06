<?php

declare(strict_types=1);

namespace BookiesProxyManager\ProxyGenerator\LazyLoadingValueHolder\MethodGenerator;

use Laminas\Code\Generator\Exception\InvalidArgumentException;
use Laminas\Code\Generator\PropertyGenerator;
use BookiesProxyManager\Generator\MethodGenerator;

/**
 * Implementation for {@see \BookiesProxyManager\Proxy\LazyLoadingInterface::isProxyInitialized}
 * for lazy loading value holder objects
 */
class IsProxyInitialized extends MethodGenerator
{
    /**
     * Constructor
     *
     * @throws InvalidArgumentException
     */
    public function __construct(PropertyGenerator $valueHolderProperty)
    {
        parent::__construct('isProxyInitialized');
        $this->setReturnType('bool');
        $this->setBody('return null !== $this->' . $valueHolderProperty->getName() . ';');
    }
}
