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
            [new EmailAddress(env('NOTIFICATION_EMAIL_FROM'))]
        ),
        'defaultSlackChannel' => new SlackChannelDestination(
            env('NOTIFICATION_SLACK_CHANNEL')
        ),
    ],
    'gateways' => [
        'EmailDestination' => new EmailGateway(
            new SwiftMailerSendEmail(
                new Swift_Mailer(
                    (new Swift_SmtpTransport(
                        env('MAIL_HOST'),
                        env('MAIL_PORT'),
                        env('MAIL_ENCRYPTION'
                        )))
                        ->setUsername(env('MAIL_USERNAME'))
                        ->setPassword(env('MAIL_PASSWORD'))
                ),
                true
            ),
            new EmailAddress(env('NOTIFICATION_EMAIL_FROM'))
        ),
        'SlackChannelDestination' => new SlackGateway(new Client(env('NOTIFICATION_SLACK_WEBHOOK_URL')))
    ],
    'template' => new Liquid(new Filesystem(new Local(base_path(env('NOTIFICATION_TEMPLATE_DIRECTORY'))))),
];
