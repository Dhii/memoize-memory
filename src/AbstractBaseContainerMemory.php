<?php

namespace Dhii\Cache;

use Dhii\Collection\AbstractBaseCountableMap;
use Dhii\Data\Container\ContainerSetCapableTrait;
use Dhii\Data\Object\SetDataCapableTrait;
use Dhii\Exception\CreateRuntimeExceptionCapableTrait;
use Dhii\Invocation\CreateInvocationExceptionCapableTrait;
use Dhii\Invocation\InvokeCallableCapableTrait;
use Dhii\Util\Normalization\NormalizeArrayCapableTrait;
use Dhii\Util\String\StringableInterface as Stringable;
use InvalidArgumentException;
use Exception as RootException;
use Psr\Container\ContainerExceptionInterface;

/**
 * Base implementation of a cache container.
 *
 * @since [*next-version*]
 */
abstract class AbstractBaseContainerMemory extends AbstractBaseCountableMap implements ContainerInterface
{
    /* Retrieval and generation of cache.
     *
     * @since [*next-version*]
     */
    use GetCachedCapableTrait;

    /* Ability to set a data member.
     *
     * @since [*next-version*]
     */
    use SetDataCapableTrait;

    /* Structured invocation of a callable.
     *
     * @since [*next-version*]
     */
    use InvokeCallableCapableTrait;

    /* Ability to set data on any container.
     *
     * @since [*next-version*]
     */
    use ContainerSetCapableTrait;

    /* Normalization to array.
     *
     * @since [*next-version*]
     */
    use NormalizeArrayCapableTrait;

    /* Factory of Runtime exception.
     *
     * @since [*next-version*]
     */
    use CreateRuntimeExceptionCapableTrait;

    /* Factory of Invocation exception.
     *
     * @since [*next-version*]
     */
    use CreateInvocationExceptionCapableTrait;

    /**
     * {@inheritdoc}
     *
     * @param string|int|float|bool|Stringable $key The key to get the data for.
     *
     * @since [*next-version*]
     */
    public function get($key, $default = null, $ttl = null)
    {
        try {
            return $this->_getCached($key, $default, $ttl);
        } catch (RootException $e) {
            if ($e instanceof ContainerExceptionInterface) {
                throw $e;
            }

            throw $this->_createContainerException($this->__('Could not set data'), null, $e, $this);
        }
    }

    /**
     * Sets a value for the specified key.
     *
     * @since [*next-version*]
     *
     * @param string|int|float|bool|Stringable $key   The key to set the value for.
     * @param mixed                            $value The value to set.
     * @param null|string|Stringable|int       $ttl   The maximal number of seconds, for which the value is considered valid.
     *                                                If null, the TTL is unpredictable, perhaps indefinite.
     *
     * @throws ContainerExceptionInterface If the value could not be set.
     * @throws InvalidArgumentException    If the key or the TTL is invalid.
     */
    protected function _set($key, $value, $ttl = null)
    {
        if ($ttl !== null) {
            $ttl = $this->_normalizeInt($ttl);
        }

        try {
            $this->_setData($key, $value);
        } catch (RootException $e) {
            if ($e instanceof InvalidArgumentException) {
                throw $e;
            }

            throw $this->_createContainerException($this->__('Could not set data'), null, $e, $this);
        }
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _getGeneratorArgs($key, $generator, $ttl)
    {
        return [$key, $ttl];
    }
}
