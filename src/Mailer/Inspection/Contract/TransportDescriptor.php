<?php

namespace App\Mailer\Inspection\Contract;

/**
 * Describes a resolved mail transport.
 *
 * Includes its human-readable description, kind, DSN information, and additional metadata.
 */
final readonly class TransportDescriptor
{
    /**
     * @param string                         $description Human-readable transport description.
     * @param TransportKind                  $kind        General category of the transport.
     * @param string|null                    $scheme      DSN scheme used by the transport.
     * @param string|null                    $endpoint    Host or base URL, when available.
     * @param int|null                       $port        Network port, when available.
     * @param array<string, string|int|bool> $metadata    Additional transport-specific metadata.
     */
    public function __construct(
        /**
         * Human-readable transport description.
         */
        public string $description,

        /**
         * General category of the transport.
         */
        public TransportKind $kind,

        /**
         * DSN scheme used by the transport.
         */
        public ?string $scheme = null,

        /**
         * Host or base URL, when available.
         */
        public ?string $endpoint = null,

        /**
         * Network port, when available.
         */
        public ?int $port = null,

        /**
         * Additional transport-specific metadata.
         */
        public array $metadata = [],
    ) {
    }
}
