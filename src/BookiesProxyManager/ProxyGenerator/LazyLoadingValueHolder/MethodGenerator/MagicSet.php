<?php

declare(strict_types=1);

namespace BookiesProxyManager\ProxyGenerator\LazyLoadingValueHolder\MethodGenerator;

use InvalidArgumentException;
use Laminas\Code\Generator\ParameterGenerator;
use Laminas\Code\Generator\PropertyGenerator;
use BookiesProxyManager\Generator\MagicMethodGenerator;
use BookiesProxyManager\ProxyGenerator\PropertyGenerator\PublicPropertiesMap;
use BookiesProxyManager\ProxyGenerator\Util\PublicScopeSimulator;
use ReflectionClass;

/**
 * Magic `__set` for lazy loading value holder objects
 */
class MagicSet extends MagicMethodGenerator
{
    /**
     * Constructor
     *
     * @throws InvalidArgumentException
     */
    public function __construct(
        ReflectionClass $originalClass,
        PropertyGenerator $initializerProperty,
        PropertyGenerator $valueHolderProperty,
        PublicPropertiesMap $publicProperties
    ) {
        parent::__construct(
            $originalClass,
            '__set',
            [new ParameterGenerator('name'), new ParameterGenerator('value')]
        );

        $hasParent   = $originalClass->hasMethod('__set');
        $initializer = $initializerProperty->getName();
        $valueHolder = $valueHolderProperty->getName();
        $callParent  = '';

        if (! $publicProperties->isEmpty()) {
            $callParent = 'if (isset(self::$' . $publicProperties->getName() . "[\$name])) {\n"
                . '    return ($this->' . $valueHolder . '->$name = $value);'
                . "\n}\n\n";
        }

        $callParent .= $hasParent
            ? 'return $this->' . $valueHolder . '->__set($name, $value);'
            : PublicScopeSimulator::getPublicAccessSimulationCode(
                PublicScopeSimulator::OPERATION_SET,
                'name',
                'value',
                $valueHolderProperty,
                null,
                $originalClass
            );

        $this->setBody(
            '$this->' . $initializer . ' && $this->' . $initializer
            . '->__invoke($this->' . $valueHolder . ', $this, '
            . '\'__set\', array(\'name\' => $name, \'value\' => $value), $this->' . $initializer . ');'
            . "\n\n" . $callParent
        );
    }
}
