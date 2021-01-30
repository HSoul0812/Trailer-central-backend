<?php

declare(strict_types=1);

namespace App\Contracts;

use Psr\Log\LoggerInterface;

/**
 * Describes a logger instance according to PSR-3.
 *
 * @see https://www.php-fig.org/psr/psr-3/#3-psrlogloggerinterface
 */
interface LoggerServiceInterface extends LoggerInterface
{
    /**
     * Get a log channel instance.
     *
     * @param string|null $channel The channel name
     *
     * @return mixed
     */
    public function channel(?string $channel);

    /**
     * Create a new, on-demand aggregate logger instance.
     *
     * @param array $channels The array of resolved channels
     * @param string|null $channel The channel name
     * @return LoggerInterface
     */
    public function stack(array $channels, ?string $channel);
}
