<?php

declare(strict_types=1);

namespace BluePsyduckTest\ZendAutoWireFactory;

use BluePsyduck\TestHelper\ReflectionTrait;
use BluePsyduck\ZendAutoWireFactory\ParameterAliasResolver;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionException;
use ReflectionNamedType;
use ReflectionParameter;

/**
 * The PHPUnit test of the ParameterAliasResolver class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \BluePsyduck\ZendAutoWireFactory\ParameterAliasResolver
 */
class ParameterAliasResolverTest extends TestCase
{
    use ReflectionTrait;

    /**
     * Tests the getParameterAliasesForConstructor method.
     * @throws ReflectionException
     * @covers ::getParameterAliasesForConstructor
     */
    public function testGetParameterAliasesForConstructorWithoutCache(): void
    {
        $className = 'abc';
        $parameterAliases = [
            'def' => ['ghi', 'jkl'],
            'mno' => ['pqr', 'stu'],
        ];
        $cache = [
            'foo' => [
                'bar' => [],
            ],
        ];
        $expectedCache = [
            'foo' => [
                'bar' => [],
            ],
            'abc' => [
                'def' => ['ghi', 'jkl'],
                'mno' => ['pqr', 'stu'],
            ],
        ];

        /* @var ParameterAliasResolver&MockObject $resolver */
        $resolver = $this->getMockBuilder(ParameterAliasResolver::class)
                         ->setMethods(['resolveParameterAliasesForConstructor', 'writeCacheToFile'])
                         ->getMock();
        $resolver->expects($this->once())
                 ->method('resolveParameterAliasesForConstructor')
                 ->with($this->identicalTo($className))
                 ->willReturn($parameterAliases);
        $resolver->expects($this->once())
                 ->method('writeCacheToFile');
        $this->injectProperty(ParameterAliasResolver::class, 'parameterAliasesCache', $cache);

        $result = $resolver->getParameterAliasesForConstructor($className);

        $this->assertSame($parameterAliases, $result);
        $this->assertEquals(
            $expectedCache,
            $this->extractProperty(ParameterAliasResolver::class, 'parameterAliasesCache')
        );
    }

    /**
     * Tests the getParameterAliasesForConstructor method.
     * @throws ReflectionException
     * @covers ::getParameterAliasesForConstructor
     */
    public function testGetParameterAliasesForConstructorWithCache(): void
    {
        $className = 'abc';
        $cache = [
            'foo' => [
                'bar' => [],
            ],
            'abc' => [
                'def' => ['ghi', 'jkl'],
                'mno' => ['pqr', 'stu'],
            ],
        ];
        $expectedResult = [
            'def' => ['ghi', 'jkl'],
            'mno' => ['pqr', 'stu'],
        ];

        /* @var ParameterAliasResolver&MockObject $resolver */
        $resolver = $this->getMockBuilder(ParameterAliasResolver::class)
                         ->setMethods(['resolveParameterAliasesForConstructor', 'writeCacheToFile'])
                         ->getMock();
        $resolver->expects($this->never())
                 ->method('resolveParameterAliasesForConstructor');
        $resolver->expects($this->never())
                 ->method('writeCacheToFile');
        $this->injectProperty(ParameterAliasResolver::class, 'parameterAliasesCache', $cache);

        $result = $resolver->getParameterAliasesForConstructor($className);

        $this->assertSame($expectedResult, $result);
        $this->assertSame($cache, $this->extractProperty(ParameterAliasResolver::class, 'parameterAliasesCache'));
    }

