<?php

namespace Elao\ErrorNotifierBundle\Configuration;

use Elao\ErrorNotifierBundle\Exception\FlattenException;

class Configuration
{
    /**
     * @var string
     */
    private $errorsDirectory;

    /**
     * @var bool
     */
    private $handle404Errors;

    /**
     * @var bool
     */
    private $handlePHPErrors;

    /**
     * @var bool
     */
    private $handlePHPWarnings;

    /**
     * @var bool
     */
    private $handleSilentErrors;

    /**
     * @var bool|string
     */
    private $repeatTimeout;

    /**
     * @var array
     */
    private $ignoredClasses;

    private $warningCodes = array(
        E_NOTICE,
        E_USER_WARNING,
        E_USER_NOTICE,
        E_STRICT,
        E_DEPRECATED,
        E_USER_DEPRECATED
    );

    private $errorCodes = array(
        E_ERROR,
        E_PARSE,
        E_CORE_ERROR,
        E_COMPILE_ERROR
    );

    /**
     * @param string            $cacheDirectory
     * @param boolean           $handle404Errors
     * @param boolean           $handlePHPErrors
     * @param boolean           $handlePHPWarnings
     * @param boolean           $handleSilentErrors
     * @param boolean|string    $repeatTimeout
     * @param array             $ignoredClasses
     */
    public function __construct(
        $cacheDirectory,
        $handle404Errors = false,
        $handlePHPErrors = false,
        $handlePHPWarnings = false,
        $handleSilentErrors = false,
        $repeatTimeout = false,
        array $ignoredClasses = array()
    ) {
        $this->errorsDirectory = sprintf('%s/errors', $cacheDirectory);
        $this->handle404Errors = $handle404Errors;
        $this->handlePHPErrors = $handlePHPErrors;
        $this->handlePHPWarnings = $handlePHPWarnings;
        $this->handleSilentErrors = $handleSilentErrors;
        $this->repeatTimeout = $repeatTimeout;
        $this->ignoredClasses = $ignoredClasses;
    }

    /**
     * @return string
     */
    public function getErrorsDirectory()
    {
        return $this->errorsDirectory;
    }

    /**
     * @return bool
     */
    public function handle404Errors()
    {
        return $this->handle404Errors;
    }

    /**
     * @return bool
     */
    public function handlePHPErrors()
    {
        return $this->handlePHPErrors;
    }

    /**
     * @param integer $statusCode
     * @return bool
     */
    public function handleError($statusCode)
    {
        return 500 === $statusCode || (404 === $statusCode && $this->handle404Errors());
    }

    /**
     * @return bool
     */
    public function handlePHPWarnings()
    {
        return $this->handlePHPWarnings;
    }

    /**
     * @param $warningCode
     * @return bool
     */
    public function handleWarning($warningCode)
    {
        $errors = array();

        if ($this->handlePHPErrors()) {
            $errors += $this->errorCodes;
        }

        if ($this->handlePHPWarnings()) {
            $errors += $this->warningCodes;
        }

        return in_array($warningCode, $errors);
    }

    /**
     * @return bool
     */
    public function handleSilentErrors()
    {
        return $this->handleSilentErrors;
    }

    /**
     * @return bool
     */
    public function doRepeatTimeout()
    {
        return false !== $this->repeatTimeout;
    }

    /**
     * @return bool|string
     */
    public function getRepeatTimeout()
    {
        return $this->repeatTimeout;
    }

    /**
     * @return array
     */
    public function getIgnoredClasses()
    {
        return $this->ignoredClasses;
    }

    /**
     * @param FlattenException $exception
     * @return bool
     */
    public function ignoreExceptionClass(FlattenException $exception)
    {
        return in_array($exception->getClass(), $this->ignoredClasses);
    }
}
