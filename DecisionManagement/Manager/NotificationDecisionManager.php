<?php

namespace Elao\ErrorNotifierBundle\DecisionManagement\Manager;

use Elao\ErrorNotifierBundle\DecisionManagement\Decider\DeciderInterface;

class NotificationDecisionManager implements NotificationDecisionManagerInterface
{
    /**
     * @var array|DeciderInterface[]
     */
    private $deciders = array();

    /**
     * {@inheritdoc}
     */
    public function addDecider(DeciderInterface $decider)
    {
        $this->deciders[get_class($decider)] = $decider;
    }

    /**
     * {@inheritdoc}
     */
    public function notifyError(\Exception $exception)
    {
        foreach ($this->deciders as $decider) {
            if ($decider->ignoreError($exception)) {
                return false;
            }
        }

        return true;
    }
}
