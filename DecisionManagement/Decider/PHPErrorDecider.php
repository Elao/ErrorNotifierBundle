<?php

namespace Elao\ErrorNotifierBundle\DecisionManagement\Decider;

use Assert\Assertion as Assert;
use Elao\ErrorNotifierBundle\Exception\ErrorException;

class PHPErrorDecider implements DeciderInterface
{
    /**
     * @var array
     */
    private $ignoredPHPErrors;

    /**
     * @var boolean
     */
    private $handlePHPErrors;

    /**
     * @var boolean
     */
    private $handlePHPWarnings;

    /**
     * @var boolean
     */
    private $handleSilentErrors;

    /**
     * @var array
     */
    private $warningCodes = array(
        E_NOTICE,
        E_USER_WARNING,
        E_USER_NOTICE,
        E_STRICT,
        E_DEPRECATED,
        E_USER_DEPRECATED
    );

    /**
     * @var array
     */
    private $errorCodes = array(
        E_ERROR,
        E_PARSE,
        E_CORE_ERROR,
        E_COMPILE_ERROR
    );

    /**
     * Constructor.
     *
     * @param array $ignoredPHPErrors
     * @param boolean $handlePHPErrors
     * @param boolean $handlePHPWarnings
     * @param boolean $handleSilentErrors
     */
    public function __construct(array $ignoredPHPErrors, $handlePHPErrors, $handlePHPWarnings, $handleSilentErrors)
    {
        Assert::allChoice(
            $ignoredPHPErrors,
            array_merge($this->errorCodes, $this->warningCodes)
        );

        $this->ignoredPHPErrors = $ignoredPHPErrors;
        $this->handlePHPErrors = $handlePHPErrors;
        $this->handlePHPWarnings = $handlePHPWarnings;
        $this->handleSilentErrors = $handleSilentErrors;
    }

    /**
     * {@inheritdoc}
     *
     * @param \Exception|ErrorException $exception
     */
    public function ignoreError(\Exception $exception)
    {
        if (!$exception instanceof ErrorException) {
            return false;
        }

        // don't catch error with error_reporting is 0
        if (0 === error_reporting() && false === $this->handleSilentErrors) {
            return true;
        }

        if (in_array($exception->getSeverity(), $this->ignoredPHPErrors)) {
            return true;
        }

        if (!$this->handlePHPWarnings && in_array($exception->getSeverity(), $this->warningCodes)) {
            return true;
        }

        if (!$this->handlePHPErrors && in_array($exception->getSeverity(), $this->errorCodes)) {
            return true;
        }

        return false;
    }
}