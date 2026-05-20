<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests;
    use DispatchesJobs;
    use ValidatesRequests;

    protected function expectsJsonResponse(Request $request): bool
    {
        return $request->expectsJson();
    }

    protected function failureResponse(
        Request $request,
        string $message,
        string $errorKey = 'error',
        int $statusCode = 500
    ): JsonResponse|RedirectResponse {
        if ($this->expectsJsonResponse($request)) {
            return response()->json(['message' => $message], $statusCode);
        }

        return redirect()->back()->withInput()->withErrors([$errorKey => $message]);
    }
}
