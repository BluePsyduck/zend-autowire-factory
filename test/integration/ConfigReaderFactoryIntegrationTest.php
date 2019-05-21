<?php

declare(strict_types=1);

namespace BluePsyduckIntegrationTest\ZendAutoWireFactory;

use BluePsyduck\ZendAutoWireFactory\ConfigReaderFactory;
use BluePsyduck\ZendAutoWireFactory\Exception\MissingConfigException;
use Interop\Container\ContainerInterface;
use PHPUnit\Framework\TestCase;
use Zend\ServiceManager\ServiceManager;

/**
 * The integration test of the ConfigReaderFactory class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \BluePsyduck\ZendAutoWireFactory\ConfigReaderFactory
 */
class ConfigReaderFactoryIntegrationTest extends TestCase
{
    /**
     * Creates a container with a test config.
     * @return ContainerInterface
     */
    protected function createContainerWithConfig(): ContainerInterface
    {
        $config = [
            'abc' => [
                'def' => 'ghi',
                'jkl' => 42,
                'mno' => ['pqr', 'stu'],
            ],
            'vwx' => null,
        ];

        $container = new ServiceManager();
        $container->setService('config', $config);
        return $container;
    }

    /**
     * Provides the data for the configReaderFactory test.
     * @return array
     */
    public function provideConfigReaderFactory(): array
    {
        return [
            [['abc', 'def'], 'ghi'],
            [['abc', 'jkl'], 42],
            [['abc', 'mno'], ['pqr', 'stu']],
            [['vwx'], null],
        ];
    }

    /**
     * Tests the ConfigReaderFactory class.
     * @param array $keys
     * @param mixed $expectedResult
     * @dataProvider provideConfigReaderFactory
     */
    public function testConfigReaderFactory(array $keys, $expectedResult): void
    {
        $container = $this->createContainerWithConfig();

        $callable = ConfigReaderFactory::register(...$keys);
        $result = $callable($container, 'foo');

        $this->assertSame($expectedResult, $result);
    }

    /**
     * Tests the ConfigReaderFactory class.
     */
    public function testConfigReaderFactoryWithException(): void
    {
        $container = $this->createContainerWithConfig();

        $this->expectException(MissingConfigException::class);

        $callable = ConfigReaderFactory::register('abc', 'foo', 'bar');
        $callable($container, 'foo');
    }
}
