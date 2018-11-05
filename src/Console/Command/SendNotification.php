<?php
declare(strict_types=1);

namespace Shippinno\Notification\Laravel\Console\Command;

use Illuminate\Console\Command;
use Shippinno\Notification\Application\Command\SendNotificationHandler;
use Shippinno\Notification\Application\Command\SendNotification as SendNotificationCommand;
use Shippinno\Notification\Domain\Model\NotificationNotFoundException;

class SendNotification extends Command
{
    /**
     * {@inheritdoc}
     */
    protected $signature = 'notification:send {notificationId}';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Send a notification';

    /**
     * @var SendNotificationHandler
     */
    protected $handler;

    /**
     * @param SendNotificationHandler $handler
     */
    public function __construct(SendNotificationHandler $handler)
    {
        parent::__construct();
        $this->handler = $handler;
    }

    /**
     * @return void
     */
    public function handle(): void
    {
        $notificationId = (int) $this->argument('notificationId');
        try {
            $this->handler->handle(new SendNotificationCommand($notificationId));
        } catch (NotificationNotFoundException $e) {
            $this->error(sprintf('Notification (%s) does not exist.', $notificationId));
        }
        $this->info('Successfully sent a notification.');
    }
}
