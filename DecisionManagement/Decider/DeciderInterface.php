<?php

namespace Elao\ErrorNotifierBundle\DecisionManagement\Decider;

interface DeciderInterface
{
    const VOTE_HANDLE   = 'handle';
    const VOTE_IGNORE   = 'ignore';
    const VOTE_ABSTAIN  = 'abstain';

    /**
     * Decide whether to ignore the current error notification
     *
     * @param \Exception $exception
     * @return boolean
     */
    public function ignoreError(\Exception $exception);
}
