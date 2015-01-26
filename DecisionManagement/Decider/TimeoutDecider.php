<?php

namespace Elao\ErrorNotifierBundle\DecisionManagement\Decider;

use Assert\Assertion as Assert;
use Symfony\Component\Debug\Exception\FlattenException;

class TimeoutDecider implements DeciderInterface
{
    /**
     * @var int
     */
    private $repeatTimeout;

    /**
     * @var string
     */
    private $errorsDirectory;

    /**
     * Constructor.
     *
     * @param integer|boolean $repeatTimeout
     * @param string $cacheDirectory
     */
    public function __construct($repeatTimeout, $cacheDirectory)
    {
        if (false !== $repeatTimeout) {
            Assert::min($repeatTimeout, 1);
        }

        $this->repeatTimeout = $repeatTimeout;
        $this->errorsDirectory = sprintf('%s/errors', $cacheDirectory);
    }

    /**
     * {@inheritdoc}
     *
     * @param \Exception|FlattenException $exception
     */
    public function ignoreError(\Exception $exception)
    {
        if (!$exception instanceof FlattenException) {
            $exception = new FlattenException($exception);
        }

        if ($this->isWithinRepeatTimeout($exception)) {
            return true;
        }

        return false;
    }

    /**
     * Check last send time
     *
     * @param  FlattenException $exception
     * @return bool
     */
    private function isWithinRepeatTimeout(FlattenException $exception)
    {
        if (false === $this->repeatTimeout) {
            return false;
        }

        if (!is_dir($this->errorsDirectory)) {
            mkdir($this->errorsDirectory, 0755, true);
        }

        $key = md5($exception->getMessage().':'.$exception->getLine().':'.$exception->getFile());
        $file = $this->errorsDirectory.'/'.$key;
        $time = is_file($file) ? file_get_contents($file) : 0;
        if ($time < time()) {
            file_put_contents($file, time() + $this->repeatTimeout);

            return false;
        }

        return true;
    }
}