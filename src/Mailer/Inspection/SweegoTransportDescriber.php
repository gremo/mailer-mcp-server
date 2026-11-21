<?php

namespace App\Mailer\Inspection;

use App\Mailer\Inspection\Contract\TransportDescriberInterface;
use App\Mailer\Inspection\Contract\TransportDescriptor;
use App\Mailer\Inspection\Contract\TransportKind;
use Symfony\Component\Mailer\Bridge\Sweego\Transport\SweegoApiTransport;
use Symfony\Component\Mailer\Bridge\Sweego\Transport\SweegoSmtpTransport;
use Symfony\Component\Mailer\Transport\Smtp\Stream\SocketStream;
use Symfony\Component\Mailer\Transport\TransportInterface;

/**
 * Describes Sweego API and SMTP transports.
 *
 * @implements TransportDescriberInterface<SweegoApiTransport|SweegoSmtpTransport>
 */
final class SweegoTransportDescriber implements TransportDescriberInterface
{
    #[\Override]
    public function supports(TransportInterface $transport): bool
    {
        return $transport instanceof SweegoApiTransport || $transport instanceof SweegoSmtpTransport;
    }

    #[\Override]
    public function describe(TransportInterface $transport): TransportDescriptor
    {
        if ($transport instanceof SweegoSmtpTransport) {
            $stream = $transport->getStream();
            $socket = $stream instanceof SocketStream ? $stream : null;

            return new TransportDescriptor(
                kind: TransportKind::Smtp,
                description: 'Sweego',
                scheme: $socket?->isTLS() ? 'sweego+smtps' : 'sweego+smtp',
                endpoint: $socket?->getHost(),
                port: $socket?->getPort(),
                metadata: ['username' => $transport->getUsername()],
            );
        }

        if ($transport instanceof SweegoApiTransport) {
            return new TransportDescriptor(
                kind: TransportKind::Api,
                description: 'Sweego',
                scheme: 'sweego+api',
            );
        }

        // @phpstan-ignore deadCode.unreachable
        throw new \InvalidArgumentException(\sprintf('Unsupported transport "%s".', $transport::class));
    }
}
