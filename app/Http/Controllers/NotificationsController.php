<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationsController extends Controller
{
    /**
     * Mark a notification read.
     *
     * @return mixed
     */
    public function markRead(Request $request)
    {
        $user = auth()->user();

        if ( ! $user) {
            abort(401, 'Unauthorized');
        }

        $notification = $user->notifications()->where('id', $request->id)->first();

        if ( ! $notification) {
            // Already read, deleted, or never belonged to this user (e.g. a
            // stale/reused link) — nothing to mark, nowhere reliable to send
            // them, so just go somewhere safe instead of a 500.
            return redirect()->route('dashboard');
        }

        if (null === $notification->read_at) {
            $notification->markAsRead();
        }

        return redirect($notification->data['url'] ?? route('dashboard'));
    }

    /**
     * Mark all notifications as read.
     *
     * @return mixed
     */
    public function markAll()
    {
        $user = auth()->user();

        if ( ! $user) {
            abort(401, 'Unauthorized');
        }

        foreach ($user->unreadNotifications as $notification) {
            $notification->markAsRead();
        }

        return redirect()->back();
    }
}