    /**
     * Tests the resolveParameterAliasesForConstructor method.
     * @throws ReflectionException
     * @covers ::resolveParameterAliasesForConstructor
     */
    public function testResolveParameterAliasesForConstructor(): void
    {
        $className = 'abc';
        $aliases1 = ['def', 'ghi'];
        $aliases2 = ['jkl', 'mno'];
        $expectedResult = [
            'pqr' => ['def', 'ghi'],
            'stu' => ['jkl', 'mno'],
        ];

        /* @var ReflectionParameter&MockObject $parameter1 */
        $parameter1 = $this->createMock(ReflectionParameter::class);
        $parameter1->expects($this->once())
                   ->method('getName')
                   ->willReturn('pqr');

        /* @var ReflectionParameter&MockObject $parameter2 */
        $parameter2 = $this->createMock(ReflectionParameter::class);
        $parameter2->expects($this->once())
                   ->method('getName')
                   ->willReturn('stu');

        /* @var ParameterAliasResolver&MockObject $resolver */
        $resolver = $this->getMockBuilder(ParameterAliasResolver::class)
                         ->setMethods([
                             'getReflectedParametersForConstructor',
                             'getAliasesForParameter',
                         ])
                         ->getMock();
        $resolver->expects($this->once())
                 ->method('getReflectedParametersForConstructor')
                 ->with($this->identicalTo($className))
                 ->willReturn([$parameter1, $parameter2]);
        $resolver->expects($this->exactly(2))
                 ->method('getAliasesForParameter')
                 ->withConsecutive(
                     [$this->identicalTo($parameter1)],
                     [$this->identicalTo($parameter2)]
                 )
                 ->willReturnOnConsecutiveCalls(
                     $aliases1,
                     $aliases2
                 );

        $result = $this->invokeMethod($resolver, 'resolveParameterAliasesForConstructor', $className);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Tests the getAliasesForParameter method.
     * @throws ReflectionException
     * @covers ::getAliasesForParameter
     */
    public function testGetAliasesForParameterWithClassHint(): void
    {
        $className = 'abc';
        $parameterName = 'def';
        $expectedResult = [
            'abc $def',
            'abc',
            '$def',
        ];

        /* @var ReflectionClass&MockObject $class */
        $class = $this->createMock(ReflectionClass::class);
        $class->expects($this->any())
              ->method('getName')
              ->willReturn($className);

        /* @var ReflectionParameter&MockObject $parameter */
        $parameter = $this->createMock(ReflectionParameter::class);
        $parameter->expects($this->any())
                  ->method('getClass')
                  ->willReturn($class);
        $parameter->expects($this->any())
                  ->method('getName')
                  ->willReturn($parameterName);

        $resolver = new ParameterAliasResolver();
        $result = $this->invokeMethod($resolver, 'getAliasesForParameter', $parameter);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Tests the getAliasesForParameter method.
     * @throws ReflectionException
     * @covers ::getAliasesForParameter
     */
    public function testGetAliasesForParameterWithTypeHint(): void
    {
        $typeName = 'abc';
        $parameterName = 'def';
        $expectedResult = [
            'abc $def',
            '$def',
        ];

        /* @var ReflectionNamedType&MockObject $type */
        $type = $this->createMock(ReflectionNamedType::class);
        $type->expects($this->any())
             ->method('getName')
             ->willReturn($typeName);

        /* @var ReflectionParameter&MockObject $parameter */
        $parameter = $this->createMock(ReflectionParameter::class);
        $parameter->expects($this->any())
                  ->method('getClass')
                  ->willReturn(null);
        $parameter->expects($this->any())
                  ->method('getType')
                  ->willReturn($type);
        $parameter->expects($this->any())
                  ->method('getName')
                  ->willReturn($parameterName);

        $resolver = new ParameterAliasResolver();
        $result = $this->invokeMethod($resolver, 'getAliasesForParameter', $parameter);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Tests the getAliasesForParameter method.
     * @throws ReflectionException
     * @covers ::getAliasesForParameter
     */
    public function testGetAliasesForParameterWithoutHint(): void
    {
        $parameterName = 'abc';
        $expectedResult = [
            '$abc',
        ];

        /* @var ReflectionParameter&MockObject $parameter */
        $parameter = $this->createMock(ReflectionParameter::class);
        $parameter->expects($this->any())
                  ->method('getClass')
                  ->willReturn(null);
        $parameter->expects($this->any())
                  ->method('getType')
                  ->willReturn(null);
        $parameter->expects($this->any())
                  ->method('getName')
                  ->willReturn($parameterName);

        $resolver = new ParameterAliasResolver();
        $result = $this->invokeMethod($resolver, 'getAliasesForParameter', $parameter);

        $this->assertEquals($expectedResult, $result);
    }

}
