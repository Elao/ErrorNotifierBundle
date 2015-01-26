<?php

namespace Elao\ErrorNotifierBundle\Tests\DecisionManagement\Decider;

use Elao\ErrorNotifierBundle\DecisionManagement\Decider\DeciderInterface;

class PresetResultDecider implements DeciderInterface
{
    /**
     * @var string
     */
    private $result;

    /**
     * Constructor.
     *
     * @param boolean $result
     */
    public function __construct($result)
    {
        $this->setResult($result);
    }

    /**
     * Set result
     *
     * @param boolean $result
     * @throws \Exception
     */
    public function setResult($result)
    {
        $this->result = true === $result;
    }

    /**
     * {@inheritdoc}
     */
    public function ignoreError(\Exception $exception)
    {
        return $this->result;
    }
}