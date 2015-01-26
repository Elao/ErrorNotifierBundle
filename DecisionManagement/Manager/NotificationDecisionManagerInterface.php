<?php

namespace Elao\ErrorNotifierBundle\DecisionManagement\Manager;

use Elao\ErrorNotifierBundle\DecisionManagement\Decider\DeciderInterface;

interface NotificationDecisionManagerInterface
{
    /**
     * Add decider to collection
     *
     * @param DeciderInterface $decider
     * @return null
     */
    public function addDecider(DeciderInterface $decider);

    /**
     * Process deciders and handle/ignore notification
     *
     * @param \Exception $exception
     * @return boolean
     */
    public function notifyError(\Exception $exception);
}
