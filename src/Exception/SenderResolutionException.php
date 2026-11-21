<?php

namespace App\Exception;

/**
 * Indicates that no sender address could be resolved for an email.
 */
final class SenderResolutionException extends \RuntimeException implements McpServerException
{
    public string $errorCode {
        get => 'sender_resolution_failed';
    }
}
