<?php

namespace App\Mcp;

use App\Exception\McpServerException;
use App\Mailer\Inspection\Contract\TransportDescriberInterface;
use App\Mailer\Resolver\Contract\MailerArgumentResolverInterface;
use Mcp\Capability\Attribute\McpTool;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Describes the configured mail transport and effective mail settings.
 */
#[McpTool(
    name: 'email_describe_config',
    description: '
        Describe the MCP server\'s mail configuration.

        Returns the transport descriptor, default sender, and attachment limits with configured and effective values
        where applicable. Unset configured values may be null, the raw transport DSN is not returned.
    ',
)]
final class EmailDescribeConfigTool
{
    public function __construct(// @phpstan-ignore missingType.generics
        private readonly MailerArgumentResolverInterface $resolver,
        private readonly TransportDescriberInterface $describer,
        private readonly NormalizerInterface $normalizer,
    ) {
    }

    public function __invoke(): array
    {
        $configuration = [
            [
                'key' => 'from',
                'configured_value' => $this->resolver->getConfiguredSender(),
                'description' => 'Configured default sender address, effective sender is resolved at send time if no explicit sender is passed',
            ],
            [
                'key' => 'max_attachment_count',
                'configured_value' => $this->resolver->getConfiguredMaxAttachmentCount(),
                'effective_value' => $this->resolver->resolveMaxAttachmentCount(),
                'unit' => 'attachments',
                'description' => 'Maximum number of attachments allowed per email',
            ],
            [
                'key' => 'max_attachment_size',
                'configured_value' => $this->resolver->getConfiguredMaxAttachmentSize(),
                'effective_value' => $this->resolver->resolveMaxAttachmentSize(),
                'unit' => 'bytes',
                'description' => 'Maximum raw size per single attachment before encoding',
            ],
            [
                'key' => 'max_total_attachment_size',
                'configured_value' => $this->resolver->getConfiguredMaxTotalAttachmentSize(),
                'effective_value' => $this->resolver->resolveMaxTotalAttachmentSize(),
                'unit' => 'bytes',
                'description' => 'Maximum combined raw size of all attachments in a single email',
            ],
        ];

        try {
            $transport = $this->resolver->resolveTransport();
            $descriptor = $this->describer->describe($transport);

            $configuration = [
                [
                    'key' => 'transport',
                    'configured_value' => $descriptor,
                    'description' => 'Mail transport configuration with sensitive credentials omitted',
                ],
                ...$configuration,
            ];
        } catch (McpServerException $exception) {
            $configuration = [
                [
                    'key' => 'transport',
                    'configured_value' => null,
                    'description' => 'Mail transport is not available: '.$exception->getMessage(),
                ],

                ...$configuration,
            ];
        }

        return $this->normalizer->normalize($configuration);
    }
}
