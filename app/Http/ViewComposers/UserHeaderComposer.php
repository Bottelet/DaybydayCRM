<?php
namespace App\Http\ViewComposers;

use Illuminate\View\View;
use App\Repositories\User\UserRepositoryContract;

class UserHeaderComposer
{
    /**
     * The User repository implementation.
     *
     * @var UserRepository
     */
    protected $users;

    /**
     * Create a new profile composer.
     *
     * @param UserRepository|UserRepositoryContract $users
     */
    public function __construct(UserRepositoryContract $users)
    {
        $this->users = $users;
    }

    /**
     * Bind data to the view.
     *
     * @param  View  $view
     * @return void
     */
    public function compose(View $view)
    {
        $view->with('contact', $this->users->find($view->getData()['user']['id']));
    }
}
