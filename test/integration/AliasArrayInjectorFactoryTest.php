<?php

declare(strict_types=1);

namespace BluePsyduckIntegrationTest\ZendAutoWireFactory;

use BluePsyduck\ZendAutoWireFactory\Exception\MissingConfigException;
use function BluePsyduck\ZendAutoWireFactory\injectAliasArray;
use BluePsyduckTestAsset\ZendAutoWireFactory\ClassWithoutConstructor;
use BluePsyduckTestAsset\ZendAutoWireFactory\ClassWithParameterlessConstructor;
use Interop\Container\ContainerInterface;
use PHPUnit\Framework\TestCase;
use Zend\ServiceManager\Config;
use Zend\ServiceManager\ServiceManager;

/**
 * The integration test of the AliasArrayInjectorFactory class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \BluePsyduck\ZendAutoWireFactory\AliasArrayInjectorFactory
 */
class AliasArrayInjectorFactoryTest extends TestCase
{
    /**
     * Creates a container with a test config.
     * @return ContainerInterface
     */
    protected function createContainerWithConfig(): ContainerInterface
    {
        $config = [
            'foo' => [
                'bar' => ['abc', 'def'],
            ],
        ];
        $dependencies = [
            'aliases' => [
                'abc' => ClassWithParameterlessConstructor::class,
                'def' => ClassWithoutConstructor::class,
            ],
            'invokables' => [
                ClassWithParameterlessConstructor::class,
                ClassWithoutConstructor::class,
            ],
        ];

        $container = new ServiceManager();
        $container->setService('config', $config);
        (new Config($dependencies))->configureServiceManager($container);

        return $container;
    }

    /**
     * Tests the injectAliasArray method.
     */
    public function testInjectAliasArray(): void
    {
        $container = $this->createContainerWithConfig();
        $expectedResult = [
            new ClassWithParameterlessConstructor(),
            new ClassWithoutConstructor(),
        ];

        $callable = injectAliasArray('foo', 'bar');
        $result = $callable($container, 'test');

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Tests the injectAliasArray method.
     */
    public function testInjectAliasArrayWithError(): void
    {
        $container = $this->createContainerWithConfig();

        $this->expectException(MissingConfigException::class);

        $callable = injectAliasArray('unknown');
        $callable($container, 'test');
    }
}
