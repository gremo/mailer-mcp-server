<?php

namespace App\Mailer\Inspection\Contract;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\Mailer\Transport\TransportInterface;

/**
 * Defines transport-specific inspection behavior.
 *
 * @template T of TransportInterface
 */
#[AutoconfigureTag]
interface TransportDescriberInterface
{
    /**
     * Determines whether this describer supports the given transport.
     */
    public function supports(TransportInterface $transport): bool;

    /**
     * Returns transport metadata for inspection.
     *
     * @param T $transport
     */
    public function describe(TransportInterface $transport): TransportDescriptor;
}
