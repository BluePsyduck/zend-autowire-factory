<?php

declare(strict_types=1);

namespace BluePsyduckTest\ZendAutoWireFactory;

use BluePsyduck\ZendAutoWireFactory\AliasArrayInjectorFactory;
use BluePsyduck\ZendAutoWireFactory\ConfigReaderFactory;
use function BluePsyduck\ZendAutoWireFactory\injectAliasArray;
use function BluePsyduck\ZendAutoWireFactory\readConfig;
use PHPStan\Testing\TestCase;

/**
 * The PHPUnit test of the helper functions.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class HelpersTest extends TestCase
{
    /**
     * Tests the injectAliasArray method.
     * @covers \BluePsyduck\ZendAutoWireFactory\injectAliasArray
     */
    public function testInjectAliasArray(): void
    {
        $expectedResult = new AliasArrayInjectorFactory('abc', 'def');

        $result = injectAliasArray('abc', 'def');

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Tests the readConfig method.
     * @covers \BluePsyduck\ZendAutoWireFactory\readConfig
     */
    public function testReadConfig(): void
    {
        $expectedResult = new ConfigReaderFactory('abc', 'def');

        $result = readConfig('abc', 'def');

        $this->assertEquals($expectedResult, $result);
    }
}
