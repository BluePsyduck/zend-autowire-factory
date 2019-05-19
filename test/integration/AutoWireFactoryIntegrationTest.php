<?php

declare(strict_types=1);

namespace BluePsyduckIntegrationTest\ZendAutoWireFactory;

use BluePsyduck\ZendAutoWireFactory\AutoWireFactory;
use BluePsyduckTestAsset\ZendAutoWireFactory\ClassWithClassTypeHintConstructor;
use BluePsyduckTestAsset\ZendAutoWireFactory\ClassWithoutConstructor;
use BluePsyduckTestAsset\ZendAutoWireFactory\ClassWithParameterlessConstructor;
use BluePsyduckTestAsset\ZendAutoWireFactory\ClassWithScalarTypeHintConstructor;
use Interop\Container\ContainerInterface;
use PHPUnit\Framework\TestCase;
use Zend\ServiceManager\Config;
use Zend\ServiceManager\ServiceManager;

/**
 * The integration test of the AutoWireFactory class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \BluePsyduck\ZendAutoWireFactory\AutoWireFactory
 */
class AutoWireFactoryIntegrationTest extends TestCase
{
    /**
     * Provides the data for the autoWiring test.
     * @return array
     */
    public function provideAutoWiredClasses(): array
    {
        return [
            [ClassWithoutConstructor::class],
            [ClassWithParameterlessConstructor::class],
            [ClassWithClassTypeHintConstructor::class],
            [ClassWithScalarTypeHintConstructor::class],
        ];
    }

    /**
     * Creates the container for the test.
     * @return ContainerInterface
     */
    protected function createContainerWithExplicitFactories(): ContainerInterface
    {
        $config = new Config([
            'services' => [
                'string $property' => 'abc',
            ],
            'factories' => [
                ClassWithClassTypeHintConstructor::class => AutoWireFactory::class,
                ClassWithoutConstructor::class => AutoWireFactory::class,
                ClassWithParameterlessConstructor::class => AutoWireFactory::class,
                ClassWithScalarTypeHintConstructor::class => AutoWireFactory::class,
            ],
        ]);

        $container = new ServiceManager();
        $config->configureServiceManager($container);

        return $container;
    }

    /**
     * Tests the autoWiring method.
     * @dataProvider provideAutoWiredClasses
     * @param string $className
     */
    public function testAutoWiringWithExplicitFactories(string $className): void
    {
        $container = $this->createContainerWithExplicitFactories();
        $instance = $container->get($className);

        $this->assertInstanceOf($className, $instance);
    }
    
    /**
     * Creates the container for the test.
     * @return ContainerInterface
     */
    protected function createContainerWithAbstractFactory(): ContainerInterface
    {
        $config = new Config([
            'services' => [
                'string $property' => 'abc',
            ],
            'abstract_factories' => [
                AutoWireFactory::class,
            ],
        ]);

        $container = new ServiceManager();
        $config->configureServiceManager($container);

        return $container;
    }

    /**
     * Tests the autoWiring method.
     * @dataProvider provideAutoWiredClasses
     * @param string $className
     */
    public function testAutoWiringWithAbstractFactory(string $className): void
    {
        $container = $this->createContainerWithAbstractFactory();
        $instance = $container->get($className);

        $this->assertInstanceOf($className, $instance);
    }
}
