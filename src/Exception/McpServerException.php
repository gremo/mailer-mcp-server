<?php

namespace App\Exception;

/**
 * Common contract for exceptions that can be reported to MCP clients.
 */
interface McpServerException extends \Throwable
{
    /**
     * Returns a stable code identifying the exception category.
     */
    public string $errorCode { get; }
}
