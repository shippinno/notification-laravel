<?php

use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use Maknz\Slack\Client;
use Shippinno\Email\SymfonyMailer\SymfonyMailerSendEmail;
use Shippinno\Notification\Domain\Model\EmailDestination;
use Shippinno\Notification\Domain\Model\SlackChannelDestination;
use Shippinno\Notification\Infrastructure\Domain\Model\EmailGateway;
use Shippinno\Notification\Infrastructure\Domain\Model\SlackGateway;
use Shippinno\Template\Liquid;
use Tanigami\ValueObjects\Web\EmailAddress;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport\Smtp\SmtpTransport;

return [
    'destinations' => [
        // DestinationRegistry entries
    ],
    'gateways' => [
        // GatewayRegistry entries
        'EmailDestination' => new EmailGateway(
            new SymfonyMailerSendEmail(
                new Mailer(
                    (new SmtpTransport(null, null, null)),
                    null,
                    null
                ),
        ), new EmailAddress(env('NOTIFICATION_EMAIL_FROM', 'from@example.com'))),
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
