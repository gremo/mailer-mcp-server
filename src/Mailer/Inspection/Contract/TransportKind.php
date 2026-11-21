<?php

namespace App\Mailer\Inspection\Contract;

/**
 * Categorizes mail transports by their delivery mechanism.
 */
enum TransportKind: string
{
    /**
     * Delivers mail through SMTP.
     */
    case Smtp = 'smtp';

    /**
     * Delivers mail through a provider API.
     */
    case Api = 'api';

    /**
     * Delivers mail through a local process.
     */
    case Local = 'local';

    /**
     * Routes mail through multiple transports.
     */
    case Composite = 'composite';

    /**
     * Discards messages instead of delivering them.
     */
    case Null = 'null';

    /**
     * Uses an unrecognized or unsupported transport mechanism.
     */
    case Unknown = 'unknown';
}
