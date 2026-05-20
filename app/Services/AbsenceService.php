<?php

namespace App\Services;

use App\Actions\Absence\StoreAbsenceAction;
use App\Models\User;
use Illuminate\Http\Request;

class AbsenceService
{
    public function __construct(private StoreAbsenceAction $storeAbsenceAction) {}

    /**
     * Store an absence record from request data.
     *
     * @return array{error: string|null}
     */
    public function storeAbsence(Request $request): array
    {
        // Get the user for whom absence is being created
        $userExternalId = $request->input('user_external_id');

        if ($userExternalId) {
            // User is trying to create absence for another user
            if ( ! auth()->user()->can('absence-manage')) {
                // User doesn't have permission to create for other users
                // Create for authenticated user instead
                $user = auth()->user();
            } else {
                // Find the user by external ID
                $user = User::where('external_id', $userExternalId)->first();
                if ( ! $user) {
                    return ['error' => 'User not found'];
                }
            }
        } else {
            // No user specified, create for authenticated user
            $user = auth()->user();
        }

        // Create the absence
        $this->storeAbsenceAction->execute(
            $user,
            $request->input('reason'),
            $request->input('start_date'),
            $request->input('end_date'),
            $request->input('medical_certificate'),
            $request->input('comment')
        );

        return ['error' => null];
    }
}
