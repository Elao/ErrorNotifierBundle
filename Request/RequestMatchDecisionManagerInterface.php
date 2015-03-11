<?php

namespace Elao\ErrorNotifierBundle\Request;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestMatcherInterface;

interface RequestMatchDecisionManagerInterface
{
    /**
     * Test whether master request matches any of the provided request matchers
     *
     * @param Request $request
     * @return mixed
     */
    public function matches(Request $request);
}
