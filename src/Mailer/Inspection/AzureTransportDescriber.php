<?php

namespace App\Mailer\Inspection;

use App\Mailer\Inspection\Contract\TransportDescriberInterface;
use App\Mailer\Inspection\Contract\TransportDescriptor;
use App\Mailer\Inspection\Contract\TransportKind;
use Symfony\Component\Mailer\Bridge\Azure\Transport\AzureApiTransport;
use Symfony\Component\Mailer\Transport\TransportInterface;

/**
 * Describes Microsoft Azure Communication Services transports.
 *
 * @implements TransportDescriberInterface<AzureApiTransport>
 */
final class AzureTransportDescriber implements TransportDescriberInterface
{
    #[\Override]
    public function supports(TransportInterface $transport): bool
    {
        return $transport instanceof AzureApiTransport;
    }

    #[\Override]
    public function describe(TransportInterface $transport): TransportDescriptor
    {
        return new TransportDescriptor(
            kind: TransportKind::Api,
            description: 'Microsoft Azure Communication Services',
            scheme: 'azure+api',
        );
    }
}
