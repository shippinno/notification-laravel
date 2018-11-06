<?php

use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use Maknz\Slack\Client;
use Shippinno\Email\SwiftMailer\SwiftMailerSendEmail;
use Shippinno\Notification\Domain\Model\EmailDestination;
use Shippinno\Notification\Domain\Model\SlackChannelDestination;
use Shippinno\Notification\Infrastructure\Domain\Model\EmailGateway;
use Shippinno\Notification\Infrastructure\Domain\Model\SlackGateway;
use Shippinno\Template\Liquid;
use Tanigami\ValueObjects\Web\EmailAddress;

return [
    'destinations' => [
        'defaultEmail' => new EmailDestination(
            [new EmailAddress(env('NOTIFICATION_EMAIL_TO', 'to@example.com'))]
        ),
        'defaultSlackChannel' => new SlackChannelDestination(
            env('NOTIFICATION_SLACK_CHANNEL', 'channel')
        ),
    ],
    'gateways' => [
        'EmailDestination' => new EmailGateway(
            new SwiftMailerSendEmail(
                new Swift_Mailer(
                    (new Swift_SmtpTransport(
                        env('MAIL_HOST', 'example.com'),
                        env('MAIL_PORT', 25),
                        env('MAIL_ENCRYPTION', null)))
                        ->setUsername(env('MAIL_USERNAME', 'username'))
                        ->setPassword(env('MAIL_PASSWORD', 'password'))
                ),
                true
            ),
            new EmailAddress(env('NOTIFICATION_EMAIL_FROM', 'from@example.com'))
        ),
        'SlackChannelDestination' => new SlackGateway(new Client(env('NOTIFICATION_SLACK_WEBHOOK_URL', 'https://example.com')))
    ],
    'template' => new Liquid(new Filesystem(new Local(base_path(env('NOTIFICATION_TEMPLATE_DIRECTORY', ''))))),
];
