<?php


namespace App\Services\Export;


trait CanTrackProgress
{
    /**
     * @var int progress maximum number; can be 100 (as in 0-100%) or any number (x of y records processed)
     */
    protected $progressMax = 100;

    /**
     * @var int progress number increment
     */
    protected $progressCurrent = 0;

    /**
     * @var Callable
     */
    protected $onProgressIncrementCallable;

    public function setProgressMax($value)
    {
        $this->progressMax = $value;
    }

    public function setProgress($value)
    {
        $this->progressCurrent = $value;
    }

    /**
     * call this to reflect that progress has incremented
     */
    public function progressIncrement()
    {
        $callable = $this->onProgressIncrementCallable;
        $callable($this->getProgress());
    }

    public function getProgress($format = "%.4f")
    {
        return sprintf($format, ($this->progressCurrent / $this->progressMax));
    }

    /**
     * Set by a caller, to tell it what to do when progress has incremented
     * @param $callable
     * @return CanTrackProgress
     */
    public function onProgressIncrement($callable)
    {
        $this->onProgressIncrementCallable = $callable;
        return $this;
    }
}
