<?php

namespace App\Http\Controllers;

use App\Http\Requests\NotificationSubcribeRequest;
use App\Http\Resources\NotificationResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

    }

    public function isUnreadNotificationExists()
    {
        return response()->json([
            'data' => DB::table('notifications')
                ->where('notifiable_type', auth()->user()::class)
                ->where('notifiable_id', auth()->user()->id)
                ->whereNull('read_at')
                ->exists(),
        ]);
    }

    public function markAsReadNotification($id)
    {
        $notification = auth()->user()->notifications()->find($id);
        if (! $notification) {
            return response()->json(['message' => 'Notification not found'], 404);
        }
        $notification->markAsRead();

        return response()->json(['message' => 'Notification marked as read']);
    }

    public function subscribe(NotificationSubcribeRequest $request)
    {
        /** @var User */
        $user = $request->user();
        $user->updatePushSubscription(
            $request->input('endpoint'),
            $request->input('keys.p256dh'),
            $request->input('keys.auth'),
        );

        return response(status: 200);
    }

    public function unsubcribe(Request $request)
    {
        $request->validate([
            'endpoint' => 'required|url',
        ]);
        $request->user()->deletePushSubscription($request->input('endpoint'));

        return response(status: 200);
    }
}
