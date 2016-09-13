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
        $notread = $user->getNotificationsNotRead();
        return $notread->toJson();
    }
    public function markRead(Request $request)
    {
        $notifyId = $request->Id;
        Notifynder::readOne($notifyId);
    }
    public function markAll()
    {
        $user = \Auth::id();
        Notifynder::readAll($user);
        return redirect()->back();
    }
}
