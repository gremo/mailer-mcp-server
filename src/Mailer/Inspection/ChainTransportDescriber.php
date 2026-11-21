<?php

namespace App\Mailer\Inspection;

use App\Mailer\Inspection\Contract\TransportDescriberInterface;
use App\Mailer\Inspection\Contract\TransportDescriptor;
use App\Mailer\Inspection\Contract\TransportKind;
use Symfony\Component\DependencyInjection\Attribute\AsAlias;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Symfony\Component\Mailer\Transport\TransportInterface;

/**
 * Delegates descriptions to the first matching transport describer.
 *
 * @implements TransportDescriberInterface<TransportInterface>
 */
#[AsAlias(TransportDescriberInterface::class)]
final class ChainTransportDescriber implements TransportDescriberInterface
{
    /**
     * @param iterable<TransportDescriberInterface<TransportInterface>> $describers
     */
    public function __construct(
        #[AutowireIterator(TransportDescriberInterface::class)]
        private readonly iterable $describers,
    ) {
    }

    #[\Override]
    public function supports(TransportInterface $transport): bool
    {
        return true;
    }

    #[\Override]
    public function describe(TransportInterface $transport): TransportDescriptor
    {
        foreach ($this->describers as $describer) {
            if ($describer !== $this && $describer->supports($transport)) {
                return $describer->describe($transport);
            }
        }

        return new TransportDescriptor(
            kind: TransportKind::Unknown,
            description: 'Unknown mail transport',
        );
    }
}
