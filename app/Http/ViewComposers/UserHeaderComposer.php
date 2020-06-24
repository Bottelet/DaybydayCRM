<?php
namespace App\Http\ViewComposers;

use Illuminate\View\View;
use App\Models\User;

class UserHeaderComposer
{

    /**
     * Bind data to the view.
     *
     * @param  View  $view
     * @return void
     */
    public function compose(View $view)
    {
        $view->with('contact', User::findOrFail($view->getData()['user']['id']));
    }
}
