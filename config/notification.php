<?php

use League\Flysystem\Filesystem;
use League\Flysystem\Local\LocalFilesystemAdapter;
use Maknz\Slack\Client;
use Shippinno\Email\SymfonyMailer\SymfonyMailerSendEmail;
use Shippinno\Notification\Infrastructure\Domain\Model\EmailGateway;
use Shippinno\Notification\Infrastructure\Domain\Model\SlackGateway;
use Shippinno\Template\Liquid;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;
use Tanigami\ValueObjects\Web\EmailAddress;
use Symfony\Component\Mailer\Mailer;

return [
    'destinations' => [
        // DestinationRegistry entries
    ],
    'gateways' => [
        // GatewayRegistry entries
        'EmailDestination' => new EmailGateway(
            new SymfonyMailerSendEmail(
                new Mailer(
                    (new EsmtpTransport(
                        env('MAIL_HOST', 'example.com'),
                        env('MAIL_PORT', 25),
                        env('MAIL_ENCRYPTION', null)))
                        ->setUsername(env('MAIL_USERNAME', 'username'))
                        ->setPassword(env('MAIL_PASSWORD', 'password'))
                ),
            ), new EmailAddress(env('NOTIFICATION_EMAIL_FROM', 'from@example.com'))),
        'SlackChannelDestination' => new SlackGateway(
            new Client(env('NOTIFICATION_SLACK_WEBHOOK_URL', 'https://example.com'))
        ),
    ],
    'template' => new Liquid(
        new Filesystem(
            new LocalFilesystemAdapter(base_path(env('NOTIFICATION_TEMPLATE_DIRECTORY', '')))
        )
    ),
];
