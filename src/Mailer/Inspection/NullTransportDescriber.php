<?php

namespace App\Mailer\Inspection;

use App\Mailer\Inspection\Contract\TransportDescriberInterface;
use App\Mailer\Inspection\Contract\TransportDescriptor;
use App\Mailer\Inspection\Contract\TransportKind;
use Symfony\Component\Mailer\Transport\NullTransport;
use Symfony\Component\Mailer\Transport\TransportInterface;

/**
 * Describes the transport that discards messages.
 *
 * @implements TransportDescriberInterface<NullTransport>
 */
final class NullTransportDescriber implements TransportDescriberInterface
{
    #[\Override]
    public function supports(TransportInterface $transport): bool
    {
        return $transport instanceof NullTransport;
    }

    #[\Override]
    public function describe(TransportInterface $transport): TransportDescriptor
    {
        return new TransportDescriptor(
            kind: TransportKind::Null,
            description: 'Null transport (messages are discarded)',
            scheme: 'null',
        );
    }
}
