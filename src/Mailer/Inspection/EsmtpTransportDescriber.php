<?php

namespace App\Mailer\Inspection;

use App\Mailer\Inspection\Contract\TransportDescriberInterface;
use App\Mailer\Inspection\Contract\TransportDescriptor;
use App\Mailer\Inspection\Contract\TransportKind;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;
use Symfony\Component\Mailer\Transport\Smtp\Stream\SocketStream;
use Symfony\Component\Mailer\Transport\TransportInterface;

/**
 * Describes generic ESMTP transports.
 *
 * @implements TransportDescriberInterface<EsmtpTransport>
 */
#[AsTaggedItem(priority: -100)]
final class EsmtpTransportDescriber implements TransportDescriberInterface
{
    #[\Override]
    public function supports(TransportInterface $transport): bool
    {
        return $transport instanceof EsmtpTransport;
    }

    #[\Override]
    public function describe(TransportInterface $transport): TransportDescriptor
    {
        $stream = $transport->getStream();
        $socket = $stream instanceof SocketStream ? $stream : null;

        return new TransportDescriptor(
            kind: TransportKind::Smtp,
            description: 'SMTP',
            scheme: $socket?->isTLS() ? 'smtps' : 'smtp',
            endpoint: $socket?->getHost(),
            port: $socket?->getPort(),
            metadata: ['username' => $transport->getUsername()],
        );
    }
}
