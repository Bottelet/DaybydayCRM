<?php

namespace App\Services\Absence;

use App\Actions\Absence\StoreAbsenceAction;
use App\Models\Absence;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;

class AbsenceService
{
    protected StoreAbsenceAction $storeAbsenceAction;

    public function __construct(StoreAbsenceAction $storeAbsenceAction)
    {
        $this->storeAbsenceAction = $storeAbsenceAction;
    }

    /**
     * Store a new absence from request input.
     *
     * @param Request $request The HTTP request containing absence data
     *
     * @return array Array with 'error' key if validation fails, or empty array on success
     */
    public function storeAbsence(Request $request): array
    {
        // Validate the incoming request
        $validationError = $this->validateAbsenceRequest($request);
        if ($validationError) {
            return ['error' => $validationError];
        }

        // Determine the user for whom the absence is being created
        $user = $this->resolveUser($request);
        if ( ! $user) {
            return ['error' => 'User not found'];
        }

        // Check permissions
        $permissionError = $this->checkPermissions($request, $user);
        if ($permissionError) {
            return ['error' => $permissionError];
        }

        // Parse dates
        $startDate = $request->input('start_date');
        $endDate   = $request->input('end_date');

        // Validate date logic
        $dateError = $this->validateDateLogic($startDate, $endDate);
        if ($dateError) {
            return ['error' => $dateError];
        }

        // Handle medical certificate
        $medicalCertificate = $this->parseMedicalCertificate($request->input('radio'));

        // Create the absence using the action
        $this->storeAbsenceAction->execute(
            $user,
            $request->input('reason'),
            $startDate,
            $endDate,
            $medicalCertificate,
            $request->input('comment')
        );

        return [];
    }

    /**
     * Delete an absence.
     *
     * @param Absence $absence The absence to delete
     *
     * @return bool True if deletion was successful
     */
    public function deleteAbsence(Absence $absence): bool
    {
        return (bool) $absence->delete();
    }

    /**
     * Get absences for a given user.
     *
     * @param User        $user      The user
     * @param Carbon|null $startDate Optional start date for filtering
     * @param Carbon|null $endDate   Optional end date for filtering
     *
     * @return \Illuminate\Database\Eloquent\Collection Collection of absences
     */
    public function getAbsencesForUser(User $user, ?Carbon $startDate = null, ?Carbon $endDate = null)
    {
        $query = Absence::where('user_id', $user->id);

        if ($startDate && $endDate) {
            $query->whereBetween('start_at', [$startDate->startOfDay(), $endDate->endOfDay()])
                ->orWhereBetween('end_at', [$startDate->startOfDay(), $endDate->endOfDay()]);
        }

        return $query->get();
    }

    /**
     * Check if user has absence during the given period.
     *
     * @param User   $user      The user
     * @param Carbon $startDate The start date
     * @param Carbon $endDate   The end date
     *
     * @return bool True if user has any absence during this period
     */
    public function userHasAbsenceDuring(User $user, Carbon $startDate, Carbon $endDate): bool
    {
        return Absence::where('user_id', $user->id)
            ->whereBetween('start_at', [$startDate->startOfDay(), $endDate->endOfDay()])
            ->orWhere(function ($query) use ($startDate, $endDate, $user) {
                $query->where('user_id', $user->id)
                    ->whereBetween('end_at', [$startDate->startOfDay(), $endDate->endOfDay()]);
            })
            ->exists();
    }

    /**
     * Validate the absence request input.
     *
     * @param Request $request The HTTP request
     *
     * @return string|null Error message if validation fails, null otherwise
     */
    protected function validateAbsenceRequest(Request $request): ?string
    {
        // Validate reason
        if ( ! $request->has('reason') || empty($request->input('reason'))) {
            return 'Reason is required';
        }

        // Validate start_date
        if ( ! $request->has('start_date') || empty($request->input('start_date'))) {
            return 'Start date is required';
        }

        // Validate end_date
        if ( ! $request->has('end_date') || empty($request->input('end_date'))) {
            return 'End date is required';
        }

        // Validate user_external_id if provided (for management mode)
        if ($request->has('user_external_id') && ! empty($request->input('user_external_id'))) {
            $user = User::where('external_id', $request->input('user_external_id'))->first();
            if ( ! $user) {
                return 'Selected user not found';
            }
        }

        return null;
    }

    /**
     * Resolve the user for whom the absence is being created.
     *
     * @param Request $request The HTTP request
     *
     * @return User|null The user model or null if not found
     */
    protected function resolveUser(Request $request): ?User
    {
        if ($request->has('user_external_id') && ! empty($request->input('user_external_id'))) {
            return User::where('external_id', $request->input('user_external_id'))->first();
        }

        return auth()->user();
    }

    /**
     * Check if the authenticated user has permission to create the absence.
     *
     * @param Request $request The HTTP request
     * @param User    $user    The user for whom the absence is being created
     *
     * @return string|null Error message if permission is denied, null otherwise
     */
    protected function checkPermissions(Request $request, User $user): ?string
    {
        // If creating absence for another user, check absence-manage permission
        if ($request->has('user_external_id') && ! empty($request->input('user_external_id'))) {
            if (auth()->user()->id !== $user->id && ! auth()->user()->can('absence-manage')) {
                return 'You do not have permission to create absence for another user';
            }
        }

        return null;
    }

    /**
     * Validate the date logic (start date should be before or equal to end date).
     *
     * @param string $startDate The start date (format: yyyy/mm/dd)
     * @param string $endDate   The end date (format: yyyy/mm/dd)
     *
     * @return string|null Error message if validation fails, null otherwise
     */
    protected function validateDateLogic(string $startDate, string $endDate): ?string
    {
        try {
            $start = Carbon::createFromFormat('Y/m/d', $startDate)->startOfDay();
            $end   = Carbon::createFromFormat('Y/m/d', $endDate)->endOfDay();

            if ($start->isAfter($end)) {
                return 'End date must be after or equal to start date';
            }
        } catch (Exception $e) {
            return 'Invalid date format';
        }

        return null;
    }

    /**
     * Parse the medical certificate radio button value.
     *
     * @param string|null $value The radio button value ('true', 'false', or 'irrelevant')
     *
     * @return bool|null Boolean if 'true' or 'false', null if 'irrelevant'
     */
    protected function parseMedicalCertificate(?string $value): ?bool
    {
        if ($value === 'true') {
            return true;
        }

        if ($value === 'false') {
            return false;
        }

        // 'irrelevant' or null returns null
        return null;
    }
}
