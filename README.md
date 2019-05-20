# Zend Auto-Wire Factory

[![Latest Stable Version](https://poser.pugx.org/bluepsyduck/zend-autowire-factory/v/stable)](https://packagist.org/packages/bluepsyduck/zend-autowire-factory) 
[![License](https://poser.pugx.org/bluepsyduck/zend-autowire-factory/license)](https://packagist.org/packages/bluepsyduck/zend-autowire-factory) 
[![Build Status](https://travis-ci.com/BluePsyduck/zend-autowire-factory.svg?branch=master)](https://travis-ci.com/BluePsyduck/zend-autowire-factory) 
[![codecov](https://codecov.io/gh/bluepsyduck/zend-autowire-factory/branch/master/graph/badge.svg)](https://codecov.io/gh/bluepsyduck/zend-autowire-factory)

This library provides few factories helping with auto-wiring service classes to make writing actual factories less
common. 

## AutoWireFactory

The `AutoWireFactory` uses reflection on the constructor of the actual service class to determine how to resolve the
dependencies and creating the actual service. The factory is adopting 
[Symfony's approach](https://symfony.com/doc/current/service_container/autowiring.html) of handling auto wiring,
especially [dealing with multiple implementations of the same type](https://symfony.com/doc/current/service_container/autowiring.html#dealing-with-multiple-implementations-of-the-same-type).

### Resolving strategies

The factory uses the following strategies to resolve a parameter of the constructor, depending on how it is type-hinted.
The first alias available in the container will be used to resolve the dependency. If no alias is available, an 
exception gets triggered.

Each parameter is resolved on its own, so they can be combined in any way.

#### Parameter with class type-hint

Example: ```__construct(FancyClass $fancy)```

If the parameter has a class name as type-hint, then the following aliases are checked in the container:

1. `FancyClass $fancy`: The combination of class name and parameter name. This allows for multiple implementations of
   the same interface as stated in the Symphony documentation.
2. `FancyClass`: "Default" case of registering a class with its name to the container.
3. `$fancy`: Fallback of using the parameter name alone, mostly to make the aliases uniform between cases.

The first alias which can be provided by the container will be used.

#### Parameter with scalar type-hint

Example: ```__construct(array $fancyConfig)```

If the parameter is type-hinted with a scalar type, e.g. to pull config values into the service, the following aliases
are checked:

1. `array $fancyConfig`: The combination of type and parameter name, the same as for class type-hints.
2. `$fancyConfig`: Fallback using only the parameter name. 

Note that the type alone, `array`, is not used as alias.

#### Parameter without type-hint

Example: ```__construct($fancyParameter)```

In this case, only one alias can be checked due to missing information:

1. `$fancyParameter`: Fallback is the only possible alias. 

### AutoWireFactory as AbstractFactory

Next to the `FactoryInterface` to use the `AutoWireFactory`as an explicit factory in the container configuration,
it also implements the `AbstractFactoryInterface`: If you add this factory as an abstract factory, it will try
to auto-wire everything it can. This will make configuring the container mostly obsolete, with the exception of 
parameters using scalar values or multiple implementations (where the parameter name is part of the container alias).

### Caching

The `AutoWireFactory` uses reflections to resolve dependencies. To make things faster, the factory offers building up
a cache on the filesystem to avoid using reflections on each script call. To enable the cache, add the following line
e.g. in the `config/container.php` file:

```php
\BluePsyduck\ZendAutoWireFactory\AutoWireFactory::setCacheFile('data/cache/autowire-factory.cache.php');
```
