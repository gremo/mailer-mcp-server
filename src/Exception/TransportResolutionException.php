<?php

namespace App\Exception;

/**
 * Indicates that the mail transport could not be resolved from the configured DSN.
 */
final class TransportResolutionException extends \RuntimeException implements McpServerException
{
    public string $errorCode {
        get => 'transport_resolution_failed';
    }
}
