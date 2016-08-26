<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\User;
use Yajra\Datatables\Datatables;

class DatatablesController extends Controller
{
    public function getIndex()
    {
        return view('datatables.index');
    }

        /**
         * Process datatables ajax request.
         *
         * @return \Illuminate\Http\JsonResponse
         */
    public function anyData()
    {
        return Datatables::of(User::select('*'))->make(true);
    }
}
