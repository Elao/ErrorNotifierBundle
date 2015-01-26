<?php

namespace Elao\ErrorNotifierBundle\DecisionManagement\Decider;

use Assert\Assertion as Assert;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\HttpException;

class UserAgentDecider implements DeciderInterface
{
    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var array
     */
    private $ignoredUserAgentPatterns;

    /**
     * Constructor.
     *
     * @param RequestStack $requestStack
     * @param array $ignoredUserAgentPatterns
     */
    public function __construct(RequestStack $requestStack, array $ignoredUserAgentPatterns)
    {
        Assert::allString($ignoredUserAgentPatterns);

        $this->requestStack = $requestStack;
        $this->ignoredUserAgentPatterns = $ignoredUserAgentPatterns;
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

        foreach ($this->ignoredUserAgentPatterns as $userAgentPattern) {
            if (preg_match('#'.$userAgentPattern.'#', $request->headers->get('User-Agent'))) {
                return true;
            }
        }

        return false;
    }
}