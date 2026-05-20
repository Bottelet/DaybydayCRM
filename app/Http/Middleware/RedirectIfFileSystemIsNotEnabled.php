<?php

namespace App\Http\Middleware;

use App\Services\Storage\StorageAdapterRegistry;
use Closure;
use Illuminate\Http\Request;

class RedirectIfFileSystemIsNotEnabled
{
    public function __construct(private StorageAdapterRegistry $storage) {}

    /**
     * Handle an incoming request.
     *
     * Authorization has already been verified by the time this middleware runs
     * (it is applied via ->middleware('filesystem.is.enabled') after the
     * permission middleware in each controller's constructor). We therefore
     * only need to check whether a real storage integration is available.
     *
     * @param Request $request
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ($this->storage->isEnabled()) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => __('File integration required for this action'),
            ], 422);
        }

        session()->flash('flash_message_warning', __('File integration required for this action'));

        return redirect()->back();
    }
}
