<?php

namespace Elao\ErrorNotifierBundle\DecisionManagement\Decider;

use Assert\Assertion as Assert;
use Symfony\Component\HttpKernel\Exception\HttpException;

class HTTPCodeDecider implements DeciderInterface
{
    /**
     * @var array
     */
    private $notifiableCodes;

    /**
     * Constructor.
     *
     * @param array $notifiableCodes
     */
    public function __construct(array $notifiableCodes)
    {
        Assert::allInteger($notifiableCodes);

        $this->notifiableCodes = $notifiableCodes;
    }

    /**
     * {@inheritdoc}
     *
     * @param \Exception|HttpException $exception
     */
    public function ignoreError(\Exception $exception)
    {
        if (!$exception instanceof HttpException) {
            return false;
        }

        if (!in_array((int) $exception->getStatusCode(), $this->notifiableCodes)) {
            return true;
        }

        return false;
    }
}