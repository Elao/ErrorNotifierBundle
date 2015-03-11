<?php

namespace Elao\ErrorNotifierBundle\Request;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestMatcherInterface;

class RequestMatchDecisionManager implements RequestMatchDecisionManagerInterface
{
    /**
     * @var array
     */
    protected $requestMatchers = array();

    public function addRequestMatcher(RequestMatcherInterface $requestMatcher)
    {
        $this->requestMatchers[] = $requestMatcher;
    }

    /**
     * {@inheritdoc}
     */
    public function matches(Request $request)
    {
        /** @var RequestMatcherInterface $requestMatcher */
        foreach ($this->requestMatchers as $requestMatcher) {
            if ($requestMatcher->matches($request)) {
                return true;
            }
        }

        return false;
    }
}
