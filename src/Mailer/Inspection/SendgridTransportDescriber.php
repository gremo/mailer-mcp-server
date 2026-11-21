<?php

namespace App\Mailer\Inspection;

use App\Mailer\Inspection\Contract\TransportDescriberInterface;
use App\Mailer\Inspection\Contract\TransportDescriptor;
use App\Mailer\Inspection\Contract\TransportKind;
use Symfony\Component\Mailer\Bridge\Sendgrid\Transport\SendgridApiTransport;
use Symfony\Component\Mailer\Bridge\Sendgrid\Transport\SendgridSmtpTransport;
use Symfony\Component\Mailer\Transport\Smtp\Stream\SocketStream;
use Symfony\Component\Mailer\Transport\TransportInterface;

/**
 * Describes Twilio SendGrid API and SMTP transports.
 *
 * @implements TransportDescriberInterface<SendgridApiTransport|SendgridSmtpTransport>
 */
final class SendgridTransportDescriber implements TransportDescriberInterface
{
    #[\Override]
    public function supports(TransportInterface $transport): bool
    {
        return $transport instanceof SendgridApiTransport || $transport instanceof SendgridSmtpTransport;
    }

    #[\Override]
    public function describe(TransportInterface $transport): TransportDescriptor
    {
        if ($transport instanceof SendgridSmtpTransport) {
            $stream = $transport->getStream();
            $socket = $stream instanceof SocketStream ? $stream : null;

            return new TransportDescriptor(
                kind: TransportKind::Smtp,
                description: 'Twilio SendGrid',
                scheme: $socket?->isTLS() ? 'sendgrid+smtps' : 'sendgrid+smtp',
                endpoint: $socket?->getHost(),
                port: $socket?->getPort(),
                metadata: ['username' => $transport->getUsername()],
            );
        }

        if ($transport instanceof SendgridApiTransport) {
            return new TransportDescriptor(
                kind: TransportKind::Api,
                description: 'Twilio SendGrid',
                scheme: 'sendgrid+api',
            );
        }

        // @phpstan-ignore deadCode.unreachable
        throw new \InvalidArgumentException(\sprintf('Unsupported transport "%s".', $transport::class));
    }
}
