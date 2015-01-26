<?php

namespace Elao\ErrorNotifierBundle\Assertion;

use Assert\Assertion as BaseAssert;

/**
 * METHODSTART
 * @method static void allIp($value, $message = null, $propertyPath = null)
 * METHODEND
 */
class Assert extends BaseAssert
{
    const INVALID_IP    = 214;

    /**
     * Assert that value is an email adress (using
     * input_filter/FILTER_VALIDATE_EMAIL).
     *
     * @param mixed $value
     * @param string|null $message
     * @param string|null $propertyPath
     * @return void
     * @throws \Assert\AssertionFailedException
     */
    public static function ip($value, $message = null, $propertyPath = null)
    {
        static::string($value, $message, $propertyPath);

        if ( ! filter_var($value, FILTER_VALIDATE_IP)) {
            $message = sprintf(
                $message ?: 'Value "%s" was expected to be a valid IP address.',
                self::stringify($value)
            );

            throw static::createException($value, $message, static::INVALID_IP, $propertyPath);
        }
    }
}