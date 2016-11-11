<?php
namespace App\Http\Controllers;

use Notifynder;
use App\Models\User;
use App\Http\Requests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Log;

class NotificationsController extends Controller
{
    public function getAll()
    {
        $user = User::find(\Auth::id());
       
        return $user->unreadNotifications;
        
    }
    public function markRead(Request $request)
    {
        $notifyId = $request->id;
       
        $user = User::find(\Auth::id());
        $user->unreadNotifications()->where('id', $request->id)->first()->markAsRead();

        return redirect(($user->notifications()->where('id', $request->id)->first()->data['url']));
    }
    public function markAll()
    {
        $user = \Auth::id();
        Notifynder::readAll($user);
        return redirect()->back();
    }
}
