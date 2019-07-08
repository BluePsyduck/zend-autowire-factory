<?php

declare(strict_types=1);

namespace BluePsyduckTest\ZendAutoWireFactory;

use BluePsyduck\TestHelper\ReflectionTrait;
use BluePsyduck\ZendAutoWireFactory\ConfigReaderFactory;
use BluePsyduck\ZendAutoWireFactory\Exception\MissingConfigException;
use Interop\Container\ContainerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;

/**
 * The PHPUnit test of the ConfigReaderFactory class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \BluePsyduck\ZendAutoWireFactory\ConfigReaderFactory
 */
class ConfigReaderFactoryTest extends TestCase
{
    use ReflectionTrait;

    /**
     * Tests the setConfigAlias method.
     * @throws ReflectionException
     * @covers ::setConfigAlias
     * @runInSeparateProcess
     */
    public function testSetConfigAlias(): void
    {
        $configAlias = 'abc';

        $this->assertSame('config', $this->extractProperty(ConfigReaderFactory::class, 'configAlias'));

        ConfigReaderFactory::setConfigAlias($configAlias);
        $this->assertSame($configAlias, $this->extractProperty(ConfigReaderFactory::class, 'configAlias'));
    }

    /**
     * Tests the __set_state method.
     * @covers ::__set_state
     */
    public function testSetState(): void
    {
        $array = [
            'keys' => ['abc', 'def'],
        ];
        $expectedResult = new ConfigReaderFactory('abc', 'def');

        $result = ConfigReaderFactory::__set_state($array);
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Tests the __set_state method.
     * @covers ::__set_state
     */
    public function testSetStateWithoutArray(): void
    {
        $expectedResult = new ConfigReaderFactory();

        $result = ConfigReaderFactory::__set_state([]);
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Tests the constructing.
     * @throws ReflectionException
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        $expectedKeys = ['abc', 'def'];
        $factory = new ConfigReaderFactory('abc', 'def');

        $this->assertSame($expectedKeys, $this->extractProperty($factory, 'keys'));
    }

    /**
     * Tests the invoking.
     * @throws ReflectionException
     * @covers ::__invoke
     */
    public function testInvoke(): void
    {
        $configAlias = 'abc';
        $requestedName = 'def';
        $config = [
            'ghi' => [
                'jkl' => 'mno',
            ],
        ];
        $keys = ['ghi', 'jkl'];
        $expectedResult = 'mno';

        ConfigReaderFactory::setConfigAlias($configAlias);

        /* @var ContainerInterface&MockObject $container */
        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->once())
                  ->method('get')
                  ->with($this->identicalTo($configAlias))
                  ->willReturn($config);

        $factory = new ConfigReaderFactory(...$keys);
        $result = $factory($container, $requestedName);

        $this->assertSame($expectedResult, $result);
    }

    /**
     * Tests the invoking.
     * @throws ReflectionException
     * @covers ::__invoke
     */
    public function testInvokeWithMissingKey(): void
    {
        $configAlias = 'abc';
        $requestedName = 'def';
        $config = [
            'ghi' => [],
        ];
        $keys = ['ghi', 'jkl'];

        /* @var ContainerInterface&MockObject $container */
        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->once())
                  ->method('get')
                  ->with($this->identicalTo($configAlias))
                  ->willReturn($config);

        $this->expectException(MissingConfigException::class);

        $factory = new ConfigReaderFactory(...$keys);
        $factory($container, $requestedName);
    }

    /**
     * Tests the invoking.
     * @throws ReflectionException
     * @covers ::__invoke
     */
    public function testInvokeWithNonArrayValue(): void
    {
        $configAlias = 'abc';
        $requestedName = 'def';
        $config = [
            'ghi' => [
                'jkl' => 'mno',
            ],
        ];
        $keys = ['ghi', 'jkl', 'foo'];

        /* @var ContainerInterface&MockObject $container */
        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->once())
                  ->method('get')
                  ->with($this->identicalTo($configAlias))
                  ->willReturn($config);

        $this->expectException(MissingConfigException::class);

        $factory = new ConfigReaderFactory(...$keys);
        $factory($container, $requestedName);
    }
}
