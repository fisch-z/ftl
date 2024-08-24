<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Message\UniqueIdStamp;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;
use Symfony\Component\Messenger\Stamp\ReceivedStamp;
use Symfony\Component\Messenger\Stamp\SentStamp;

final class MilpacUpdateMessageMiddleware implements MiddlewareInterface
{
    public function __construct(private LoggerInterface $milpacUpdatesLogger)
    {
    }


    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        if (null === $envelope->last(UniqueIdStamp::class)) {
            $envelope = $envelope->with(new UniqueIdStamp());
        }
        /** @var UniqueIdStamp $stamp */
        $stamp = $envelope->last(UniqueIdStamp::class);
        $context = [
            "id" => $stamp->getUniqueId(),
            "class" => get_class($envelope->getMessage()),
        ];

        $envelope = $stack->next()->handle($envelope, $stack);

        // if ($envelope->last(ReceivedStamp::class)) {
        //     $this->milpacUpdatesLogger->info("[{id}] received {class}", $context);
        // } else if ($envelope->last(SentStamp::class)) {
        //     $this->milpacUpdatesLogger->info("[{id}] sent {class}", $context);
        // } else {
        //     $this->milpacUpdatesLogger->info("[{id}] handling {class}", $context);
        // }

        return $envelope;
    }
}
