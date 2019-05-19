<?php

declare(strict_types=1);

namespace BluePsyduckTestAsset\ZendAutoWireFactory;

/**
 * A class with a constructor, but no parameters for it.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ClassWithParameterlessConstructor
{
    public $property;

    public function __construct()
    {
        $this->property = 'foo';
    }
}
