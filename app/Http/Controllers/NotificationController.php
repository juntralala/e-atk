<?php

namespace App\Http\Controllers;

use App\Dto\Response\Notification;
use App\Dto\Response\PaginatedResponse;
use Illuminate\Http\Request;

/*
notification diambil secara rest api karna aplikasi dideploy pakai apache2,
jadi ya cuman bisa ngandelin http server
*/
class NotificationController extends Controller
{
    public function getCurrentUserNotifications(Request $request)
    {
        $page = $request->integer('page', 1);
        $data = collect();
        $notifications = auth()->user()->notifications()->paginate(page: $page, perPage: 5);
        foreach ($notifications as $notification) {
            $data->add(
                new Notification(
                    $notification->id,
                    $notification->data['title'] ?? null,
                    $notification->data['message'],
                    $notification->data['url'] ?? null,
                    $notification->data['icon'] ?? null,
                    $notification->created_at,
                    $notification->read_at
                )
            );
        }

        return response()->json(new PaginatedResponse(
            $data,
            $notifications->currentPage(),
            $notifications->perPage(),
            $notifications->lastPage(),
            $notifications->total()
        ));
    }

    public function countUnreadNotification()
    {
        return response()->json([
            'data' => [
                'unread_count' => auth()->user()->notifications()->whereNull('read_at')->count()
            ]
        ]);
    }
}
