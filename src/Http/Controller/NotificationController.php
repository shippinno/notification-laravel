<?php
declare(strict_types=1);

namespace Shippinno\Notification\Laravel\Http\Controller;

use Doctrine\Common\Collections\Expr\Comparison;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Liquid\Exception\NotFoundException;
use Shippinno\Notification\Application\Command\SendNotification;
use Shippinno\Notification\Application\Command\SendNotificationHandler;
use Shippinno\Notification\Application\Query\FetchNotification;
use Shippinno\Notification\Application\Query\FetchNotificationHandler;
use Shippinno\Notification\Application\Query\FilterNotifications;
use Shippinno\Notification\Application\Query\FilterNotificationsHandler;
use Shippinno\Notification\Domain\Model\NotificationIsFailedSpecification;
use Shippinno\Notification\Domain\Model\NotificationIsFreshSpecification;
use Shippinno\Notification\Domain\Model\NotificationIsLockedSpecification;
use Shippinno\Notification\Domain\Model\NotificationIsSentSpecification;
use Shippinno\Notification\Domain\Model\NotificationMetadataSpecification;
use Shippinno\Notification\Domain\Model\NotificationNotFoundException;
use Tanigami\Specification\AnyOfSpecification;

class NotificationController extends Controller
{
    /**
     * @param Request $request
     * @param FilterNotificationsHandler $handler
     * @return JsonResponse
     */
    public function index(Request $request, FilterNotificationsHandler $handler)
    {
        $perPage = 50;
        $page = $request->input('page', 1);
        $specifications = [];
        if (!is_null($request->input('filters.fresh'))) {
            $specifications[] = new NotificationIsFreshSpecification((bool) $request->input('filters.fresh'));
        }
        if (!is_null($request->input('filters.locked'))) {
            $specifications[] = new NotificationIsLockedSpecification((bool) $request->input('filters.locked'));
        }
        if (!is_null($request->input('filters.failed'))) {
            $specifications[] = new NotificationIsFailedSpecification((bool) $request->input('filters.failed'));
        }
        if (!is_null($request->input('filters.sent'))) {
            $specifications[] = new NotificationIsSentSpecification((bool) $request->input('filters.sent'));
        }
        if (!is_null($request->input('filters.metadata'))) {
            foreach ($request->input('filters.metadata') as $field => $filter) {
                foreach ($filter as $operator => $value) {
                    $operator = constant(Comparison::class . '::' . strtoupper($operator));
                    $specifications[] = new NotificationMetadataSpecification($field, $operator, $value);
                }
            }
        }
        $specification = count($specifications) !== 0
            ? new AnyOfSpecification(...$specifications)
            : null;
        $notifications = $handler->handle(
            new FilterNotifications(
                $specification,
                ['notificationId' => 'DESC'],
                $perPage,
                $perPage * ($page - 1)
            )
        );

        return new JsonResponse(
            $notifications,
            200
        );
    }

    /**
     * @param int $notificationId
     * @param FetchNotificationHandler $handler
     * @return JsonResponse
     * @throws NotFoundException
     */
    public function show(int $notificationId, FetchNotificationHandler $handler)
    {
        try {
            $notification = $handler->handle(new FetchNotification($notificationId));
        } catch (NotificationNotFoundException $e) {
            throw new NotFoundException(sprintf('Notification not found: %s', $notificationId));
        }

        return new JsonResponse($notification, 200);
    }

    /**
     * @param int $notificationId
     * @param SendNotificationHandler $handler
     * @throws NotFoundException
     */
    public function send(int $notificationId, SendNotificationHandler $handler)
    {
        try {
            $handler->handle(new SendNotification($notificationId));
        } catch (NotificationNotFoundException $e) {
            throw new NotFoundException(sprintf('Notification not found: %s', $notificationId));
        }
    }
}
