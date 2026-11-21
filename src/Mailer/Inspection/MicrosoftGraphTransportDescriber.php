<?php

namespace App\Mailer\Inspection;

use App\Mailer\Inspection\Contract\TransportDescriberInterface;
use App\Mailer\Inspection\Contract\TransportDescriptor;
use App\Mailer\Inspection\Contract\TransportKind;
use Symfony\Component\Mailer\Bridge\MicrosoftGraph\Transport\MicrosoftGraphApiTransport;
use Symfony\Component\Mailer\Transport\TransportInterface;

/**
 * Describes Microsoft Graph transports.
 *
 * @implements TransportDescriberInterface<MicrosoftGraphApiTransport>
 */
final class MicrosoftGraphTransportDescriber implements TransportDescriberInterface
{
    #[\Override]
    public function supports(TransportInterface $transport): bool
    {
        return $transport instanceof MicrosoftGraphApiTransport;
    }

    #[\Override]
    public function describe(TransportInterface $transport): TransportDescriptor
    {
        if ($transport instanceof MicrosoftGraphApiTransport) {
            return new TransportDescriptor(
                kind: TransportKind::Api,
                description: 'Microsoft Graph',
                scheme: 'microsoftgraph+api',
            );
        }

        // @phpstan-ignore deadCode.unreachable
        throw new \InvalidArgumentException(\sprintf('Unsupported transport "%s".', $transport::class));
    }
}
