<?php

namespace App\Services\Appointment;

use App\Models\Appointment;
use App\Models\User;
use Carbon\Carbon;
use RuntimeException;

class AppointmentService
{
    /**
     * Update an appointment's time and assigned user.
     *
     * @param Appointment $appointment    The appointment to update
     * @param string      $startTime      The start time (ISO format or Carbon parseable)
     * @param string      $endTime        The end time (ISO format or Carbon parseable)
     * @param string|null $userExternalId Optional user external ID to reassign
     *
     * @return bool Success
     */
    public function updateAppointmentTime(
        Appointment $appointment,
        string $startTime,
        string $endTime,
        ?string $userExternalId = null
    ): bool {
        // Parse timestamps
        $appointment->start_at = Carbon::parse($startTime);
        $appointment->end_at   = Carbon::parse($endTime);

        // Update user if provided
        if ($userExternalId) {
            $user = User::where('external_id', $userExternalId)->first();
            if ($user) {
                $appointment->user()->associate($user);
            }
        }

        return $appointment->save();
    }

    /**
     * Update just the appointment time (start and end).
     *
     * @param Appointment $appointment The appointment
     * @param string      $startTime   Start time
     * @param string      $endTime     End time
     *
     * @return bool Success
     */
    public function updateTime(Appointment $appointment, string $startTime, string $endTime): bool
    {
        $appointment->start_at = Carbon::parse($startTime);
        $appointment->end_at   = Carbon::parse($endTime);

        return $appointment->save();
    }

    /**
     * Reassign appointment to a different user.
     *
     * @param Appointment $appointment The appointment
     * @param User        $user        The new user
     *
     * @return bool Success
     */
    public function reassignToUser(Appointment $appointment, User $user): bool
    {
        $appointment->user()->associate($user);

        return $appointment->save();
    }

    /**
     * Reassign appointment by user external ID.
     *
     * @param Appointment $appointment    The appointment
     * @param string      $userExternalId The user external ID
     *
     * @return bool Success
     *
     * @throws RuntimeException If user not found
     */
    public function reassignToUserByExternalId(Appointment $appointment, string $userExternalId): bool
    {
        $user = User::where('external_id', $userExternalId)->first();
        if ( ! $user) {
            throw new RuntimeException("User with external ID '{$userExternalId}' not found");
        }

        return $this->reassignToUser($appointment, $user);
    }

    /**
     * Delete an appointment.
     *
     * @param Appointment $appointment The appointment to delete
     *
     * @return bool Success
     */
    public function deleteAppointment(Appointment $appointment): bool
    {
        return (bool) $appointment->delete();
    }

    /**
     * Get appointments for a user within a date range.
     *
     * @param User   $user      The user
     * @param Carbon $startDate Start date
     * @param Carbon $endDate   End date
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAppointmentsForUserInRange(User $user, Carbon $startDate, Carbon $endDate)
    {
        return $user->appointments()
            ->whereBetween('start_at', [$startDate->startOfDay(), $endDate->endOfDay()])
            ->orWhereBetween('end_at', [$startDate->startOfDay(), $endDate->endOfDay()])
            ->get();
    }

    /**
     * Get appointment by ID.
     *
     * @param int $id The appointment ID
     *
     * @return Appointment|null
     */
    public function findById(int $id): ?Appointment
    {
        return Appointment::find($id);
    }

    /**
     * Check if user has any appointments at a given time.
     *
     * @param User   $user The user
     * @param Carbon $time The time to check
     *
     * @return bool True if user has an appointment at that time
     */
    public function hasAppointmentAtTime(User $user, Carbon $time): bool
    {
        return Appointment::where('user_id', $user->id)
            ->where('start_at', '<=', $time)
            ->where('end_at', '>=', $time)
            ->exists();
    }
}
