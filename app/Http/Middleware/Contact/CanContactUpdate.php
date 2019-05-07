<?php

namespace App\Http\Middleware\Contact;

use Closure;

class CanContactUpdate
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (!auth()->user()->can('contact-update')) {
            Session()->flash('flash_message_warning', 'Not allowed to update contact');
            return redirect()->route('clients.index');
        }
        return $next($request);
    }
}
