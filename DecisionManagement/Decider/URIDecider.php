<?php

namespace Elao\ErrorNotifierBundle\DecisionManagement\Decider;

use Assert\Assertion as Assert;
use Symfony\Component\HttpFoundation\RequestMatcherInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\HttpException;

class URIDecider implements DeciderInterface
{
    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var array
     */
    private $requestMatchers;

    /**
     * Constructor.
     *
     * @param RequestStack $requestStack
     * @param array|RequestMatcherInterface[] $requestMatchers
     */
    public function __construct(RequestStack $requestStack, array $requestMatchers)
    {
        Assert::allIsInstanceOf($requestMatchers, 'Symfony\Component\HttpFoundation\RequestMatcherInterface');

        $this->requestStack = $requestStack;
        $this->requestMatchers = $requestMatchers;
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

        /** @var RequestMatcherInterface $requestMatcher */
        foreach ($this->requestMatchers as $requestMatcher) {
            if ($requestMatcher->matches($request)) {
                return true;
            }
        }

        return false;
    }
}