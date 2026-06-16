<?php

namespace App\Http\Controllers;

use App\Http\Requests\Appointment\UpdateAppointmentCalendarRequest;
use App\Models\Appointment;
use App\Models\User;
use Carbon\Carbon;
use Throwable;

class AppointmentsController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:calendar-view', ['only' => ['calendar']]);
        $this->middleware('permission:appointment-edit', ['only' => ['update']]);
        $this->middleware('permission:appointment-delete', ['only' => ['destroy']]);
    }

    public function calendar()
    {
        return view('appointments.calendar');
    }

    public function appointmentsJson()
    {
        return Appointment::with(['user:id,name,external_id', 'user.department:name'])
            ->whereBetween('start_at', [today()->subWeeks(2)->startOfDay(), today()->addWeeks(4)->endOfDay()])
            ->orWhereBetween('end_at', [today()->subWeeks(2)->startOfDay(), today()->addWeeks(4)->endOfDay()])
            ->get();
    }

    public function update(UpdateAppointmentCalendarRequest $request, Appointment $appointment)
    {
        try {
            // Parse the timestamps directly - they're already in the correct format
            // Don't convert timezone as that would shift the time
            $appointment->start_at = Carbon::parse($request->start);
            $appointment->end_at   = Carbon::parse($request->end);
            $assignee              = User::query()->where('external_id', $request->group)->first();

            if ( ! $assignee) {
                $message = __('Selected assignee was not found.');

                if ($request->expectsJson()) {
                    return response()->json([
                        'message' => __('The given data was invalid.'),
                        'errors'  => ['group' => [$message]],
                    ], 400);
                }

                return redirect()->back()->withInput()->withErrors(['group' => $message]);
            }

            $appointment->user()->associate($assignee);
            $appointment->save();
        } catch (Throwable $exception) {
            report($exception);

            return $this->failureResponse(
                $request,
                __('Appointment could not be updated. Please try again.'),
                'appointment'
            );
        }

        return response($appointment);
    }

    public function destroy(Appointment $appointment)
    {
        $deleted = $appointment->delete();
        if ($deleted) {
            return response('Success');
        }

        return response('Error', 503);
    }
}
