<?php

namespace App\Http\Middleware;

use App\Services\Storage\GetStorageProvider;
use Closure;
use Illuminate\Http\Request;

class RedirectIfFileSystemIsNotEnabled
{
    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $filesystemIntegration = GetStorageProvider::getStorage();
        if ($filesystemIntegration->isEnabled()) {
            return $next($request);
        }

        session()->flash('flash_message_warning', __('File integration required for this action'));

        return redirect()->back();
    }
}
