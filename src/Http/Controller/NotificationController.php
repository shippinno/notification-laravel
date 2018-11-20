<?php
declare(strict_types=1);

namespace Shippinno\Notification\Laravel\Http\Controller;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Liquid\Exception\NotFoundException;
use Shippinno\Notification\Application\Query\FetchNotification;
use Shippinno\Notification\Application\Query\FetchNotificationHandler;
use Shippinno\Notification\Application\Query\FilterNotifications;
use Shippinno\Notification\Application\Query\FilterNotificationsHandler;
use Shippinno\Notification\Domain\Model\NotificationIsFreshSpecification;
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
        if ($request->input('filter.is_fresh')) {
            $specifications = new NotificationIsFreshSpecification((bool) $request->input('filter.is_fresh'));
        }
        $specification = count($specifications) !== 0
            ? new AnyOfSpecification($specifications)
            : null;
        $notifications = $handler->handle(
            new FilterNotifications(
                $specification,
                [$request->get('sort') => $request->get('direction')],
                $perPage,
                $perPage * ($page - 1)
            )
        );

        return new JsonResponse(
            $notifications,
            200,
            []
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
            throw new NotFoundException('Notification not found.');
        }

        return new JsonResponse($notification, 200);
    }
}
