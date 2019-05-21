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
     * Tests the setDefaultConfigAlias method.
     * @throws ReflectionException
     * @covers ::setDefaultConfigAlias
     * @runInSeparateProcess
     */
    public function testSetDefaultConfigAlias(): void
    {
        $configAlias = 'abc';

        $this->assertSame('config', $this->extractProperty(ConfigReaderFactory::class, 'defaultConfigAlias'));

        ConfigReaderFactory::setDefaultConfigAlias($configAlias);
        $this->assertSame($configAlias, $this->extractProperty(ConfigReaderFactory::class, 'defaultConfigAlias'));
    }

    /**
     * Tests the register method.
     * @throws ReflectionException
     * @covers ::register
     */
    public function testRegister(): void
    {
        $requestedName = 'def';
        $config = [
            'ghi' => [
                'jkl' => 'mno',
            ],
        ];
        $keys = ['ghi', 'jkl'];
        $expectedResult = 'mno';

        /* @var ContainerInterface&MockObject $container */
        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->once())
                  ->method('get')
                  ->with($this->identicalTo('config'))
                  ->willReturn($config);

        $callback = ConfigReaderFactory::register(...$keys);
        $result = $callback($container, $requestedName);

        $this->assertSame($expectedResult, $result);
    }

    /**
     * Tests the constructing.
     * @throws ReflectionException
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        $configAlias = 'abc';
        $keys = ['def', 'ghi'];

        $factory = new ConfigReaderFactory($configAlias, $keys);

        $this->assertSame($configAlias, $this->extractProperty($factory, 'configAlias'));
        $this->assertSame($keys, $this->extractProperty($factory, 'keys'));
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

        /* @var ContainerInterface&MockObject $container */
        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->once())
                  ->method('get')
                  ->with($this->identicalTo($configAlias))
                  ->willReturn($config);

        $factory = new ConfigReaderFactory($configAlias, $keys);
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

        $factory = new ConfigReaderFactory($configAlias, $keys);
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

        $factory = new ConfigReaderFactory($configAlias, $keys);
        $factory($container, $requestedName);
    }
}
