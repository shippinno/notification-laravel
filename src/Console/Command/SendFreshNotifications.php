<?php
declare(strict_types=1);

namespace Shippinno\Notification\Laravel\Console\Command;

use Illuminate\Console\Command;
use Shippinno\Notification\Application\Command\SendFreshNotifications as SendFreshNotificationsCommand;
use Shippinno\Notification\Application\Command\SendFreshNotificationsHandler;
use Tanigami\Specification\Specification;

class SendFreshNotifications extends Command
{
    /**
     * {@inheritdoc}
     */
    protected $signature = 'notification:send:fresh';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Send fresh notifications.';

    /**
     * @var SendFreshNotificationsHandler
     */
    protected $handler;

    /**
     * @param SendFreshNotificationsHandler $handler
     */
    public function __construct(SendFreshNotificationsHandler $handler)
    {
        parent::__construct();
        $this->handler = $handler;
    }

    /**
     * @return void
     */
    public function handle(): void
    {
        $this->handler->handle(new SendFreshNotificationsCommand($this->specification()));
        $this->info('Tried to send fresh notifications (some might have failed).');
    }

    /**
     * @return null|Specification
     */
    protected function specification(): ?Specification
    {
        return null;
    }
}
