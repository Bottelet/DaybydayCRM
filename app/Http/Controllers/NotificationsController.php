<?php
namespace App\Http\Controllers;

use Notifynder;
use App\Models\User;
use App\Http\Requests;
use Illuminate\Http\Request;

class NotificationsController extends Controller
{
    public function getAll()
    {
        $user = User::find(\Auth::id());
       
        return $user->unreadNotifications;
        
    }
    public function markRead(Request $request)
    {
        $notifyId = $request->Id;

        $user = User::find(\Auth::id());
        $user->unreadNotifications()->where('id', $request->id)->first()->markAsRead();
    }
    public function markAll()
    {
        $user = \Auth::id();
        Notifynder::readAll($user);
        return redirect()->back();
    }
}
