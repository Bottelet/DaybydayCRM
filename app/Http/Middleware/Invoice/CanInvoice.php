<?php
	
	namespace App\Http\Middleware\Invoice;
	
	use Closure;
	
	class CanInvoice
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
			if (!auth()->user()->can('invoice')) {
				Session()->flash('flash_message_warning', 'Not allowed to manage invoices');
				
				return redirect('/');
			}
			
			return $next($request);
		}
	}
