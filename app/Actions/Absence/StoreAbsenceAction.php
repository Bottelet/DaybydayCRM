<?php

namespace App\Actions\Absence;

use App\Models\Absence;
use App\Models\User;
use Carbon\Carbon;
use Ramsey\Uuid\Uuid;

class StoreAbsenceAction
{
    /**
     * Create a new absence record
     *
     * @param  User  $user  The user for whom the absence is being created
     * @param  string  $reason  The reason for absence
     * @param  string  $startDate  The start date of absence
     * @param  string  $endDate  The end date of absence
     * @param  bool|null  $medicalCertificate  Whether a medical certificate is provided
     * @param  string|null  $comment  Additional comments
     */
    public function execute(
        User $user,
        string $reason,
        string $startDate,
        string $endDate,
        ?bool $medicalCertificate = null,
        ?string $comment = null
    ): Absence {
        return Absence::create([
            'external_id' => Uuid::uuid4()->toString(),
            'reason' => $reason,
            'user_id' => $user->id,
            'start_at' => Carbon::parse($startDate)->startOfDay(),
            'end_at' => Carbon::parse($endDate)->endOfDay(),
            'medical_certificate' => $medicalCertificate,
            'comment' => $comment ? clean($comment) : null,
        ]);
    }
}
