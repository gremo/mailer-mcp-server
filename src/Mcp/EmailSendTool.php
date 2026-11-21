<?php

namespace App\Mcp;

use App\Exception\McpServerException;
use App\Mailer\Resolver\Contract\MailerArgumentResolverInterface;
use Mcp\Capability\Attribute\McpTool;
use Mcp\Capability\Attribute\Schema;
use Symfony\Component\Mime\Email;

/**
 * Sends an email with optional recipients, priority, and attachments.
 */
#[McpTool(
    name: 'email_send',
    description: '
        Send an email using the configured mail transport.

        The required "to" recipient and at least one of "text" or "html" (or both) must be provided. The optional
        "from" argument overrides the mailer\'s default sender. Subject, CC, BCC, Reply-To, priority, and attachments
        are optional. Priority ranges from 1 (highest) to 5 (lowest). Each attachment requires filename, contentType,
        and contentBase64, and is subject to the configured count and size limits.
    ',
)]
final class EmailSendTool
{
    public function __construct(
        private readonly MailerArgumentResolverInterface $resolver,
    ) {
    }

    public function __invoke(
        #[Schema(description: 'Required recipient email address', format: 'email')]
        string $to,

        #[Schema(
            definition: [
                'description' => "Optional sender email address that overrides the mailer's default sender",
                'anyOf' => [['type' => 'string', 'format' => 'email'], ['type' => 'null']],
            ],
        )]
        ?string $from = null,

        #[Schema(
            definition: [
                'description' => 'Optional email subject',
                'anyOf' => [['type' => 'string'], ['type' => 'null']],
            ],
        )]
        ?string $subject = null,

        #[Schema(
            definition: [
                'description' => 'Optional plain-text body that can be sent with or without HTML',
                'anyOf' => [['type' => 'string'], ['type' => 'null']],
            ],
        )]
        ?string $text = null,

        #[Schema(
            definition: [
                'description' => 'Optional HTML body that can be sent with or without plain text',
                'anyOf' => [['type' => 'string'], ['type' => 'null']],
            ],
        )]
        ?string $html = null,

        #[Schema(description: 'Optional CC recipient addresses', type: 'array', items: ['type' => 'string', 'format' => 'email'])]
        array $cc = [],

        #[Schema(description: 'Optional BCC recipient addresses', type: 'array', items: ['type' => 'string', 'format' => 'email'])]
        array $bcc = [],

        #[Schema(description: 'Optional Reply-To addresses', type: 'array', items: ['type' => 'string', 'format' => 'email'])]
        array $replyTo = [],

        #[Schema(
            definition: [
                'description' => 'Optional message priority: 1 (highest) through 5 (lowest)',
                'anyOf' => [
                    [
                        'type' => 'integer',
                        'enum' => [Email::PRIORITY_HIGHEST, Email::PRIORITY_HIGH, Email::PRIORITY_NORMAL, Email::PRIORITY_LOW, Email::PRIORITY_LOWEST],
                    ],
                    ['type' => 'null'],
                ],
            ],
        )]
        ?int $priority = null,

        #[Schema(
            description: 'Optional attachments with filename, contentType, and contentBase64 fields, subject to the configured count and size limits',
            type: 'array',
            items: [
                'type' => 'object',
                'properties' => [
                    'filename' => ['type' => 'string', 'minLength' => 1, 'maxLength' => 255],
                    'contentType' => ['type' => 'string', 'minLength' => 1],
                    'contentBase64' => ['type' => 'string', 'minLength' => 1],
                ],
                'required' => ['filename', 'contentType', 'contentBase64'],
                'additionalProperties' => false,
            ],
        )]
        array $attachments = [],
    ): array {
        try {
            $sender = $this->resolver->resolveSender($from);
            $transport = $this->resolver->resolveTransport();
        } catch (McpServerException $exception) {
            return [
                'sent' => false,
                'code' => $exception->errorCode,
                'error' => $exception->getMessage(),
            ];
        }

        if (null === $text && null === $html) {
            return [
                'sent' => false,
                'error' => 'Provide a plain-text or HTML email body.',
            ];
        }

        $message = (new Email())
            ->from($sender)
            ->to($to)
        ;

        if (null !== $subject) {
            $message->subject($subject);
        }

        if (null !== $text) {
            $message->text($text);
        }

        if ([] !== $cc) {
            $message->cc(...$cc);
        }

        if ([] !== $bcc) {
            $message->bcc(...$bcc);
        }

        if ([] !== $replyTo) {
            $message->replyTo(...$replyTo);
        }

        if (null !== $html) {
            $message->html($html);
        }

        if (null !== $priority) {
            $message->priority($priority);
        }

        $maxAttachmentCount = $this->resolver->resolveMaxAttachmentCount();
        $maxAttachmentSize = $this->resolver->resolveMaxAttachmentSize();
        $maxTotalAttachmentSize = $this->resolver->resolveMaxTotalAttachmentSize();

        // Enforce the configured count before processing any attachment content.
        if (\count($attachments) > $maxAttachmentCount) {
            return [
                'sent' => false,
                'error' => \sprintf('Provide no more than %d attachments.', $maxAttachmentCount),
            ];
        }

        $totalAttachmentSize = 0;
        foreach ($attachments as $attachment) {
            // Validate the untrusted attachment shape and field types before accessing its values.
            if (
                !\is_array($attachment)
                || !isset($attachment['filename'], $attachment['contentType'], $attachment['contentBase64'])
                || !\is_string($attachment['filename'])
                || !\is_string($attachment['contentType'])
                || !\is_string($attachment['contentBase64'])
            ) {
                return [
                    'sent' => false,
                    'error' => 'Each attachment must include a filename, content type, and base64-encoded content.',
                ];
            }

            $filename = $attachment['filename'];
            $contentType = $attachment['contentType'];
            $encodedContent = $attachment['contentBase64'];

            // Reject unsafe filenames and malformed media types before passing metadata to the mailer
            if (
                '' === $filename
                || \strlen($filename) > 255
                || 1 === preg_match('/[\/\\\\\x00-\x1F\x7F]/', $filename)
                || 1 !== preg_match('/\A[a-z0-9!#$&^_.+-]+\/[a-z0-9!#$&^_.+-]+\z/i', $contentType)
            ) {
                return [
                    'sent' => false,
                    'error' => 'Attachment filename or content type is invalid.',
                ];
            }

            // Bound the encoded input first to avoid decoding an obviously oversized attachment
            if (\strlen($encodedContent) > 4 * (int) ceil($maxAttachmentSize / 3)) {
                return [
                    'sent' => false,
                    'error' => \sprintf('An attachment exceeds the maximum size of %d bytes.', $maxAttachmentSize),
                ];
            }

            // Strict decoding rejects invalid base64 instead of silently ignoring invalid characters
            $content = base64_decode($encodedContent, true);
            if (false === $content) {
                return [
                    'sent' => false,
                    'error' => \sprintf('Attachment "%s" must contain valid base64 data.', $filename),
                ];
            }

            // Enforce limits against decoded bytes, including the combined size of prior attachments
            $contentSize = \strlen($content);
            if (
                $contentSize > $maxAttachmentSize
                || $totalAttachmentSize + $contentSize > $maxTotalAttachmentSize
            ) {
                return [
                    'sent' => false,
                    'error' => \sprintf(
                        'Attachments exceed the maximum size of %d bytes per file or %d bytes in total.',
                        $maxAttachmentSize,
                        $maxTotalAttachmentSize,
                    ),
                ];
            }

            $message->attach($content, $filename, $contentType);
            $totalAttachmentSize += $contentSize;
        }

        $transport->send($message);

        return ['sent' => true];
    }
}
