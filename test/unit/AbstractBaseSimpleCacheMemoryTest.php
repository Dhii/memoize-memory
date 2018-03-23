<?php

namespace Dhii\Cache\UnitTest;

use Dhii\Cache\AbstractBaseSimpleCacheMemory as TestSubject;
use InvalidArgumentException;
use Psr\Container\ContainerExceptionInterface;
use stdClass;
use Xpmock\TestCase;
use Exception as RootException;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use PHPUnit_Framework_MockObject_MockBuilder as MockBuilder;

/**
 * Tests {@see TestSubject}.
 *
 * @since [*next-version*]
 */
class AbstractBaseSimpleCacheMemoryTest extends TestCase
{
    /**
     * The class name of the test subject.
     *
     * @since [*next-version*]
     */
    const TEST_SUBJECT_CLASSNAME = 'Dhii\Cache\AbstractBaseSimpleCacheMemory';

    /**
     * Creates a new instance of the test subject.
     *
     * @since [*next-version*]
     *
     * @param array $methods The methods to mock.
     *
     * @return MockObject|TestSubject The new instance.
     */
    public function createInstance($methods = [])
    {
        is_array($methods) && $methods = $this->mergeValues($methods, [
            '__',
        ]);

        $mock = $this->getMockBuilder(static::TEST_SUBJECT_CLASSNAME)
            ->setMethods($methods)
            ->getMockForAbstractClass();

        $mock->method('__')
            ->will($this->returnArgument(0));

        return $mock;
    }

    /**
     * Merges the values of two arrays.
     *
     * The resulting product will be a numeric array where the values of both inputs are present, without duplicates.
     *
     * @since [*next-version*]
     *
     * @param array $destination The base array.
     * @param array $source      The array with more keys.
     *
     * @return array The array which contains unique values
     */
    public function mergeValues($destination, $source)
    {
        return array_keys(array_merge(array_flip($destination), array_flip($source)));
    }

    /**
     * Creates a mock that both extends a class and implements interfaces.
     *
     * This is particularly useful for cases where the mock is based on an
     * internal class, such as in the case with exceptions. Helps to avoid
     * writing hard-coded stubs.
     *
     * @since [*next-version*]
     *
     * @param string   $className      Name of the class for the mock to extend.
     * @param string[] $interfaceNames Names of the interfaces for the mock to implement.
     *
     * @return MockBuilder The builder for a mock of an object that extends and implements
     *                     the specified class and interfaces.
     */
    public function mockClassAndInterfaces($className, $interfaceNames = [])
    {
        $paddingClassName = uniqid($className);
        $definition = vsprintf('abstract class %1$s extends %2$s implements %3$s {}', [
            $paddingClassName,
            $className,
            implode(', ', $interfaceNames),
        ]);
        eval($definition);

        return $this->getMockBuilder($paddingClassName);
    }

    /**
     * Creates a mock that uses traits.
     *
     * This is particularly useful for testing integration between multiple traits.
     *
     * @since [*next-version*]
     *
     * @param string[] $traitNames Names of the traits for the mock to use.
     *
     * @return MockBuilder The builder for a mock of an object that uses the traits.
     */
    public function mockTraits($traitNames = [])
    {
        $paddingClassName = uniqid('Traits');
        $definition = vsprintf('abstract class %1$s {%2$s}', [
            $paddingClassName,
            implode(
                ' ',
                array_map(
                    function ($v) {
                        return vsprintf('use %1$s;', [$v]);
                    },
                    $traitNames)),
        ]);
        var_dump($definition);
        eval($definition);

        return $this->getMockBuilder($paddingClassName);
    }

    /**
     * Creates a new invocable object.
     *
     * @since [*next-version*]
     *
     * @return MockObject An object that has an `__invoke()` method.
     */
    public function createCallable()
    {
        $mock = $this->getMockBuilder('MyCallable')
            ->setMethods(['__invoke'])
            ->getMock();

        return $mock;
    }

    /**
     * Creates a new exception.
     *
     * @since [*next-version*]
     *
     * @param string $message The exception message.
     *
     * @return RootException|MockObject The new exception.
     */
    public function createException($message = '')
    {
        $mock = $this->getMockBuilder('Exception')
            ->setConstructorArgs([$message])
            ->getMock();

        return $mock;
    }

