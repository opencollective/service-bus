<?php

/**
 * PHP Service Bus (publish-subscribe pattern implementation).
 *
 * @author  Maksim Masiukevich <dev@async-php.com>
 * @license MIT
 * @license https://opensource.org/licenses/MIT
 */

declare(strict_types = 1);

namespace ServiceBus\Infrastructure\Retry;

use function Kelunik\Retry\retry;
use Amp\Promise;
use Kelunik\Retry\ConstantBackoff;

/**
 * A wrapper on an operation that performs repetitions in case of an error.
 */
final class OperationRetryWrapper
{
    /**
     * Retry operation options.
     *
     * @var RetryOptions
     */
    private $options;

    /**
     * @param RetryOptions|null $options
     */
    public function __construct(RetryOptions $options = null)
    {
        $this->options = $options ?? new RetryOptions();
    }

    /**
     * @param callable   $operation     Wrapped operation
     * @param string ...$exceptionClasses Exceptions in which attempts are repeating the operation
     *
     * @return Promise<mixed>
     */
    public function __invoke(callable $operation, string ...$exceptionClasses): Promise
    {
        return retry(
            $this->options->maxCount,
            $operation,
            $exceptionClasses,
            new ConstantBackoff($this->options->delay)
        );
    }
}
