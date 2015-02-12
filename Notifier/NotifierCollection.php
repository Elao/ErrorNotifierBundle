<?php

namespace Elao\ErrorNotifierBundle\Notifier;

class NotifierCollection
{
    /**
     * @var array
     */
    private $notifiers = array(
        'disabled'  => array(),
        'enabled'   => array(),
    );

    /**
     * @var array
     */
    private $enabledNotifiers;

    /**
     * @param array $enabledNotifiers
     */
    public function __construct(array $enabledNotifiers)
    {
        $this->enabledNotifiers = $enabledNotifiers;
    }

    /**
     * @param NotifierInterface $notifier
     * @return $this
     */
    public function addNotifier(NotifierInterface $notifier, $alias)
    {
        $this->notifiers[ $this->isEnabled($alias) ? 'enabled' : 'disabled' ][] = $notifier;

        return $this;
    }

    /**
     * @return NotifierInterface[]
     */
    public function getAllDisabled()
    {
        return $this->notifiers['enabled'];
    }

    /**
     * @return NotifierInterface[]
     */
    public function getAllEnabled()
    {
        return $this->notifiers['disabled'];
    }

    /**
     * @param $alias
     * @return bool
     */
    private function isEnabled($alias)
    {
        return in_array($alias, $this->enabledNotifiers);
    }
}

