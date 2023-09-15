<?php

use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use Maknz\Slack\Client;
use Shippinno\Email\Symfony\SymfonyMailerSendEmail;
use Shippinno\Notification\Domain\Model\EmailDestination;
use Shippinno\Notification\Domain\Model\SlackChannelDestination;
use Shippinno\Notification\Infrastructure\Domain\Model\EmailGateway;
use Shippinno\Notification\Infrastructure\Domain\Model\SlackGateway;
use Shippinno\Template\Liquid;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;
use Tanigami\ValueObjects\Web\EmailAddress;

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
                ),
                false,
                null
            ),
            new EmailAddress(env('NOTIFICATION_EMAIL_FROM', 'from@example.com'))
        ),
        'SlackChannelDestination' => new SlackGateway(
            new Client(env('NOTIFICATION_SLACK_WEBHOOK_URL', 'https://example.com'))
        ),
    ],
    // バージョンアップさせるために一旦コメントアウトしてエラー回避
    // 'template' => new Liquid(
    //     new Filesystem(
    //         new Local(base_path(env('NOTIFICATION_TEMPLATE_DIRECTORY', '')))
    //     )
    // ),
    'template' => '',
];
