<?php

namespace App\Http\Controllers;

use App\Enums\AbsenceReason;
use App\Models\Absence;
use App\Models\Client;
use App\Models\User;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Ramsey\Uuid\Uuid;
use Yajra\DataTables\DataTables;

class AbsenceController extends Controller
{
    public function indexData()
    {
        if (!auth()->user()->can('absence-view')) {
            session()->flash('flash_message_warning', __('You do not have permission to view this page'));
            return redirect()->back();
        }
        $absences = Absence::select(['external_id', 'reason', 'start_at', 'end_at', 'user_id'])->with('user');
        return Datatables::of($absences)
            ->editColumn('user_id', function ($absences) {
                return $absences->user->name;
            })
            ->editColumn('reason', function ($absences) {
                return __(AbsenceReason::fromStatus($absences->reason)->getDisplayValue());
            })
            ->editColumn('start_at', function ($absences) {
                return $absences->start_at->format(carbonDateWithText());
            })
            ->editColumn('end_at', function ($absences) {
                return $absences->end_at->format(carbonDateWithText());
            })
            ->addColumn('delete', '
                <form action="{{ route(\'absence.destroy\', $external_id) }}" method="POST">
            <input type="hidden" name="_method" value="DELETE">
            <input type="submit" name="submit" value="' . __('Delete') . '" class="btn btn-link" onClick="return confirm(\'Are you sure?\')"">
            {{csrf_field()}}
            </form>')
            ->rawColumns(['delete'])
            ->make(true);
    }
    public function index()
    {
        if (!auth()->user()->can('absence-view')) {
            session()->flash('flash_message_warning', __('You do not have permission to view this page'));
            return redirect()->back();
        }
        return view('absence.index');
    }

    public function create()
    {
        $users = null;
        if (request()->management === "true" && auth()->user()->can('absence-manage')) {
            $users = User::with(['department'])->get()->pluck('nameAndDepartmentEagerLoading', 'external_id');
        }
        return view('absence.create')
            ->withReasons(AbsenceReason::values())
            ->withUsers($users);
    }

    public function store(Request $request)
    {
        $medical_certificate = null;
        $user = auth()->user();
        if ($request->user_external_id && auth()->user()->can('absence-manage')) {
            $user = User::whereExternalId($request->user_external_id)->first();
            if (!$user) {
                Session::flash('flash_message_warning', __('Could not find user'));
                return redirect()->back();
            }
        }
        if ($request->medical_certificate == true) {
            $medical_certificate = true;
        } elseif ($request->medical_certificate == false) {
            $medical_certificate = false;
        }

        Absence::create([
            'external_id' => Uuid::uuid4()->toString(),
            'reason' => $request->reason,
            'user_id' => $user->id,
            'start_at' => Carbon::parse($request->start_date)->startOfDay(),
            'end_at' => Carbon::parse($request->end_date)->endOfDay(),
            'medical_certificate' => $medical_certificate,
            'comment' => clean($request->comment),
        ]);

        Session::flash('flash_message', __('Absence registered'));
        return redirect()->back();
    }

    public function destroy(Absence $absence)
    {
        if (!auth()->user()->can('absence-manage')) {
            Session::flash('flash_message_warning', __('You do not have sufficient privileges for this action'));
            return redirect()->back();
        }
        $absence->delete();

        return response("OK");
    }
}
