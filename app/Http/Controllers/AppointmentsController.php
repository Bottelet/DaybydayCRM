<?php

namespace App\Http\Controllers;

use App\Http\Requests\Appointment\UpdateAppointmentCalendarRequest;
use App\Models\Appointment;
use App\Models\User;
use Carbon\Carbon;

class AppointmentsController extends Controller
{
    public function calendar()
    {
        if (! auth()->user()->can('calendar-view')) {
            session()->flash('flash_message_warning', __('You do not have permission to view this page'));

            return redirect()->back();
        }

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
        // Parse the timestamps directly - they're already in the correct format
        // Don't convert timezone as that would shift the time
        $appointment->start_at = Carbon::parse($request->start);
        $appointment->end_at = Carbon::parse($request->end);
        $appointment->user()->associate(User::where('external_id', $request->group)->first());
        $appointment->save();

        return response($appointment);
    }

    public function destroy(Appointment $appointment)
    {
        if (! auth()->user()->can('appointment-delete')) {
            return response('Access denied', 403);
        }

        $deleted = $appointment->delete();
        if ($deleted) {
            return response('Success');
        }

        return response('Error', 503);
    }
}
