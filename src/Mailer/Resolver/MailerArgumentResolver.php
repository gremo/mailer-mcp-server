<?php

namespace App\Mailer\Resolver;

use App\Exception\SenderResolutionException;
use App\Exception\TransportResolutionException;
use App\Mailer\Resolver\Contract\MailerArgumentResolverInterface;
use Symfony\Component\DependencyInjection\Attribute\AsAlias;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Mailer\Exception\ExceptionInterface as MailerExceptionInterface;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Transport\TransportInterface;

/**
 * Resolves mailer configuration and transports.
 */
#[AsAlias(MailerArgumentResolverInterface::class)]
final class MailerArgumentResolver implements MailerArgumentResolverInterface
{
    private const int MAILER_MAX_ATTACHMENT_COUNT = 10;

    private const int MAILER_MAX_ATTACHMENT_SIZE = 10485760; // 10 MB

    private const int MAILER_MAX_TOTAL_ATTACHMENT_SIZE = 26214400; // 25 MB

    /**
     * @param string|null $defaultDsn             Configured transport DSN.
     * @param int|null    $maxAttachmentCount     Maximum attachment count.
     * @param int|null    $maxAttachmentSize      Maximum size per attachment.
     * @param int|null    $maxTotalAttachmentSize Maximum combined attachment size.
     * @param string|null $defaultSender          Configured default sender.
     */
    public function __construct(
        /**
         * Configured transport DSN.
         */
        #[Autowire('%env(default::MAILER_DSN)%')]
        private readonly ?string $defaultDsn = null,

        /**
         * Maximum attachment count.
         */
        #[Autowire('%env(default::int:MAILER_MAX_ATTACHMENT_COUNT)%')]
        private readonly ?int $maxAttachmentCount = null,

        /**
         * Maximum size per attachment.
         */
        #[Autowire('%env(default::int:MAILER_MAX_ATTACHMENT_SIZE)%')]
        private readonly ?int $maxAttachmentSize = null,

        /**
         * Maximum combined attachment size.
         */
        #[Autowire('%env(default::int:MAILER_MAX_TOTAL_ATTACHMENT_SIZE)%')]
        private readonly ?int $maxTotalAttachmentSize = null,

        /**
         * Configured default sender.
         */
        #[Autowire('%env(default::MAILER_FROM)%')]
        private readonly ?string $defaultSender = null,
    ) {
    }

    #[\Override]
    public function getConfiguredSender(): ?string
    {
        return $this->defaultSender;
    }

    #[\Override]
    public function resolveSender(?string $sender = null): string
    {
        $sender ??= $this->getConfiguredSender();

        if (null === $sender || '' === trim($sender)) {
            throw new SenderResolutionException('Missing sender address: pass "from" or configure MAILER_FROM.');
        }

        return $sender;
    }

    #[\Override]
    public function hasConfiguredTransport(): bool
    {
        return null !== $this->defaultDsn && '' !== trim($this->defaultDsn);
    }

    #[\Override]
    public function resolveTransport(): TransportInterface
    {
        if (null === $this->defaultDsn || '' === trim($this->defaultDsn)) {
            throw new TransportResolutionException('Missing mail transport DSN: configure MAILER_DSN.');
        }

        try {
            return Transport::fromDsn($this->defaultDsn);
        } catch (MailerExceptionInterface $exception) {
            throw new TransportResolutionException('Unable to resolve the mail transport DSN from MAILER_DSN. Check that the DSN is valid and uses a supported transport scheme.', previous: $exception);
        }
    }

    #[\Override]
    public function getConfiguredMaxAttachmentCount(): ?int
    {
        return $this->maxAttachmentCount;
    }

    #[\Override]
    public function resolveMaxAttachmentCount(): int
    {
        return $this->getConfiguredMaxAttachmentCount() ?? self::MAILER_MAX_ATTACHMENT_COUNT;
    }

    #[\Override]
    public function getConfiguredMaxAttachmentSize(): ?int
    {
        return $this->maxAttachmentSize;
    }

    #[\Override]
    public function resolveMaxAttachmentSize(): int
    {
        return $this->getConfiguredMaxAttachmentSize() ?? self::MAILER_MAX_ATTACHMENT_SIZE;
    }

    #[\Override]
    public function getConfiguredMaxTotalAttachmentSize(): ?int
    {
        return $this->maxTotalAttachmentSize;
    }

    #[\Override]
    public function resolveMaxTotalAttachmentSize(): int
    {
        return $this->getConfiguredMaxTotalAttachmentSize() ?? self::MAILER_MAX_TOTAL_ATTACHMENT_SIZE;
    }
}
