<?php
declare(strict_types=1);

namespace Shippinno\Notification\Laravel;

use Doctrine\Common\Persistence\ManagerRegistry;
use Shippinno\Notification\Domain\Model\DestinationRegistry;
use Shippinno\Notification\Domain\Model\GatewayRegistry;
use Shippinno\Notification\Domain\Model\Notification;
use Shippinno\Notification\Domain\Model\NotificationRepository;
use Shippinno\Notification\Domain\Model\SendNotification;
use Shippinno\Notification\Domain\Model\TemplateNotificationFactory;
use Shippinno\Notification\Infrastructure\Domain\Model\DoctrineNotificationRepository;
use Shippinno\Notification\Laravel\Console\Command\SendFreshNotifications;
use Shippinno\Notification\Laravel\Console\Command\SendNotification as SendNotificationCommand;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    /**
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/notification.php' => config_path('notification.php')
        ], 'config');

        if ($this->app->runningInConsole()) {
            $this->commands([
                SendFreshNotifications::class,
                SendNotificationCommand::class,
            ]);
        }
    }

    /**
     * @return void
     */
    public function register(): void
    {
        $this->app->singleton(DestinationRegistry::class, function () {
            $destinationRegistry = new DestinationRegistry;
            $destinationRegistry->setAll(config('notification.destinations'));
            return $destinationRegistry;
        });

        $this->app->singleton(GatewayRegistry::class, function () {
            $gatewayRegistry = new GatewayRegistry;
            $gatewayRegistry->setAll(config('notification.gateways'));
            return $gatewayRegistry;
        });

        $this->app->singleton(TemplateNotificationFactory::class, function () {
            return new TemplateNotificationFactory(config('notification.template'));
        });

        $this->app->singleton(SendNotification::class, function () {
            $gatewayRegistry = new GatewayRegistry;
            $gatewayRegistry->setAll(config('notification.gateways'));
            return new SendNotification($gatewayRegistry);
        });

        $this->app->singleton(NotificationRepository::class, function () {
            $entityManager = $this->app->make(ManagerRegistry::class)->getManager('notifications');
            return new DoctrineNotificationRepository(
                $entityManager,
                $entityManager->getClassMetadata(Notification::class),
                true
            );
        });
    }
}