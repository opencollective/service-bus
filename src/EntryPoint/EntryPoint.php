<?php

/**
 * PHP Service Bus (publish-subscribe pattern implementation).
 *
 * @author  Maksim Masiukevich <dev@async-php.com>
 * @license MIT
 * @license https://opensource.org/licenses/MIT
 */

declare(strict_types = 1);

namespace ServiceBus\EntryPoint;

use function Amp\call;
use function ServiceBus\Common\collectThrowableDetails;
use Amp\Delayed;
use Amp\Loop;
use Amp\Promise;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use ServiceBus\Transport\Common\Package\IncomingPackage;
use ServiceBus\Transport\Common\Queue;
use ServiceBus\Transport\Common\Transport;

/**
 * Application entry point.
 * It is the entry point for messages coming from a transport. Responsible for processing.
 */
final class EntryPoint
{
    /**
     * The default value for the maximum number of tasks processed simultaneously.
     * The value should not be too large and should not exceed the maximum number of available connections to the
     * database.
     */
    private const DEFAULT_MAX_CONCURRENT_TASK_COUNT = 60;

    /** Throttling value (in milliseconds) while achieving the maximum number of simultaneously executed tasks. */
    private const DEFAULT_AWAIT_DELAY = 20;

    /**
     * Current transport from which messages will be received.
     *
     * @var Transport
     */
    private $transport;

    /**
     * Handling incoming package processor.
     * Responsible for deserialization, routing and task execution.
     *
     * @var EntryPointProcessor
     */
    private $processor;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * The max number of concurrent tasks.
     *
     * @var int
     */
    private $maxConcurrentTaskCount;

    /**
     * The current number of tasks performed.
     * The value should not be too large and should not exceed the maximum number of available connections to the
     * database.
     *
     * @var int
     */
    private $currentTasksInProgressCount = 0;

    /**
     * Throttling value (in milliseconds) while achieving the maximum number of simultaneously executed tasks.
     *
     * @var int
     */
    private $awaitDelay;

    /**
     * @param Transport            $transport
     * @param EntryPointProcessor  $processor
     * @param LoggerInterface|null $logger
     * @param int|null             $maxConcurrentTaskCount
     * @param int|null             $awaitDelay Barrier wait delay (in milliseconds)
     */
    public function __construct(
        Transport $transport,
        EntryPointProcessor $processor,
        ?LoggerInterface $logger = null,
        ?int $maxConcurrentTaskCount = null,
        ?int $awaitDelay = null
    ) {
        $this->transport              = $transport;
        $this->processor              = $processor;
        $this->logger                 = $logger ?? new NullLogger();
        $this->maxConcurrentTaskCount = $maxConcurrentTaskCount ?? self::DEFAULT_MAX_CONCURRENT_TASK_COUNT;
        $this->awaitDelay             = $awaitDelay ?? self::DEFAULT_AWAIT_DELAY;
    }

    /**
     * Start queues listen.
     *
     * @param Queue ...$queues
     *
     * @throws \ServiceBus\Transport\Common\Exceptions\ConnectionFail Connection refused
     *
     * @return Promise It does not return any result
     */
    public function listen(Queue ...$queues): Promise
    {
        /** Hack for phpunit tests */
        $isTestCall = 'phpunitTests' === (string) \getenv('SERVICE_BUS_TESTING');

        /** @psalm-suppress MixedArgument */
        return call(
            function(array $queues) use ($isTestCall): \Generator
            {
                /** @psalm-suppress TooManyTemplateParams */
                yield $this->transport->consume(
                    function(IncomingPackage $package) use ($isTestCall): \Generator
                    {
                        $this->currentTasksInProgressCount++;

                        /** Hack for phpUnit */
                        if (true === $isTestCall)
                        {
                            $this->currentTasksInProgressCount--;

                            yield $this->processor->handle($package);
                            yield $this->transport->stop();

                            Loop::stop();
                        }

                        /** Handle incoming package */
                        $this->deferExecution($package);

                        /** Limit the maximum number of concurrently running tasks */
                        while ($this->maxConcurrentTaskCount <= $this->currentTasksInProgressCount)
                        {
                            yield new Delayed($this->awaitDelay);
                        }
                    },
                    ...$queues
                );
            },
            $queues
        );
    }

    /**
     * Unsubscribe all queues.
     * Terminates the subscription and stops the daemon after the specified number of seconds.
     *
     * @param int $delay The delay before the completion (in seconds)
     *
     * @return void
     */
    public function stop(int $delay = 10): void
    {
        $delay = 0 >= $delay ? 1 : $delay;

        Loop::defer(
            function() use ($delay): \Generator
            {
                yield $this->transport->stop();

                $this->logger->info('Handler will stop after {duration} seconds', ['duration' => $delay]);

                Loop::delay(
                    $delay * 1000,
                    function(): void
                    {
                        $this->logger->info('The event loop has been stopped');

                        Loop::stop();
                    }
                );
            }
        );
    }

    /**
     * @param IncomingPackage $package
     *
     * @return void
     */
    private function deferExecution(IncomingPackage $package): void
    {
        Loop::defer(
            function() use ($package): void
            {
                $this->processor->handle($package)->onResolve(
                    function(?\Throwable $throwable) use ($package): void
                    {
                        $this->currentTasksInProgressCount--;

                        if (null !== $throwable)
                        {
                            $this->logger->critical(
                                $throwable->getMessage(),
                                \array_merge(
                                    collectThrowableDetails($throwable),
                                    [
                                        'packageId' => $package->id(),
                                        'traceId'   => $package->traceId(),
                                    ]
                                )
                            );
                        }
                    }
                );
            }
        );
    }
}
