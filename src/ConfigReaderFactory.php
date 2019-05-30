<?php

declare(strict_types=1);

namespace BluePsyduck\ZendAutoWireFactory;

use BluePsyduck\ZendAutoWireFactory\Exception\MissingConfigException;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

/**
 * The factory reading a value from the application config.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ConfigReaderFactory implements FactoryInterface
{
    /**
     * The default config alias to use when calling the register() method.
     * @var string
     */
    protected static $defaultConfigAlias = 'config';

    /**
     * The alias used for the config.
     * @var string
     */
    protected $configAlias;

    /**
     * The keys of the config.
     * @var array
     */
    protected $keys;

    /**
     * Sets the default config alias to use when calling the register() method.
     * @param string $defaultConfigAlias
     */
    public static function setDefaultConfigAlias(string $defaultConfigAlias): void
    {
        self::$defaultConfigAlias = $defaultConfigAlias;
    }

    /**
     * Registers an instance of the factory to the container.
     * @param string ...$keys
     * @return callable
     */
    public static function register(string ...$keys): callable
    {
        return new self(self::$defaultConfigAlias, $keys);
    }

    /**
     * Sets the state of the factory on deserialization.
     * @param array $array
     * @return ConfigReaderFactory
     */
    public static function __set_state(array $array): ConfigReaderFactory
    {
        return new self(
            $array['configAlias'] ?? self::$defaultConfigAlias,
            $array['keys'] ?? []
        );
    }

    /**
     * Initializes the factory.
     * @param string $configAlias
     * @param array $keys
     */
    public function __construct(string $configAlias, array $keys)
    {
        $this->configAlias = $configAlias;
        $this->keys = $keys;
    }

    /**
     * Creates the service.
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array|null $options
     * @return mixed
     * @throws MissingConfigException
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $result = $container->get($this->configAlias);
        foreach ($this->keys as $key) {
            if (!is_array($result) || !array_key_exists($key, $result)) {
                throw new MissingConfigException($this->keys);
            }
            $result = $result[$key];
        }
        return $result;
    }
}
