<?php

namespace App\Mailer\Inspection;

use App\Mailer\Inspection\Contract\TransportDescriberInterface;
use App\Mailer\Inspection\Contract\TransportDescriptor;
use App\Mailer\Inspection\Contract\TransportKind;
use Symfony\Component\Mailer\Bridge\Mailomat\Transport\MailomatApiTransport;
use Symfony\Component\Mailer\Bridge\Mailomat\Transport\MailomatSmtpTransport;
use Symfony\Component\Mailer\Transport\Smtp\Stream\SocketStream;
use Symfony\Component\Mailer\Transport\TransportInterface;

/**
 * Describes Mailomat API and SMTP transports.
 *
 * @implements TransportDescriberInterface<MailomatApiTransport|MailomatSmtpTransport>
 */
final class MailomatTransportDescriber implements TransportDescriberInterface
{
    #[\Override]
    public function supports(TransportInterface $transport): bool
    {
        return $transport instanceof MailomatApiTransport || $transport instanceof MailomatSmtpTransport;
    }

    #[\Override]
    public function describe(TransportInterface $transport): TransportDescriptor
    {
        if ($transport instanceof MailomatSmtpTransport) {
            $stream = $transport->getStream();
            $socket = $stream instanceof SocketStream ? $stream : null;

            return new TransportDescriptor(
                kind: TransportKind::Smtp,
                description: 'Mailomat',
                scheme: $socket?->isTLS() ? 'mailomat+smtps' : 'mailomat+smtp',
                endpoint: $socket?->getHost(),
                port: $socket?->getPort(),
                metadata: ['username' => $transport->getUsername()],
            );
        }

        if ($transport instanceof MailomatApiTransport) {
            return new TransportDescriptor(
                kind: TransportKind::Api,
                description: 'Mailomat',
                scheme: 'mailomat+api',
            );
        }

        // @phpstan-ignore deadCode.unreachable
        throw new \InvalidArgumentException(\sprintf('Unsupported transport "%s".', $transport::class));
    }
}
