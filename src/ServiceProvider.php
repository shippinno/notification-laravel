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
            $this->configPath() => config_path('notification.php')
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
        $this->mergeConfigFrom(
            $this->configPath(), 'notificationdo'
        );

        $this->app->singleton(DestinationRegistry::class, function () {
            $destinationRegistry = new DestinationRegistry;
            $destinationRegistry->setAll($this->app->make('config')->get('notification.destinations', []));
            return $destinationRegistry;
        });

        $this->app->singleton(GatewayRegistry::class, function () {
            $gatewayRegistry = new GatewayRegistry;
            $gatewayRegistry->setAll($this->app->make('config')->get('notification.gateways', []));
            return $gatewayRegistry;
        });

        if (!is_null($this->app->make('config')->get('notification.template', null))) {
            $this->app->singleton(TemplateNotificationFactory::class, function () {
                return new TemplateNotificationFactory($this->app->make('config')->get('notification.template'));
            });
        }

        $this->app->singleton(SendNotification::class, function () {
            return new SendNotification($this->app->make(GatewayRegistry::class));
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

    /**
     * @return string
     */
    private function configPath(): string
    {
        return __DIR__ . '/../config/notification.php';
    }
}