    /**
     * Creates a new Invalid Argument exception.
     *
     * @since [*next-version*]
     *
     * @param string $message The exception message.
     *
     * @return InvalidArgumentException|MockObject The new exception.
     */
    public function createInvalidArgumentException($message = '')
    {
        $mock = $this->getMockBuilder('InvalidArgumentException')
            ->setConstructorArgs([$message])
            ->getMock();

        return $mock;
    }

    /**
     * Creates a new Container exception.
     *
     * @since [*next-version*]
     *
     * @param string $message The exception message.
     *
     * @return RootException|ContainerExceptionInterface|MockObject
     */
    public function createContainerException($message = '')
    {
        $mock = $this->mockClassAndInterfaces('Exception', ['Psr\Container\ContainerExceptionInterface'])
            ->setConstructorArgs([$message])
            ->getMock();

        return $mock;
    }

    /**
     * Tests whether a valid instance of the test subject can be created.
     *
     * @since [*next-version*]
     */
    public function testCanBeCreated()
    {
        $subject = $this->createInstance();

        $this->assertInternalType(
            'object',
            $subject,
            'A valid instance of the test subject could not be created.'
        );
    }

    /**
     * Tests that `set()` fails as expected when a container exception is encountered.
     *
     * @since [*next-version*]
     */
    public function testSetFailureContainer()
    {
        $key = uniqid('key');
        $val = uniqid('val');
        $ttl = rand(1, 999999);
        $exception = $this->createContainerException('Problem setting');
        $subject = $this->createInstance(['_set']);
        $_subject = $this->reflect($subject);

        $subject->expects($this->exactly(1))
            ->method('_set')
            ->with($key, $val, $ttl)
            ->will($this->throwException($exception));

        $this->setExpectedException('Psr\Container\ContainerExceptionInterface');
        $_subject->set($key, $val, $ttl);
    }

    /**
     * Tests that `set()` fails as expected when a non-container exception is encountered.
     *
     * @since [*next-version*]
     */
    public function testSetFailure()
    {
        $key = uniqid('key');
        $val = uniqid('val');
        $ttl = rand(1, 999999);
        $exception = $this->createException('Could not set data');
        $containerException = $this->createContainerException('Problem assigning');
        $subject = $this->createInstance(['_set', '_createContainerException']);
        $_subject = $this->reflect($subject);

        $subject->expects($this->exactly(1))
            ->method('_set')
            ->with($key, $val)
            ->will($this->throwException($exception));
        $subject->expects($this->exactly(1))
            ->method('_createContainerException')
            ->with(
                $this->isType('string'),
                null,
                $exception,
                $subject
            )
            ->will($this->returnValue($containerException));

        $this->setExpectedException('Psr\Container\ContainerExceptionInterface');
        $_subject->set($key, $val, $ttl);
    }

    /**
     * Tests that `delete()` fails as expected when an exception is encountered.
     *
     * @since [*next-version*]
     */
    public function testDeleteFailure()
    {
        $key = uniqid('key');
        $exception = $this->createException('Could not set data');
        $containerException = $this->createContainerException('Problem assigning');
        $subject = $this->createInstance(['_unsetData', '_createContainerException']);
        $_subject = $this->reflect($subject);

        $subject->expects($this->exactly(1))
            ->method('_unsetData')
            ->with($key)
            ->will($this->throwException($exception));
        $subject->expects($this->exactly(1))
            ->method('_createContainerException')
            ->with(
                $this->isType('string'),
                null,
                $exception,
                $subject
            )
            ->will($this->returnValue($containerException));

        $this->setExpectedException('Psr\Container\ContainerExceptionInterface');
        $_subject->delete($key);
    }

    /**
     * Tests that `delete()` fails as expected when an exception is encountered.
     *
     * @since [*next-version*]
     */
    public function testClearFailure()
    {
        $store = new stdClass();
        $key = uniqid('key');
        $exception = $this->createException('Could not set data');
        $containerException = $this->createContainerException('Problem assigning');
        $subject = $this->createInstance(['_createDataStore', '_setDataStore', '_createContainerException']);
        $_subject = $this->reflect($subject);

        $subject->expects($this->exactly(1))
            ->method('_createDataStore')
            ->will($this->returnValue($store));
        $subject->expects($this->exactly(1))
            ->method('_setDataStore')
            ->with($store)
            ->will($this->throwException($exception));
        $subject->expects($this->exactly(1))
            ->method('_createContainerException')
            ->with(
                $this->isType('string'),
                null,
                $exception,
                $subject
            )
            ->will($this->returnValue($containerException));

        $this->setExpectedException('Psr\Container\ContainerExceptionInterface');
        $_subject->clear($key);
    }
}
