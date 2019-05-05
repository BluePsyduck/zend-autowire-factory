<?php

declare(strict_types=1);

namespace BluePsyduck\ZendAutoWireFactory;

use ReflectionClass;
use ReflectionException;
use ReflectionParameter;

/**
 * The class resolving the parameters to their possible aliases in the container.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ParameterAliasResolver
{
    /**
     * The cache file path to use.
     * @var string|null
     */
    private static $cacheFile = null;

    /**
     * The already resolved parameter aliases.
     * @var array|string[][][]
     */
    private static $parameterAliasesCache = [];

    /**
     * Sets the cache file to use.
     * @param string $cacheFile
     */
    public static function setCacheFile(string $cacheFile): void
    {
        self::$cacheFile = $cacheFile;
        if (file_exists($cacheFile)) {
            $parameterAliasesCache = require($cacheFile);
            if (is_array($parameterAliasesCache)) {
                self::$parameterAliasesCache = $parameterAliasesCache;
            }
        }
    }

    /**
     * Returns the aliases for the parameters of the constructor.
     * @param string $className
     * @return array|string[][]
     * @throws ReflectionException
     */
    public function getParameterAliasesForConstructor(string $className): array
    {
        if (!isset(self::$parameterAliasesCache[$className])) {
            self::$parameterAliasesCache[$className] = $this->resolveParameterAliasesForConstructor($className);
            $this->writeCacheToFile();
        }
        return self::$parameterAliasesCache[$className];
    }

    /**
     * Resolves the parameter aliases for the constructor.
     * @param string $className
     * @return array|string[][]
     * @throws ReflectionException
     */
    protected function resolveParameterAliasesForConstructor(string $className): array
    {
        $result = [];
        foreach ($this->getReflectedParametersForConstructor($className) as $parameter) {
            $result[$parameter->getName()] = $this->getAliasesForParameter($parameter);
        }
        return $result;
    }

    /**
     * Returns the reflected parameters of the constructor.
     * @param string $className
     * @return array|ReflectionParameter[]
     * @throws ReflectionException
     */
    private function getReflectedParametersForConstructor(string $className): array
    {
        $result = [];
        $reflectedClass = new ReflectionClass($className);
        if ($reflectedClass->getConstructor() !== null) {
            $result = $reflectedClass->getConstructor()->getParameters();
        }
        return $result;
    }

    /**
     * Returns the aliases for the parameter.
     * @param ReflectionParameter $parameter
     * @return array|string[]
     */
    private function getAliasesForParameter(ReflectionParameter $parameter): array
    {
        $result = [];

        if ($parameter->getClass() !== null) {
            $result[] = $parameter->getClass()->getName() . ' $' . $parameter->getName();
            $result[] = $parameter->getClass()->getName();
        } elseif ($parameter->getType() !== null) {
            $result[] = $parameter->getType()->getName() . ' $' . $parameter->getName();
        }

        $result[] = '$' . $parameter->getName();
        return $result;
    }

    /**
     * Writes the current cache to the cache file, if set.
     */
    private function writeCacheToFile(): void
    {
        if (self::$cacheFile !== null) {
            file_put_contents(
                self::$cacheFile,
                sprintf('<?php return %s;', var_export(self::$parameterAliasesCache, true))
            );
        }
    }
}
