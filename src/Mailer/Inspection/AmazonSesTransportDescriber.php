<?php

namespace App\Mailer\Inspection;

use App\Mailer\Inspection\Contract\TransportDescriberInterface;
use App\Mailer\Inspection\Contract\TransportDescriptor;
use App\Mailer\Inspection\Contract\TransportKind;
use Symfony\Component\Mailer\Bridge\Amazon\Transport\SesApiAsyncAwsTransport;
use Symfony\Component\Mailer\Bridge\Amazon\Transport\SesHttpAsyncAwsTransport;
use Symfony\Component\Mailer\Bridge\Amazon\Transport\SesSmtpTransport;
use Symfony\Component\Mailer\Transport\Smtp\Stream\SocketStream;
use Symfony\Component\Mailer\Transport\TransportInterface;

/**
 * Describes Amazon SES API, HTTP, and SMTP transports.
 *
 * @implements TransportDescriberInterface<SesApiAsyncAwsTransport|SesHttpAsyncAwsTransport|SesSmtpTransport>
 */
final class AmazonSesTransportDescriber implements TransportDescriberInterface
{
    #[\Override]
    public function supports(TransportInterface $transport): bool
    {
        return $transport instanceof SesApiAsyncAwsTransport
            || $transport instanceof SesHttpAsyncAwsTransport
            || $transport instanceof SesSmtpTransport;
    }

    #[\Override]
    public function describe(TransportInterface $transport): TransportDescriptor
    {
        if ($transport instanceof SesApiAsyncAwsTransport) {
            return new TransportDescriptor(
                kind: TransportKind::Api,
                description: 'Amazon SES',
                scheme: 'ses+api',
            );
        }

        if ($transport instanceof SesHttpAsyncAwsTransport) {
            return new TransportDescriptor(
                kind: TransportKind::Api,
                description: 'Amazon SES',
                scheme: 'ses+https',
            );
        }

        if ($transport instanceof SesSmtpTransport) {
            $stream = $transport->getStream();
            $socket = $stream instanceof SocketStream ? $stream : null;

            return new TransportDescriptor(
                kind: TransportKind::Smtp,
                description: 'Amazon SES',
                scheme: $socket?->isTLS() ? 'ses+smtps' : 'ses+smtp',
                endpoint: $socket?->getHost(),
                port: $socket?->getPort(),
                metadata: ['username' => $transport->getUsername()],
            );
        }

        // @phpstan-ignore deadCode.unreachable
        throw new \InvalidArgumentException(\sprintf('Unsupported transport "%s".', $transport::class));
    }
}
