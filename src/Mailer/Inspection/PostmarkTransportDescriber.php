<?php

namespace App\Mailer\Inspection;

use App\Mailer\Inspection\Contract\TransportDescriberInterface;
use App\Mailer\Inspection\Contract\TransportDescriptor;
use App\Mailer\Inspection\Contract\TransportKind;
use Symfony\Component\Mailer\Bridge\Postmark\Transport\PostmarkApiTransport;
use Symfony\Component\Mailer\Bridge\Postmark\Transport\PostmarkSmtpTransport;
use Symfony\Component\Mailer\Transport\Smtp\Stream\SocketStream;
use Symfony\Component\Mailer\Transport\TransportInterface;

/**
 * Describes Postmark API and SMTP transports.
 *
 * @implements TransportDescriberInterface<PostmarkApiTransport|PostmarkSmtpTransport>
 */
final class PostmarkTransportDescriber implements TransportDescriberInterface
{
    #[\Override]
    public function supports(TransportInterface $transport): bool
    {
        return $transport instanceof PostmarkApiTransport || $transport instanceof PostmarkSmtpTransport;
    }

    #[\Override]
    public function describe(TransportInterface $transport): TransportDescriptor
    {
        if ($transport instanceof PostmarkSmtpTransport) {
            $stream = $transport->getStream();
            $socket = $stream instanceof SocketStream ? $stream : null;

            return new TransportDescriptor(
                kind: TransportKind::Smtp,
                description: 'Postmark',
                scheme: $socket?->isTLS() ? 'postmark+smtps' : 'postmark+smtp',
                endpoint: $socket?->getHost(),
                port: $socket?->getPort(),
                metadata: ['username' => $transport->getUsername()],
            );
        }

        if ($transport instanceof PostmarkApiTransport) {
            return new TransportDescriptor(
                kind: TransportKind::Api,
                description: 'Postmark',
                scheme: 'postmark+api',
            );
        }

        // @phpstan-ignore deadCode.unreachable
        throw new \InvalidArgumentException(\sprintf('Unsupported transport "%s".', $transport::class));
    }
}
