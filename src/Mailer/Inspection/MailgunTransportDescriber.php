<?php

namespace App\Mailer\Inspection;

use App\Mailer\Inspection\Contract\TransportDescriberInterface;
use App\Mailer\Inspection\Contract\TransportDescriptor;
use App\Mailer\Inspection\Contract\TransportKind;
use Symfony\Component\Mailer\Bridge\Mailgun\Transport\MailgunApiTransport;
use Symfony\Component\Mailer\Bridge\Mailgun\Transport\MailgunHttpTransport;
use Symfony\Component\Mailer\Bridge\Mailgun\Transport\MailgunSmtpTransport;
use Symfony\Component\Mailer\Transport\Smtp\Stream\SocketStream;
use Symfony\Component\Mailer\Transport\TransportInterface;

/**
 * Describes Mailgun API, HTTP, and SMTP transports.
 *
 * @implements TransportDescriberInterface<MailgunApiTransport|MailgunHttpTransport|MailgunSmtpTransport>
 */
final class MailgunTransportDescriber implements TransportDescriberInterface
{
    #[\Override]
    public function supports(TransportInterface $transport): bool
    {
        return $transport instanceof MailgunApiTransport
            || $transport instanceof MailgunHttpTransport
            || $transport instanceof MailgunSmtpTransport;
    }

    #[\Override]
    public function describe(TransportInterface $transport): TransportDescriptor
    {
        if ($transport instanceof MailgunSmtpTransport) {
            $stream = $transport->getStream();
            $socket = $stream instanceof SocketStream ? $stream : null;

            return new TransportDescriptor(
                kind: TransportKind::Smtp,
                description: 'Mailgun',
                scheme: $socket?->isTLS() ? 'mailgun+smtps' : 'mailgun+smtp',
                endpoint: $socket?->getHost(),
                port: $socket?->getPort(),
                metadata: ['username' => $transport->getUsername()],
            );
        }

        if ($transport instanceof MailgunHttpTransport) {
            return new TransportDescriptor(
                kind: TransportKind::Api,
                description: 'Mailgun',
                scheme: 'mailgun+https',
            );
        }

        if ($transport instanceof MailgunApiTransport) {
            return new TransportDescriptor(
                kind: TransportKind::Api,
                description: 'Mailgun',
                scheme: 'mailgun+api',
            );
        }

        // @phpstan-ignore deadCode.unreachable
        throw new \InvalidArgumentException(\sprintf('Unsupported transport "%s".', $transport::class));
    }
}
