<?php

namespace App\Http\Controllers;

use App\Http\Requests\Appointment\CreateAppointmentCalendarRequest;
use App\Http\Requests\Appointment\UpdateAppointmentCalendarRequest;
use App\Models\Appointment;
use App\Models\User;
use App\Models\Client;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Ramsey\Uuid\Uuid;

class AppointmentsController extends Controller
{
    public function calendar()
    {
        if (!auth()->user()->can("calendar-view")) {
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
        $appointment->start_at = Carbon::parse($request->start)->setTimezone("Europe/Copenhagen");
        $appointment->end_at = Carbon::parse($request->end)->setTimezone("Europe/Copenhagen");
        $appointment->user()->associate(User::where('external_id', $request->group)->first());
        $appointment->save();

        return response($appointment);
    }

    public function store(CreateAppointmentCalendarRequest $request)
    {
        $client_id = null;
        $user = User::where('external_id', $request->user)->first();

        if ($request->client_external_id) {
            $client_id = Client::where('external_id', $request->client_external_id)->first()->id;
            if (!$client_id) {
                return response(__("Client not found"), 422);
            }
        }

        $request_type = null;
        $request_id = null;
        if ($request->source_type && $request->source_external_id) {
            $request_type = $request->source_type;

            $entry = $request_type::whereExternalId($request->source_external_id);
            $request_id = $entry->id;
        }

        if (!$user) {
            return response(__("User not found"), 422);
        }

        $startTime = str_replace(["am", "pm", ' '], "", $request->start_time) . ':00';
        $endTime = str_replace(["am", "pm", ' '], "", $request->end_time) . ':00';

     

        $appointment = Appointment::create([
            'external_id' => Uuid::uuid4()->toString(),
            'source_type' => $request_type,
            'source_id' => $request_id,
            'client_id' => $client_id,
            'title' => $request->title,
            'start_at' => Carbon::parse($request->start_date . " " . $startTime),
            'end_at' => Carbon::parse($request->end_date . " " . $endTime),
            'user_id' => $user->id,
            'color' => $request->color
        ]);
        $appointment->user_external_id = $user->external_id;
        $appointment->start_at = $appointment->start_at;

        return response($appointment);
    }

    public function destroy(Appointment $appointment)
    {
        if (!auth()->user()->can("appointment-delete")) {
            return response("Access denied", 403);
        }

        $deleted = $appointment->delete();
        if ($deleted) {
            return response("Success");
        }
        return response("Error", 503);
    }
}
