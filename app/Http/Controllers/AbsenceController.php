<?php

namespace App\Http\Controllers;

use App\Enums\AbsenceReason;
use App\Models\Absence;
use App\Models\User;
use App\Services\AbsenceService;
use Illuminate\Http\Request;
use Throwable;
use Yajra\DataTables\DataTables;

class AbsenceController extends Controller
{
    public function indexData()
    {
        if ( ! auth()->user()->can('absence-view')) {
            session()->flash('flash_message_warning', __('You do not have permission to view this page'));

            return redirect()->back();
        }
        $absences = Absence::query()->select(['external_id', 'reason', 'start_at', 'end_at', 'user_id'])->with('user');

        return DataTables::of($absences)
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
        if ( ! auth()->user()->can('absence-view')) {
            session()->flash('flash_message_warning', __('You do not have permission to view this page'));

            return redirect()->back();
        }

        return view('absence.index');
    }

    public function create()
    {
        $users = null;
        if (request()->management === 'true' && auth()->user()->can('absence-manage')) {
            $users = User::with(['department'])->get()->pluck('nameAndDepartmentEagerLoading', 'external_id');
        }

        return view('absence.create')
            ->withReasons(AbsenceReason::values())
            ->withUsers($users);
    }

    public function store(Request $request, AbsenceService $absenceService)
    {
        try {
            $result = $absenceService->storeAbsence($request);
        } catch (Throwable $exception) {
            report($exception);

            return $this->failureResponse(
                $request,
                __('Absence could not be registered. Please try again.'),
                'absence'
            );
        }

        if ($result['error']) {
            if ($request->expectsJson()) {
                return response()->json(['error' => $result['error']], 400);
            }
            session()->flash('flash_message_warning', __($result['error']));

            return redirect()->back();
        }

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Absence registered'], 200);
        }
        session()->flash('flash_message', __('Absence registered'));

        return redirect()->back();
    }

    public function destroy(Absence $absence)
    {
        if ( ! auth()->user()->can('absence-manage')) {
            session()->flash('flash_message_warning', __('You do not have sufficient privileges for this action'));

            return redirect()->back();
        }
        $absence->delete();

        return response('OK');
    }
}
