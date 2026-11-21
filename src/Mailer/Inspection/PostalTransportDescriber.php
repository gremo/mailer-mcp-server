<?php

namespace App\Mailer\Inspection;

use App\Mailer\Inspection\Contract\TransportDescriberInterface;
use App\Mailer\Inspection\Contract\TransportDescriptor;
use App\Mailer\Inspection\Contract\TransportKind;
use Symfony\Component\Mailer\Bridge\Postal\Transport\PostalApiTransport;
use Symfony\Component\Mailer\Transport\TransportInterface;

/**
 * Describes Postal API transports.
 *
 * @implements TransportDescriberInterface<PostalApiTransport>
 */
final class PostalTransportDescriber implements TransportDescriberInterface
{
    #[\Override]
    public function supports(TransportInterface $transport): bool
    {
        return $transport instanceof PostalApiTransport;
    }

    #[\Override]
    public function describe(TransportInterface $transport): TransportDescriptor
    {
        if ($transport instanceof PostalApiTransport) {
            return new TransportDescriptor(
                kind: TransportKind::Api,
                description: 'Postal',
                scheme: 'postal+api',
            );
        }

        // @phpstan-ignore deadCode.unreachable
        throw new \InvalidArgumentException(\sprintf('Unsupported transport "%s".', $transport::class));
    }
}
