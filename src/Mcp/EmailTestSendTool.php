<?php

namespace App\Mcp;

use App\Exception\McpServerException;
use App\Mailer\Resolver\Contract\MailerArgumentResolverInterface;
use Mcp\Capability\Attribute\McpTool;
use Mcp\Capability\Attribute\Schema;
use Symfony\Component\Mime\Email;

/**
 * Sends a test email through the configured mail transport.
 */
#[McpTool(
    name: 'email_test_send',
    description: '
        Send a test email via the configured mail transport.

        The "to" recipient is required. The optional "from" argument overrides the mailer\'s default sender. The
        subject and plain-text body default to "Testing transport" and "Testing body".
    ',
)]
final class EmailTestSendTool
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

        #[Schema(description: 'Optional email subject; uses the tool default when omitted')]
        string $subject = 'Testing transport',

        #[Schema(description: 'Optional plain-text email body; uses the tool default when omitted')]
        string $body = 'Testing body',
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

        $transport->send((new Email())
            ->to($to)
            ->from($sender)
            ->subject($subject)
            ->text($body)
        );

        return ['sent' => true];
    }
}
