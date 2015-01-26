<?php

namespace Elao\ErrorNotifierBundle\DecisionManagement\Decider;

use Elao\ErrorNotifierBundle\Assertion\Assert;
use Symfony\Component\HttpFoundation\IpUtils;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ClientIPDecider implements DeciderInterface
{
    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var array
     */
    private $ignoredIPs;

    /**
     * Constructor.
     *
     * @param RequestStack $requestStack
     * @param array $ignoredIPs
     */
    public function __construct(RequestStack $requestStack, array $ignoredIPs)
    {
        Assert::allIp($ignoredIPs);

        $this->requestStack = $requestStack;
        $this->ignoredIPs = $ignoredIPs;
    }

    /**
     * {@inheritdoc}
     */
    public function ignoreError(\Exception $exception)
    {
        if (!$exception instanceof HttpException) {
            return false;
        }

        if (null === $request = $this->requestStack->getMasterRequest()) {
            return false;
        }

        if (IpUtils::checkIp($request->getClientIp(), $this->ignoredIPs)) {
            return true;
        }

        return false;
    }
}