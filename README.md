# 🤖 Symfony Mailer MCP Server

[![GitHub Actions Workflow Status](https://img.shields.io/github/actions/workflow/status/gremo/mailer-mcp-server/.github/workflows/test-publish.yaml?label=test-publish&style=flat-square)](https://github.com/gremo/mailer-mcp-server/actions/workflows/test-publish.yaml)
[![GitHub last commit](https://img.shields.io/github/last-commit/gremo/mailer-mcp-server?style=flat-square)](https://github.com/gremo/mailer-mcp-server/commits/main)
[![GitHub issues](https://img.shields.io/github/issues/gremo/mailer-mcp-server?style=flat-square)](https://github.com/gremo/mailer-mcp-server/issues)
[![GitHub pull requests](https://img.shields.io/github/issues-pr/gremo/mailer-mcp-server?style=flat-square)](https://github.com/gremo/mailer-mcp-server/pulls)
[![DockerHub](https://img.shields.io/badge/Docker-2496ED?logo=docker&logoColor=fff&style=flat-square)](https://hub.docker.com/r/gremo/mailer-mcp-server)

A fast, small-footprint, no-frills, send-only Streamable HTTP MCP server available as a Docker image.

A Symfony-based Model Context Protocol (MCP) server supports the following mail transports: SMTP, Sendmail, native (uses `sendmail_path` from `php.ini`; falls back to `smtp` on Windows), AhaSend, Amazon SES, Azure, Brevo, Infobip, Mailgun, Mailjet, Mailomat, MailPace, MailerSend, Mailtrap, Mandrill, Microsoft Graph, Postal, Postmark, Resend, Scaleway, SendGrid, and Sweego.

## 🛠️ MCP tools

| Name | Description |
| --- | --- |
| `email_send` | Requires `to` and at least one of `text` or `html` (or both). `from`, `subject`, `cc`, `bcc`, `replyTo`, `priority` (1 highest, 5 lowest), and `attachments` are optional. Attachments require `filename`, `contentType`, and base64-encoded `contentBase64`, subject to configured count and size limits. |
| `email_test_send` | Send a test email to the required recipient. The sender defaults to `MAILER_FROM`; subject and body have built-in defaults. |
| `email_describe_config` | Describe the MCP server's mail configuration: transport descriptor, default sender, and attachment limits. Shows configured and effective values where applicable; unset values may be `null`. The raw transport DSN is not returned. |

## 🚀 Deploy

> [!IMPORTANT]
> This server does not provide authentication or authorization, do not expose its endpoint directly to the public internet.

The following command starts the MCP server container named `mailer`, with a transport and default sender configured, and connects it to a user-defined Docker network `traefik` (which must be created beforehand):

```bash
docker run -d \
  --name mailer \
  --network traefik \
  -e MAILER_DSN="smtp://user:pass@smtp.example.com:25" \
  -e MAILER_FROM="llm@example.com" \
  gremo/mailer-mcp-server:v1
```

Container and network names are just examples: you can choose different names. Any MCP client container (e.g. Open WebUI) that needs to use the server **must join the same network** and use `http://<container-name>` (or the configured network alias) as its MCP endpoint.

The server's behavior can be configured using the following environment variables:

| Variable | Default | Description |
| --- | --- | --- |
| `MAILER_DSN` | Unset. | Required mail transport DSN; sending is unavailable if unset. See the [Symfony Mailer documentation](https://symfony.com/doc/current/mailer.html#using-built-in-transports) for supported DSN formats. |
| `MAILER_FROM` | Unset. | Default sender address. If unset, a sender must be provided for each message. |
| `MAILER_MAX_ATTACHMENT_COUNT` | `10` | Maximum number of attachments per email. |
| `MAILER_MAX_ATTACHMENT_SIZE` | `10485760` (10 MiB) | Maximum size, in bytes, of each attachment. |
| `MAILER_MAX_TOTAL_ATTACHMENT_SIZE` | `26214400` (25 MiB) | Maximum combined size, in bytes, of all attachments in an email. |

### 💻 Development

1. Install and start Docker
2. Install Visual Studio Code and the [Remote Development extension pack](https://marketplace.visualstudio.com/items?itemName=ms-vscode-remote.vscode-remote-extensionpack), which includes Dev Containers.
3. Open the project folder and select **Dev Containers: Reopen in Container** from the Command Palette. The [Dev Container configuration](.devcontainer/devcontainer.json) opens the workspace in the PHP container and installs the development extensions listed in its configuration.
4. In the integrated VS Code terminal inside the Dev Container, install the PHP dependencies:

   ```bash
   composer install
   ```

The development services are available at:

| Address | Description |
| --- | --- |
| `http://localhost` | MCP endpoint. |
| [http://localhost:6274](http://localhost:6274) | MCP Inspector interface (preconfigured with the MCP server URL). |
| [http://localhost:8025](http://localhost:8025) | Mailpit web interface (for debugging purposes). |

In development, the PHP container uses Mailpit as its SMTP transport (`mailpit:1025`, configured through `MAILER_DSN`) and `no-reply@example.com` as `MAILER_FROM`. Mailpit captures outgoing messages instead of delivering them to real recipients, so you can inspect them in its web interface.

## ❤️ Contributing

All types of contributions are encouraged and valued. See the [contributing](.github/CONTRIBUTING.md) guidelines, the community looks forward to your contributions!

## 📘 License

Released under the terms of the [ISC License](LICENSE).
