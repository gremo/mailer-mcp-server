<?php

namespace App\Mailer\Resolver\Contract;

use Symfony\Component\Mailer\Transport\TransportInterface;

/**
 * Resolves mailer configuration and transports.
 */
interface MailerArgumentResolverInterface
{
    /**
     * Returns the configured default sender.
     */
    public function getConfiguredSender(): ?string;

    /**
     * Resolves the provided sender or configured default.
     *
     * @param string|null $sender Sender override, if any
     *
     * @return string Resolved sender address
     */
    public function resolveSender(?string $sender = null): string;

    /**
     * Checks whether a transport DSN is configured.
     */
    public function hasConfiguredTransport(): bool;

    /**
     * Creates a transport from the configured DSN.
     *
     * @return TransportInterface Resolved mail transport
     */
    public function resolveTransport(): TransportInterface;

    /**
     * Returns the configured maximum attachment count, if set.
     */
    public function getConfiguredMaxAttachmentCount(): ?int;

    /**
     * Returns the maximum attachment count.
     */
    public function resolveMaxAttachmentCount(): int;

    /**
     * Returns the configured maximum size per attachment, if set.
     */
    public function getConfiguredMaxAttachmentSize(): ?int;

    /**
     * Returns the maximum size per attachment.
     */
    public function resolveMaxAttachmentSize(): int;

    /**
     * Returns the configured maximum combined attachment size, if set.
     */
    public function getConfiguredMaxTotalAttachmentSize(): ?int;

    /**
     * Returns the maximum combined attachment size.
     */
    public function resolveMaxTotalAttachmentSize(): int;
}
