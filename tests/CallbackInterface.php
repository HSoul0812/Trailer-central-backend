<?php


namespace Tests;

/**
 * Interface CallableInterface
 * @package Tests\Unit
 */
interface CallbackInterface
{
    /**
     * @return \Closure
     */
    public function getClosure(): \Closure;

    /**
     * @return bool
     */
    public function isCalled(): bool;
}
