<?php

declare(strict_types=1);

namespace BluePsyduckIntegrationTest\ZendAutoWireFactory;

use BluePsyduck\ZendAutoWireFactory\AutoWireFactory;
use BluePsyduck\ZendAutoWireFactory\ConfigReaderFactory;
use BluePsyduckTestAsset\ZendAutoWireFactory\ClassWithScalarTypeHintConstructor;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use Zend\ConfigAggregator\ConfigAggregator;
use Zend\ServiceManager\Config;
use Zend\ServiceManager\ServiceManager;

/**
 * The integration test for the ConfigAggregator.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ConfigAggregatorIntegrationTest extends TestCase
{
    /**
     * Returns a test config as a config provider.
     * @return callable
     */
    protected function getConfigProvider(): callable
    {
        return function (): array {
            return [
                ConfigAggregator::ENABLE_CACHE => true,
                'dependencies' => [
                    'factories' => [
                        ClassWithScalarTypeHintConstructor::class => AutoWireFactory::class,
                        'string $property' => ConfigReaderFactory::register('foo', 'bar'),
                    ],
                ],
                'foo' => [
                    'bar' => 'abc',
                ],
            ];
        };
    }

    /**
     * Creates the service manager with the config.
     * @param array $config
     * @return ServiceManager
     */
    protected function createServiceManagerWithConfig(array $config): ServiceManager
    {
        $result = new ServiceManager();

        (new Config($config['dependencies'] ?? []))->configureServiceManager($result);
        $result->setService('config', $config);

        return $result;
    }

    /**
     * Tests the caching of the ConfigAggregator.
     */
    public function testCaching(): void
    {
        $expectedInstance = new ClassWithScalarTypeHintConstructor('abc');

        $root = vfsStream::setup('root');
        $cacheFile = vfsStream::url('root/config-cache.php');

        // Do all steps without a cached config.
        $this->assertFalse($root->hasChild('config-cache.php'));
        $configAggregator = new ConfigAggregator([$this->getConfigProvider()], $cacheFile);
        $configWithoutCache = $configAggregator->getMergedConfig();
        $serviceManager = $this->createServiceManagerWithConfig($configWithoutCache);
        $instance = $serviceManager->get(ClassWithScalarTypeHintConstructor::class);
        $this->assertEquals($expectedInstance, $instance);

        // Redo all steps with the now-cached config.
        $this->assertTrue($root->hasChild('config-cache.php'));
        $configAggregator = new ConfigAggregator([], $cacheFile);
        $configWithCache = $configAggregator->getMergedConfig();
        $serviceManager = $this->createServiceManagerWithConfig($configWithCache);
        $instance = $serviceManager->get(ClassWithScalarTypeHintConstructor::class);
        $this->assertEquals($expectedInstance, $instance);
    }
}
