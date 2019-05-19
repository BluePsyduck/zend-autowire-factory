<?php

declare(strict_types=1);

namespace BluePsyduckTestAsset\ZendAutoWireFactory;

/**
 * A class using a scalar type hint in the constructor.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ClassWithScalarTypeHintConstructor
{
    public $foo;

    public function __construct(string $property)
    {
        $this->foo = new ClassWithParameterlessConstructor();
        $this->foo->property = $property;
    }
}
