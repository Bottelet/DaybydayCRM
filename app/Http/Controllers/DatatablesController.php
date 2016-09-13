<?php
namespace App\Http\Controllers;

use App\Models\User;
use App\Http\Requests;
use Illuminate\Http\Request;
use Yajra\Datatables\Datatables;
use App\Http\Controllers\Controller;

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
