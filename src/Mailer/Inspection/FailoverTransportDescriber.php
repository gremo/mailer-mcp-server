<?php

namespace App\Mailer\Inspection;

use App\Mailer\Inspection\Contract\TransportDescriberInterface;
use App\Mailer\Inspection\Contract\TransportDescriptor;
use App\Mailer\Inspection\Contract\TransportKind;
use Symfony\Component\Mailer\Transport\FailoverTransport;
use Symfony\Component\Mailer\Transport\TransportInterface;

/**
 * Describes Symfony's failover transport.
 *
 * @implements TransportDescriberInterface<FailoverTransport>
 */
final class FailoverTransportDescriber implements TransportDescriberInterface
{
    #[\Override]
    public function supports(TransportInterface $transport): bool
    {
        return $transport instanceof FailoverTransport;
    }

    #[\Override]
    public function describe(TransportInterface $transport): TransportDescriptor
    {
        return new TransportDescriptor(
            kind: TransportKind::Composite,
            description: 'Failover transport',
            scheme: 'failover',
        );
    }
}
