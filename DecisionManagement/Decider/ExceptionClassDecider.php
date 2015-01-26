<?php

namespace Elao\ErrorNotifierBundle\DecisionManagement\Decider;

use Assert\Assertion as Assert;
use Symfony\Component\Debug\Exception\FlattenException;

class ExceptionClassDecider implements DeciderInterface
{
    /**
     * @var array
     */
    private $ignoredClasses;

    /**
     * Constructor.
     *
     * @param array $ignoredClasses
     */
    public function __construct(array $ignoredClasses)
    {
        Assert::allString($ignoredClasses);

        $this->ignoredClasses = $ignoredClasses;
    }

    /**
     * {@inheritdoc}
     *
     * @param \Exception|FlattenException $exception
     */
    public function ignoreError(\Exception $exception)
    {
        if (in_array(get_class($exception), $this->ignoredClasses)) {
            return true;
        }

        return false;
    }
}