<?php

namespace App\Mailer\Inspection;

use App\Mailer\Inspection\Contract\TransportDescriberInterface;
use App\Mailer\Inspection\Contract\TransportDescriptor;
use App\Mailer\Inspection\Contract\TransportKind;
use Symfony\Component\Mailer\Transport\FailoverTransport;
use Symfony\Component\Mailer\Transport\RoundRobinTransport;
use Symfony\Component\Mailer\Transport\TransportInterface;

/**
 * Describes Symfony's round-robin transport.
 *
 * @implements TransportDescriberInterface<RoundRobinTransport>
 */
final class RoundRobinTransportDescriber implements TransportDescriberInterface
{
    #[\Override]
    public function supports(TransportInterface $transport): bool
    {
        return $transport instanceof RoundRobinTransport && !$transport instanceof FailoverTransport;
    }

    #[\Override]
    public function describe(TransportInterface $transport): TransportDescriptor
    {
        return new TransportDescriptor(
            kind: TransportKind::Composite,
            description: 'Round-robin transport',
            scheme: 'roundrobin',
        );
    }
}
