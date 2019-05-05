<?php

declare(strict_types=1);

namespace BluePsyduck\ZendAutoWireFactory;

use BluePsyduck\ZendAutoWireFactory\Exception\AutoWireException;
use BluePsyduck\ZendAutoWireFactory\Exception\FailedReflectionException;
use BluePsyduck\ZendAutoWireFactory\Exception\NoParameterMatchException;
use Psr\Container\ContainerInterface;
use ReflectionException;

/**
 * The factory auto-wiring the parameters of services.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class AutoWireFactory
{
    /**
     * The parameter alias resolver.
     * @var ParameterAliasResolver
     */
    private $parameterAliasResolver;

    /**
     * Sets the cache file to use.
     * @param string $cacheFile
     */
    public static function setCacheFile(string $cacheFile): void
    {
        ParameterAliasResolver::setCacheFile($cacheFile);
    }

    /**
     * Initializes the factory.
     */
    public function __construct()
    {
        $this->parameterAliasResolver = new ParameterAliasResolver();
    }

    /**
     * Creates the service.
     * @param ContainerInterface $container
     * @param string $requestedName
     * @return object
     * @throws AutoWireException
     */
    public function __invoke(ContainerInterface $container, $requestedName)
    {
        try {
            $parameterAliases = $this->parameterAliasResolver->getParameterAliasesForConstructor($requestedName);
        } catch (ReflectionException $e) {
            throw new FailedReflectionException($requestedName, $e);
        }

        $parameters = $this->createParameterInstances($container, $requestedName, $parameterAliases);
        return new $requestedName(...$parameters);
    }

    /**
     * Creates the instances for the parameter aliases.
     * @param ContainerInterface $container
     * @param string $className
     * @param array|string[][] $parameterAliases
     * @return array|object[]
     * @throws AutoWireException
     */
    private function createParameterInstances(
        ContainerInterface $container,
        string $className,
        array $parameterAliases
    ): array {
        $result = [];
        foreach ($parameterAliases as $parameterName => $aliases) {
            $result[] = $this->createInstanceOfFirstAvailableAlias($container, $className, $parameterName, $aliases);
        }
        return $result;
    }

    /**
     * Creates the instance of the first alias actually available in the container.
     * @param ContainerInterface $container
     * @param string $className
     * @param string $parameterName
     * @param array|string[] $aliases
     * @return mixed
     * @throws AutoWireException
     */
    private function createInstanceOfFirstAvailableAlias(
        ContainerInterface $container,
        string $className,
        string $parameterName,
        array $aliases
    ) {
        foreach ($aliases as $alias) {
            if ($container->has($alias)) {
                return $container->get($alias);
            }
        }

        throw new NoParameterMatchException($className, $parameterName);
    }
}
