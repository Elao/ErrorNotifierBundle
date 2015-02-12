<?php

namespace Elao\ErrorNotifierBundle\Exception;

class ErrorException extends \ErrorException
{
    public function __construct($severity = 1, $message = '', $filename = __FILE__, $lineNumber = __LINE__, $code = 0)
    {
        parent::__construct(
            sprintf(sprintf('%s: %s in %s line %d', @$this->getErrorString($severity), $message, $filename, $lineNumber)),
            $code,
            $severity,
            $filename,
            $lineNumber
        );
    }

    /**
     * Convert the error code to a readable format
     *
     * @param  integer $errorNo
     * @return string
     */
    private function getErrorString($errorNo)
    {
        // may be exhaustive, but not sure
        $errorStrings = array(
            E_WARNING           => 'Warning',
            E_NOTICE            => 'Notice',
            E_USER_ERROR        => 'User Error',
            E_USER_WARNING      => 'User Warning',
            E_USER_NOTICE       => 'User Notice',
            E_STRICT            => 'Runtime Notice (E_STRICT)',
            E_RECOVERABLE_ERROR => 'Catchable Fatal Error',
            E_DEPRECATED        => 'Deprecated',
            E_USER_DEPRECATED   => 'User Deprecated',
            E_ERROR             => 'Error',
            E_PARSE             => 'Parse Error',
            E_CORE_ERROR        => 'E_CORE_ERROR',
            E_COMPILE_ERROR     => 'E_COMPILE_ERROR',
            E_CORE_WARNING      => 'E_CORE_WARNING',
            E_COMPILE_WARNING   => 'E_COMPILE_WARNING',
        );

        return array_key_exists($errorNo, $errorStrings) ? $errorStrings[$errorNo] : 'UNKNOWN';
    }
}