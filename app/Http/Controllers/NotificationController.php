<?php

namespace App\Http\Controllers;

use App\Http\Resources\NotificationResource;
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
        $notifications = auth()->user()->notifications()->paginate(page: $page, perPage: 5);
        return NotificationResource::collection($notifications)
            ->additional([
                'currentPage' => $notifications->currentPage(),
                'lastPage' => $notifications->lastPage(),
                'perPage' => $notifications->perPage(),
                'total' => $notifications->total(),
            ]);
        ;
    }

    public function isUnreadNotificationExists()
    {
        return response()->json([
            'data' => auth()->user()->unreadNotifications()->exists()
        ]);
    }

    public function markAsReadNotification($id)
    {
        $notification = auth()->user()->notifications()->find($id);
        if (!$notification) {
            return response()->json(['message' => 'Notification not found'], 404);
        }
        $notification->markAsRead();
        return response()->json(['message' => 'Notification marked as read']);
    }
}
