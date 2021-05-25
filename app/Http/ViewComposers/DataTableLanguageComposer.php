<?php
namespace App\Http\ViewComposers;

use Illuminate\View\View;
use App\Models\User;

class DataTableLanguageComposer
{

    /**
     * Bind data to the view.
     *
     * @param  View  $view
     * @return void
     */
    public function compose(View $view)
    {
        $dataTableLanguageFile = asset('lang/' . (in_array(\Lang::locale(), ['dk', 'en', 'fa']) ? \Lang::locale() : 'en') . '/datatable.json');

        $view->with('dataTableLanguageFile', $dataTableLanguageFile);
    }
}
