<?php

namespace App\Http\Controllers;

use App\Http\Requests\Integration\StoreIntegrationRequest;
use App\Models\Integration;
use App\Services\Integration\IntegrationService;
use Illuminate\Http\JsonResponse;

class IntegrationsController extends Controller
{
    public function __construct()
    {
        $this->middleware('is.demo');
        $this->middleware('user.is.admin');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\View\View
     */
    public function index(): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\View\View
    {
        $billing_integration    = Integration::whereApiType('billing')->first();
        $filesystem_integration = Integration::whereApiType('file')->first();

        return view('integrations.index')
            ->with('billing_integration', $billing_integration)
            ->with('filesystem_integration', $filesystem_integration)
            ->with('google_drive_auth_url', null)
            ->with('dropbox_auth_url', null);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreIntegrationRequest $request, IntegrationService $integrationService): JsonResponse|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\View\View
    {
        $integrationService->storeOrUpdateByApiType($request->validated());

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Integration saved successfully'], 201);
        }

        return $this->index();
    }
}
