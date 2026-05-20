<?php

namespace App\Http\ViewComposers;

use App\Models\User;
use Illuminate\View\View;

class UserHeaderComposer
{
    /**
     * Bind data to the view.
     *
     * @return void
     */
    public function compose(View $view)
    {
        $view->with('contact', User::findOrFail($view->getData()['user']['id']));
    }
}
