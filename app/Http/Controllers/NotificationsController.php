<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\User;
use Notifynder;

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

