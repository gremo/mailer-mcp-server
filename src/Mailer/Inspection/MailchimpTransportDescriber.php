<?php

namespace App\Mailer\Inspection;

use App\Mailer\Inspection\Contract\TransportDescriberInterface;
use App\Mailer\Inspection\Contract\TransportDescriptor;
use App\Mailer\Inspection\Contract\TransportKind;
use Symfony\Component\Mailer\Bridge\Mailchimp\Transport\MandrillApiTransport;
use Symfony\Component\Mailer\Bridge\Mailchimp\Transport\MandrillHttpTransport;
use Symfony\Component\Mailer\Bridge\Mailchimp\Transport\MandrillSmtpTransport;
use Symfony\Component\Mailer\Transport\Smtp\Stream\SocketStream;
use Symfony\Component\Mailer\Transport\TransportInterface;

/**
 * Describes Mailchimp Transactional API, HTTP, and SMTP transports.
 *
 * @implements TransportDescriberInterface<MandrillApiTransport|MandrillHttpTransport|MandrillSmtpTransport>
 */
final class MailchimpTransportDescriber implements TransportDescriberInterface
{
    #[\Override]
    public function supports(TransportInterface $transport): bool
    {
        return $transport instanceof MandrillApiTransport
            || $transport instanceof MandrillHttpTransport
            || $transport instanceof MandrillSmtpTransport;
    }

    #[\Override]
    public function describe(TransportInterface $transport): TransportDescriptor
    {
        if ($transport instanceof MandrillSmtpTransport) {
            $stream = $transport->getStream();
            $socket = $stream instanceof SocketStream ? $stream : null;

            return new TransportDescriptor(
                kind: TransportKind::Smtp,
                description: 'Mandrill',
                scheme: $socket?->isTLS() ? 'mandrill+smtps' : 'mandrill+smtp',
                endpoint: $socket?->getHost(),
                port: $socket?->getPort(),
                metadata: ['username' => $transport->getUsername()],
            );
        }

        if ($transport instanceof MandrillHttpTransport) {
            return new TransportDescriptor(
                kind: TransportKind::Api,
                description: 'Mandrill',
                scheme: 'mandrill+https',
            );
        }

        if ($transport instanceof MandrillApiTransport) {
            return new TransportDescriptor(
                kind: TransportKind::Api,
                description: 'Mailchimp Transactional',
                scheme: 'mandrill+api',
            );
        }

        // @phpstan-ignore deadCode.unreachable
        throw new \InvalidArgumentException(\sprintf('Unsupported transport "%s".', $transport::class));
    }
}
