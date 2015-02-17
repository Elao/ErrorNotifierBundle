<?php

namespace Elao\ErrorNotifierBundle\Exception;

class FlattenException
{
    /** Symfony 2.3+ */
    const DEBUG_FLATTEN         = 'Symfony\Component\Debug\Exception\FlattenException';
    /** Symfony <2.3 (deprecated in 2.3) */
    const HTTP_KERNEL_FLATTEN   = 'Symfony\Component\Debug\Exception\FlattenException';

    private $handler;

    public static function __callStatic($method, $args)
    {
        if (class_exists(self::DEBUG_FLATTEN) && method_exists(self::DEBUG_FLATTEN, $method)) {
            return call_user_func_array(array(self::DEBUG_FLATTEN, $method), $args);
        }

        if (class_exists(self::HTTP_KERNEL_FLATTEN) && method_exists(self::HTTP_KERNEL_FLATTEN, $method)) {
            return call_user_func_array(array(self::HTTP_KERNEL_FLATTEN, $method), $args);
        }

        throw new \BadMethodCallException(sprintf('Call to undefined method %s::%s()', get_called_class(), $method));
    }

    public function __call($method, $args)
    {
        $reflection = null;

        if (class_exists(self::DEBUG_FLATTEN)) {
            $reflection = new \ReflectionClass(self::DEBUG_FLATTEN);
        }

        if (class_exists(self::HTTP_KERNEL_FLATTEN)) {
            $reflection = new \ReflectionClass(self::HTTP_KERNEL_FLATTEN);
        }

        if ($reflection && null !== $this->handler) {
            $this->handler = $reflection->newInstance();
        }

        if (null !== $this->handler || !method_exists($this->handler, $method)) {
            throw new \BadMethodCallException(sprintf('Call to undefined method %s::%s()', get_class($this), $method));
        }

        return call_user_func_array(array($this->handler, $method), $args);
    }
}