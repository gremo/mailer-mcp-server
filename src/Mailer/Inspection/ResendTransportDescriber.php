<?php

namespace App\Mailer\Inspection;

use App\Mailer\Inspection\Contract\TransportDescriberInterface;
use App\Mailer\Inspection\Contract\TransportDescriptor;
use App\Mailer\Inspection\Contract\TransportKind;
use Symfony\Component\Mailer\Bridge\Resend\Transport\ResendApiTransport;
use Symfony\Component\Mailer\Bridge\Resend\Transport\ResendSmtpTransport;
use Symfony\Component\Mailer\Transport\Smtp\Stream\SocketStream;
use Symfony\Component\Mailer\Transport\TransportInterface;

/**
 * Describes Resend API and SMTP transports.
 *
 * @implements TransportDescriberInterface<ResendApiTransport|ResendSmtpTransport>
 */
final class ResendTransportDescriber implements TransportDescriberInterface
{
    #[\Override]
    public function supports(TransportInterface $transport): bool
    {
        return $transport instanceof ResendApiTransport || $transport instanceof ResendSmtpTransport;
    }

    #[\Override]
    public function describe(TransportInterface $transport): TransportDescriptor
    {
        if ($transport instanceof ResendSmtpTransport) {
            $stream = $transport->getStream();
            $socket = $stream instanceof SocketStream ? $stream : null;

            return new TransportDescriptor(
                kind: TransportKind::Smtp,
                description: 'Resend',
                scheme: $socket?->isTLS() ? 'resend+smtps' : 'resend+smtp',
                endpoint: $socket?->getHost(),
                port: $socket?->getPort(),
                metadata: ['username' => $transport->getUsername()],
            );
        }

        if ($transport instanceof ResendApiTransport) {
            return new TransportDescriptor(
                kind: TransportKind::Api,
                description: 'Resend',
                scheme: 'resend+api',
            );
        }

        // @phpstan-ignore deadCode.unreachable
        throw new \InvalidArgumentException(\sprintf('Unsupported transport "%s".', $transport::class));
    }
}
